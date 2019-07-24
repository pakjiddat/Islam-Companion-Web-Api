<?php

declare(strict_types=1);

namespace Framework\Utilities\DatabaseManager;

use \Framework\Utilities\UtilitiesFramework as UtilitiesFramework;
use \Framework\Utilities\CacheManager as CacheManager;

/**
 * This class contains functions that allow fetching and saving database to a memory cache
 * It is useful for caching results of database queries
 *
 * @category   UtilityClass
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
final class DbCacheManager
{
    /** @var object The single static instance */
    protected static $instance;
    
    /**
     * Class constructor
     * Sets the cachemanager object
     * 
     * @param array $parameters the constructor parameters
     *    cachemanager => DbCacheManager the database cache manager object
     */
    function __construct(array $parameters) 
    {
        /** The CacheManager object is set */
        $this->cachemanager                 = $parameters['cachemanager'];
    }
    
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
    public static function GetInstance(array $parameters) : DbCacheManager
    {
        if (static::$instance == null) {
            static::$instance = new static($parameters);
        }
        return static::$instance;
    }
    
    /**
     * Used to fetch data from memory cache if it exists
     *
     * It checks if the data exists in memory cache for the given sql query and parameters
     * If the data exists, then it is returned. Otherwise null is returned
     *
     * @param string $sql the sql query
     * @param array $$query_params the sql query parameters
     *
     * @return array $data the required data or null if the data was not found
     */
    public function FetchDataFromMemoryCache(string $sql, ?array $query_params) : ?array
    {
    	/** The required data is initialized to null */
    	$data                         = null;
        /** The memory cache key is calculated */
    	$memory_cache_key             = base64_encode($sql);
    	/** If the query parameters are given */
    	if ($$query_params != null) {
        	/** The memory cache key is updated */
	    	$memory_cache_key         .= UtilitiesFramework::Factory("encryption")->EncodeData($query_params);
	    	/** The memory cache key is encoded using md5 hash */
	    	$memory_cache_key         = md5($memory_cache_key);
	    }
		/** If the data exists in memory cache, then it is returned */
		if ($this->cachemanager->DataExistsInMemoryCache("database", $memory_cache_key)) 
			$data                     = $this->cachemanager->FetchDataFromMemoryCache("database", $memory_cache_key);				
    	
    	return $data;
    }
	/**
     * Used to save data to memory cache
     *
     * It saves the given data to memory cache
     *
     * @param string $sql the sql query
     * @param array $query_params the sql query parameters
     * @param array $data the data to be added to memory cache
     */
    public function SaveDataToMemoryCache(string $sql, ?array $query_params, ?array $data)
    {
    	/** The memory cache key is calculated */
	    $memory_cache_key         = base64_encode($sql);
	    /** If the query parameters are given */
	    if ($query_params != null) {
    		/** The memory cache key is updated */
	    	$memory_cache_key         .= UtilitiesFramework::Factory("encryption")->EncodeData($query_params);
	    	/** The memory cache key is encoded using md5 hash */
	    	$memory_cache_key         = md5($memory_cache_key);
		}
       	/** The data is saved to memory cache */
	    $this->cachemanager->AddDataToMemoryCache("database", $memory_cache_key, $data);
    }
}

