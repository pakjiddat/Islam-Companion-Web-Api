<?php

declare(strict_types=1);

namespace Framework\Utilities;

use \Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

/**
 * This class provides functions for caching data in memory or in database
 * 
 * @category   UtilityClass
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class CacheManager
{
    /** @var string The caching duration for each function */
    public static $cache_duration;
    /** @var object The single static instance */
    protected static $instance;    
    /** @var object The Database object for accessing the database */
    private $database;
    /** @var string The table name for the mysql table containing cached data */
    private $table_name;
    /** @var string The application name */
    private $app_name;
    /** @var array The memory cache */
    private $memory_cache;
    
    /**
     * Used to return a single instance of the class
     * 
     * Checks if instance already exists. If it does not exist then it is created. The instance is returned
     * 
     * @param array $parameters an array containing class parameters. it has following keys:
     *    database => Database the object for accessing the database
     *    table_name => string string the database table where the cached data will be stored		 		
     *  
     * @return CacheManager static::$instance name the class instance
     */
    public static function GetInstance(array $parameters) : CacheManager
    {
        if (static::$instance == null) {
            static::$instance = new static($parameters);
        }
        return static::$instance;
    }
	
    /**
     * Class constructor
     *
     * Used to set the database table name for the table that stores cached data
     * Also sets the database link resource
     * 
     * @param array $parameters an array containing class parameters. it has following keys:
     *    dbinit => DatabaseInitializer the object for accessing the database
     *    table_name => string the table of the database table where the cached data will be stored
     *    app_name => string the site url					 		
     */
    protected function __construct(array $parameters)
    {
        /** The database connection link */
        $this->database                = $parameters['dbinit']->GetDbManagerClassObj("Database");
        /** The database table name */
        $this->table_name              = $parameters['table_name'];
        /** The site url */
        $this->app_name                = $parameters['app_name'];
        /** The duration in seconds for which each functon should be cached */
        self::$cache_duration          = array("TestFunction" => (3600 * 24));
    }
	
	/**
     * Used to add an item to the memory cache
     *
     * It adds an item to the memory cache
     * If the item already exists, then an Exception is thrown
     * 
     * @param string $type the type of item to be added
     * @param string $key the the id of the item
     * @param array $value the item value
     */
    public function AddDataToMemoryCache(string $type, string $key, ?array $value) : void
    {
    	/** If the item does not exist in cache, then it is added */
    	$this->memory_cache[$type][$key] = $value;
    }
    
    /**
     * Used to check if the data exists in memory cache
     *
     * If the data exists in memory cache, then it is fetched
     * 
     * @param string $type the type of item to be added
     * @param string $key the the id of the item
     *
     * @boolean $data_exists indicates if the data exists in memory cache
     */
    public function DataExistsInMemoryCache(string $type, string $key) : bool
    {
	    /** Indicates if the item exists */
    	$data_exists                     = false;
    	/** If the item exists in cache, then it is returned */
    	if (isset($this->memory_cache[$type]) && isset($this->memory_cache[$type][$key])) {
    		/** The data is marked as existing */
    		$data_exists                 = true;
    	}
    	
    	return $data_exists;
    }
    
    /**
     * Used to fetch an item from memory cache
     *
     * It fetches an item from memory cache
     * An Exception is thrown if the item does not exist in memory cache
     * 
     * @param string $type the type of item to be added
     * @param string $key the the id of the item
     *
     * @return array $item_value the value of the required item
     *     
     * @throws Exception an object of type Exception is thrown if the data does not exist in memory cache
     */
    public function FetchDataFromMemoryCache(string $type, string $key) : ?array
    {
    	/** If the item does not exist in cache, then an exception is thrown */
    	if (!isset($this->memory_cache[$type]) || !isset($this->memory_cache[$type][$key])) {
    		/** The Exception is thrown */
    		throw new \Error("Item with key: " . $key . " was not found in memory cache");
    	}
    	/** The item is fetched from cache */
    	$item_value        = $this->memory_cache[$type][$key];
    	
    	return $item_value;
    }
	/**
     * Used to set the Database object
     * 
     * @param Database $database the database object
     */
    public function SetDbObj(Database $database)
    {
       $this->database = $database;
    }
	
    /**
     * Used to set the application name
     *
     * Used to set the application name
     * 
     * @param string $app_name the application name
     */
    public function SetApplicationName(string $app_name) : void
    {
       $this->app_name = $app_name;
    }
    
    /**
     * It converts the data to json format
     * It then encodes the data using base64 encoding
     * 
     * @param array $data an array
     * 
     * @return string $encoded_data base64 encoded parameters
     */
    private function EncodeFunctionData(array $data) : string
    {
        /** The parameters are json encoded */
        $data         = base64_encode(json_encode($data));
        /** The parameters are encoded to base64 in any case */
        $encoded_data = base64_encode($data);
        
        return $encoded_data;
    }
	
    /**
     * Gets the data in the function cache
     *
     * It checks if the data has been in cache for the config duration
     * If so then it returns the cached data
     * 		
     * @param string $function_name name of the function whoose output is required
     * @param array $parameters function parameters		 
     * @param boolean $check_cache_duration used to indicate if the function cache duration should be checked. if set to false then the data will be fetched from cache even if it is expired
	 * 
     * @return array $data the function data is returned or null if data was not found in cache
     */
    public function GetCachedData(string $function_name, array $parameters, bool $check_cache_duration) : ?array
    {
        /** The required data */
        $data               = null;
        /** The duration for which function is to be cached */
        $cache_duration     = ($check_cache_duration) ? self::$cache_duration[$function_name] : '-1';
        /** The function parameters are encoded */
        $parameters         = UtilitiesFramework::Factory("encryption")->EncodeData($parameters);
        /** The cached data is fetched from database */
        if ($cache_duration != -1 && $check_cache_duration) {
            /** The sql query for fetching the data */
            $sql            = "SELECT * FROM " . $this->table_name . 
                              " WHERE function_name=? AND function_parameters=? AND (created_on + ?)>=?";
            /** The sql query data */
            $query_data     = array($function_name, $parameters, $cache_duration, time());
            /** The data is fetched */
            $row            = $this->database->FirstRow($sql, $query_data, null);           
        }
        else {
            /** The sql query for fetching the data */
            $sql            = "SELECT * FROM " . $this->table_name . " WHERE function_name=? AND function_parameters=?";
            /** The sql query data */
            $query_data     = array($function_name, $parameters);
            /** The data is fetched */
            $row            = $this->database->FirstRow($sql, $query_data, null);           
        }
        /** If the data is found then it is returned */
        if ($row != null) {        
			/** The encrypted data is decoded */
            $data           = UtilitiesFramework::Factory("encryption")->DecodeData($row['data'], true);
        }
        
        return $data;
    }
    
    /**
     * Saves the data returned by the function to database cache table
     *
     * It checks if data exists in database
     * If it does not exist, then data is added to database
     * 
     * @param string $function_name name of the function whoose output needs to be cached
     * @param array $parameters function parameters
     * @param array $data function data that needs to be cached			 
     */
    public function SaveDataToCache(string $function_name, array $parameters, array $data) : void
    {
        /** The function parameters are encoded */
        $encoded_parameters = UtilitiesFramework::Factory("encryption")->EncodeData($parameters);
        /** The function data is encoded */
        $encoded_data       = UtilitiesFramework::Factory("encryption")->EncodeData($data);
        /** The data is fetched from cache. If it exists in cache then it is updated */
        if ($this->GetCachedData($function_name, $parameters, false)) {
            /** The sql query for updating the data */
            $sql            = "UPDATE " . $this->table_name . 
                              " SET created_on=?,data=?,app_name=? WHERE function_name=? AND function_parameters=?";
            /** The sql query data */
            $query_data     = array(time(), $encoded_data, $this->app_name, $function_name, $encoded_parameters);
            /** The data is saved */
            $this->database->Execute($sql, $query_data, null);
        }
        /** Otherwise it is added to database */
        else {
            /** The sql query for updating the data */
            $sql            = "INSERT INTO " . $this->table_name . 
                              " (created_on, data, app_name, function_name, function_parameters) VALUES(?, ?, ?, ?, ?)";
            /** The sql query data */
            $query_data     = array(time(), $encoded_data, $this->app_name, $function_name, $encoded_parameters);
            /** The data is saved */
            $this->database->Execute($sql, $query_data, null);
        }
    }
}
