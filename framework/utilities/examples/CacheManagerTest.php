<?php

declare(strict_types=1);

namespace Framework\Utilities\Examples;

use Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

error_reporting(E_ALL);
ini_set("display_errors", "1");
include('../autoload.php');

/**
 * Provides functions for test CacheManager package
 *
 * @category   UtilityClassTests
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
final class CacheManagerTest
{
	/** 
     * Used to test function caching
     */
    public function TestCacheManager() : void
    {
        /** The database object is fetched */
        $connection_info     = array(
									"dsn" => "mysql:host=localhost;dbname=pakjiddat_crm;charset=utf8",
									"user_name" => "nadir",
									"password" => "kcW5eFSCbPXb#7LHvUGG8T8",
									"use_cache" => false,
									"debug" => 2,
									"app_name" => "Example Application"
								);
        /** The Database Initializer object is created */								
        $dbinitializer       = UtilitiesFramework::Factory("dbinitializer", $connection_info);
        /** The caching object is fetched with given parameters */
        $parameters          = array(
                                    "dbinitializer" => $dbinitializer, 
                                    "table_name" => "pakphp_cached_data",
                                    "app_name" => "Example Application"
                               );
        /** The cachemanager object */
        $cachemanager        = UtilitiesFramework::Factory("cachemanager", $parameters);
        
        /** The data to cache */
        $cached_data         = array("value" => "test data");
        /** The data is saved to cache */
        $cachemanager->SaveDataToCache("TestFunction", array(
									"parameter 1",
									"parameter 2"
								) , $cached_data);
        /** The data is fetched from cache */
        $cached_data         = $cachemanager->GetCachedData("TestFunction", array(
									"parameter 1",
									"parameter 2"
								) , true);
								
		/** The cache data is exported */								
        var_export($cached_data);
    }
}

/** The CacheManagerTest class object is created */
$cachemanager = new CacheManagerTest();
/** The TestCacheManager is run */
$cachemanager->TestCacheManager(); 
