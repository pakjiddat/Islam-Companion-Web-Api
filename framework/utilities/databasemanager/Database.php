<?php

declare(strict_types=1);

namespace Framework\Utilities\DatabaseManager;

use \Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

/**
 * This class is a wrapper around PDO functions. It allows running sql queries
 *
 * @category   UtilityClass
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class Database
{    
    /** @var array the data used by class */
    public $data = array();
    /** @var object The DbCacheManager class object */
    private $dbcachemanager;    
    /** @var object The LogManager class object */
    private $dblogmanager;
    /** @var object The Initializer class object */
    private $dbinitializer;
    /** @var object The PDO class object */
    private $pdo;
    
    /**
     * Class constructor
     * Sets the initializer object
     * 
     * @param array $parameters the parameters for the class constructor
     *    dbinit => DbInitializer the initializer object
     */
    function __construct($parameters) 
    {
        /** The initializer object is set */
        $this->dbinitializer           = $parameters['dbinit'];
        /** The debug level is set */
        $this->data['debug']           = $this->dbinitializer->GetDebugLevel();
        /** The use_cache value is fetched */
        $this->data['use_cache']       = $this->dbinitializer->IsUseCache();
        /** The PDO object is set */
        $this->pdo                     = $this->dbinitializer->GetId();
    }
    
    /**
     * Gets the initializer object
     *
     * @return Initializer $dbinitializer the initializer object is returned
     */
    public function GetInitializer() : Initializer
    {
        /** The initializer object is returned */
        return $this->dbinitializer;
    }
    
    /**
     * Sets the initializer object
     *
     * @param Initializer $dbinitializer the initializer object
     */
    public function SetInitializer(DbInitializer $dbinitializer)
    {
        /** The initializer object is set */
        $this->dbinitializer = $dbinitializer;
    }
    
    /**
     * Used to prepare the given sql query
     *
     * @param string $sql sql query that needs to be prepared
     * @param array $driver_options the options for the database driver. e.g scrollable cursor
     *     
     * @return \PDOStatement $sth the PDOStatement object representing prepared sql query
     */
    public function Prepare(string $sql, array $driver_options = array()) : \PDOStatement
    {
        /** An object of DbLogManager class is fetched */
        $this->dblogmanager            = $this->dbinitializer->GetDbManagerClassObj("DbLogManager");
        /** The query logging is started */
        $this->dblogmanager->LogQuery(true);
        
        /** The SQL query is prepared */
        $sth                           = $this->pdo->prepare($sql, $driver_options);
        
        /** The query logging is stopped */
        $this->dblogmanager->LogQuery(false, $sql);
        
        return $sth;
    }
    
    /**
     * Used to execute the given sql query
     *
     * @param string $sql sql query that needs to be executed
     * @param array $query_params the list of parameters to be used with the prepared query
     * @param \PDOStatement $sth optional the prepared sql query
     *     
     * @return boolean $is_run true if sql query was successfully executed
     */
    public function Execute(string $sql, ?array $query_params, ?\PDOStatement $sth = null) : bool
    {
        /** An object of DbLogManager class is fetched */
        $this->dblogmanager          = $this->dbinitializer->GetDbManagerClassObj("DbLogManager");
        /** The query logging is started */
        $this->dblogmanager->LogQuery(true);
        
        /** If the prepared sql query is not provided */
        if ($sth == null) {
            /** The SQL query is prepared */
            $sth                     = $this->pdo->prepare($sql);
        }
        /** If the query parameters are given, then they are used */
        if ($query_params !== null) {
            /** The SQL query is run */
            $is_run                  = $sth->execute($query_params);
        }
        /** If the query parameters are not given */
        else {
            /** The SQL query is run */
            $is_run                  = $sth->execute();
        }
        /** The number of affected rows */
        $this->data['affected_rows'] = $sth->rowCount();
        
        /** The query logging is stopped */
        $this->dblogmanager->LogQuery(false, $sql);
        
        return $is_run;
    }
    /**
     * Used to fetch the first row of the select query results
     *
     * @param string $sql sql query for which the data needs to be fetched
     * @param array $query_params the list of parameters to be used with the prepared query
     * @param \PDOStatement $sth the prepared sql query
     * @param int $fetch_style the format of the fetched data
     *
     * @return array first row of the select query result
     */
    public function FirstRow(
        ?string $sql = null,
        ?array $query_params = null,
        ?\PDOStatement $sth = null,
        ?int $fetch_style = \PDO::FETCH_ASSOC
    ) : ?array {
    
        /** If the data should be cached */
        if ($this->data['use_cache']) {
      		/** An object of class DbCacheManager is fetched */
            $this->dbcachemanager    = $this->dbinitializer->GetDbManagerClassObj("DbCacheManager");
            /** The data is fetched from memory cache */
        	$row                     = $this->dbcachemanager->FetchDataFromMemoryCache($sql, $query_params);
        	/** If the data was found in cache, then it is returned */
        	if ($row != null) return $row; 
        }
        /** An object of DbLogManager class is fetched */
        $this->dblogmanager          = $this->dbinitializer->GetDbManagerClassObj("DbLogManager");
        /** The query logging is started */
        $this->dblogmanager->LogQuery(true);
        
        /** If the prepared sql query is not provided */
        if ($sth == null) 
            /** The SQL query is prepared */
            $sth                     = $this->pdo->prepare($sql);
        
        /** If the query parameters are given, then they are used */
        if ($query_params !== null)
            /** The SQL query is run */
            $sth->execute($query_params);        
        /** If the query parameters are not given */
        else 
            /** The SQL query is run */
            $sth->execute();
         
        /** The row is fetched using the given fetch style */
        $row                         = $sth->fetch($fetch_style);

        /** If the data was not found, then it is set to null */
        if (!$row) 
            $row                    = null;
            
        /** The query logging is stopped */
        $this->dblogmanager->LogQuery(false, $sql);
        
        /** If the data should be cached */
        if ($this->data['use_cache']) {
            /** The data is saved to memory cache if needed */
        	$this->dbcachemanager->SaveDataToMemoryCache($sql, $query_params, $row);
        }
            
        return $row;
    }
    /**
     * Used to run the given sql query and return all the rows
     *
     * @param string $sql optional sql query for which the data needs to be fetched
     * @param array $query_params optional the list of parameters to be used with the prepared query
     * @param \PDOStatement $sth optional the prepared sql query
     * @param int $fetch_style the format of the fetched data     
     *
     * @link http://php.net/manual/en/pdostatement.fetchall.php Documentation of the fetchall PDO function
     *
     * @return array $rows all the rows returned by the query
     */
    public function AllRows(
        ?string $sql = null,
        ?array $query_params = null,
        ?\PDOStatement $sth = null,
        ?int $fetch_style = \PDO::FETCH_ASSOC    
    ) : ?array 
    {    
        /** If the data should be cached */
        if ($this->data['use_cache']) {
            /** An object of class DbCacheManager is fetched */
            $this->dbcachemanager = $this->dbinitializer->GetDbManagerClassObj("DbCacheManager");
            /** The data is fetched from memory cache */
        	$rows                 = $this->dbcachemanager->FetchDataFromMemoryCache($sql, $query_params);        
    	    /** If the data was found in cache, then the data is returned */
        	if ($rows != null) return $rows; 
        }
    	
        /** An object of DbLogManager class is fetched */
        $this->dblogmanager       = $this->dbinitializer->GetDbManagerClassObj("DbLogManager");
        /** The query logging is started */
        $this->dblogmanager->LogQuery(true);
        
        /** If the prepared sql query is not provided */
        if ($sth == null)
            /** The SQL query is prepared */
            $sth                  = $this->pdo->prepare($sql);       
        
        /** If the query parameters are given */
        if ($query_params !== null)
            /** The SQL query is run */
            $sth->execute($query_params);
        else 
            /** The SQL query is run */
            $sth->execute();
        
        /** The rows returned by the query are fetched using the default fetch style */
        $rows                     = $sth->fetchAll($fetch_style);
        
        /** If the data was not found, then it is set to null */
        if (!$rows) 
            $rows                 = null;
        
        /** The query logging is stopped */
        $this->dblogmanager->LogQuery(false, $sql);
        
        /** If the data should be cached */
        if ($this->data['use_cache']) {
            /** The data is saved to memory cache if needed */
        	$this->dbcachemanager->SaveDataToMemoryCache($sql, $query_params, $rows);
        }
            
        return $rows;
    }
    /**
     * Used to get the number of rows affected by the last query
     *
     * @return int row_count number of rows affected by last database query
     */
    public function AffectedRows() : int
    {
        /** The number of rows affected by last database query */
        $row_count = $this->data['affected_rows'];
        
        return $row_count;
    }
    /**
     * Used to get the total number of rows in the database table
     *
     * @param string $table_name the name of the database table
     *     
     * @return int $row_count the total number of rows in the table
     */
    public function GetRowCount(string $table_name) : int
    {
        /** The sql for getting the total number of rows */
        $sql                     = "SELECT count(*) AS total FROM " . $table_name;
        /** The number of rows are fetched */
        $row                     = $this->FirstRow($sql, null);
        /** The total number of rows */
        $row_count               = $row['total'];
        
        return $row_count;
    }

    /**
     * Used to get the row id of the last row that was added to database
     *
     * @return string $row_id row id of last row added to database
     */
    public function LastInsertId() : string
    {
        /** The last inserted id is fetched */
        $row_id = $this->pdo->lastInsertId();
        
        return $row_id;
    }
}
