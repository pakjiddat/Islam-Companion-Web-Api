<?php

declare(strict_types=1);

namespace Framework\Utilities\DatabaseManager;

use \Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

/**
 * Provides functions for initializing database connections
 *
 * It provides functions for connecting to given database and initializing database connections
 *
 * @category   UtilityClass
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
class DbInitializer
{        
    /** @var object The PDO class object */
    private $pdo;
    /** @var object The list of objects in DatabaseManager package */
    private $dbobjectlist;
    /** Indicates the debug level */
    private $debug;
    /** Indicates if data should be cached */
    private $use_cache;
    /** The application name */
    private $app_name;
    /**
     * Initializes object variables. Connects to database server
     *
     * @param array $parameters database server connection information
     *    dsn => string the data source name. for example: mysql:dbname=testdb;host=127.0.0.1;charset=utf8
     *    user => string database user
     *    password => string database password
     *    debug => int the debug level
     *    use_cache => bool indicates if data should be cached
     *    app_name => string the application name
     */
    public function __construct($parameters)
    {
        /** The debug level is set */
        $this->debug     = $parameters['debug'];
        /** The caching option is set */
        $this->use_cache = $parameters['use_cache'];
        /** The application name is set */
        $this->app_name  = $parameters['app_name'];
                
        /** The database connection is created */
        $this->Connect(
            $parameters['dsn'],
            $parameters['user_name'],
            $parameters['password'],
            $parameters['debug'],
            $parameters['use_cache']
        );
    }    
    /**
     * Initializes object variables. connects to database server
     *
     * @param string $dsn the data source name. for example: mysql:dbname=testdb;host=127.0.0.1;charset=utf8
     * @param string $user the database user
     * @param string $password the database password
     * @param int $debug the debug level
     * @param bool $use_cache indicates if data should be cached
     */
    public function Connect(string $dsn, string $user, string $password, int $debug, bool $use_cache)
    {
        /** The debug level is set */
        $this->debug     = $debug;
        /** The caching option is set */
        $this->use_cache = $use_cache;    
        /** The PDO connection object is initialized */
        $this->pdo       = 0;
        /** The database connection is created */
        $this->CreatePdoConnection($dsn, $user, $password);
    }
    /**
     * Used to close the SQL connection
     */
    public function Close() 
    {
        if (is_object($this->pdo)) $this->pdo = null;
    }
    /**
     * Used to get the SQL query link
     *
     * @return \PDO $Id the PDO object
     */
    public function GetId() : \PDO
    {
        return $this->pdo;
    }
    /**
     * Returns the debug level
     *
     * @return int $debug the debug level
     */
    public function GetDebugLevel() : int
    {
        return $this->debug;
    }
    /**
     * Indicates whether database results should be cached
     *
     * @return bool $use_cache indicates if cache should be used
     */
    public function IsUseCache() : bool
    {
        return $this->use_cache;
    }
    /**
     * Returns the name of the application
     *
     * @return string $app_name the name of the application
     */
    public function GetAppName() : string
    {
        return $this->app_name;
    }
    /**
     * Used to connect to the SQL database server
     *
     * @param string $dsn database server host name
     * @param string $user_name database user name
     * @param string $password database password
     *
     * @throws PDOException object if an error occured
     */
    private function CreatePdoConnection(string $dsn, string $user_name, string $password) 
    {
        try {
            /** The PDO connection is created */
            $this->pdo = new \PDO($dsn, $user_name, $password);
            /** The error mode is set so an exception is thrown in case of errors */
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
        catch (\PDOException $e) {
            /** If the connection could not be created, then an Exception is thrown */
            throw new \Error("Error in establishing database server connection");
        }
    }
    
    /**
     * Used to return an object of the given DatabaseManager package
     *
     * @param string $class_name the name of the DatabaseManager class
     *
     * @return $dbobject object the object of the given DatabaseManager class
     */
    public function GetDbManagerClassObj(string $class_name) : object
    {
        /** The required object */
        $dbobject      = "";
        /** If the object has already been created */
        if (isset($this->dbobjectlist[$class_name])) {
            /** The object is set */
            $dbobject  = $this->dbobjectlist[$class_name];
            /** The object is returned */
            return $dbobject;
        }
        
        /** If an object of class Database is required */
        if ($class_name == "Database") {
            /** The parameters for the object */
            $params    = array("debug" => $this->debug, "use_cache" => $this->use_cache, "dbinit" => $this);
            /** The object is created */
            $dbobject  = UtilitiesFramework::Factory("database", $params);
        }
        /** If an object of class DbCacheManager is required */
        else if ($class_name == "DbCacheManager") {
            /** The parameters for accessing the cache object */
		    $params    = array("table_name" => "pakphp_cached_data", "dbinit" => $this, "app_name" => $this->app_name);
		    /** The parameters for the object */
            $cmanager  = UtilitiesFramework::Factory("cachemanager", $params);
            /** An object of class DbCacheManager is created */
            $dbobject  = UtilitiesFramework::Factory("dbcachemanager", array("cachemanager" => $cmanager));
        }
        /** If an object of class DbLogManager is required */
        else if ($class_name == "DbLogManager") {
            /** The parameters for the object */
            $dbobject  = UtilitiesFramework::Factory("dblogmanager", array("debug" => $this->debug));
        }
        /** If an object of class DbMetaQueryRunner is required */
        else if ($class_name == "DbMetaQueryRunner") {
            /** The parameters for the object */
            $dbobject  = UtilitiesFramework::Factory("dbmetaqueryrunner", array("dbinit" => $this));
        }
        /** If an object of class DbQueryBuilder is required */
        else if ($class_name == "DbQueryBuilder") {
            /** The parameters for the object */
            $dbobject  = UtilitiesFramework::Factory("dbquerybuilder", array());
        }
        /** If an object of class DbTransactionManager is required */
        else if ($class_name == "DbTransactionManager") {
            /** The parameters for the object */
            $dbobject  = UtilitiesFramework::Factory("dbtransactionmanager", array("pdo" => $this->pdo));
        }
        /** If an object of class DbDeleteQueryBuilder is required */
        else if ($class_name == "DbDeleteQueryBuilder") {
            /** The parameters for the object */
            $dbobject  = UtilitiesFramework::Factory("dbdeletequerybuilder", array());
        }
        /** If an object of class DbInsertQueryBuilder is required */
        else if ($class_name == "DbInsertQueryBuilder") {
            /** The parameters for the object */
            $dbobject  = UtilitiesFramework::Factory("dbinsertquerybuilder", array());
        }
        /** If an object of class DbSelectQueryBuilder is required */
        else if ($class_name == "DbSelectQueryBuilder") {
            /** The parameters for the object */
            $dbobject  = UtilitiesFramework::Factory("dbselectquerybuilder", array());
        }
        /** If an object of class DbUpdateQueryBuilder is required */
        else if ($class_name == "DbUpdateQueryBuilder") {
            /** The parameters for the object */
            $dbobject  = UtilitiesFramework::Factory("dbupdatequerybuilder", array());
        }
        
        return $dbobject;
    }
}

