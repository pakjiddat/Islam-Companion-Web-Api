<?php

declare(strict_types=1);

namespace Framework\Utilities\Examples;

use \Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

error_reporting(E_ALL);
ini_set("display_errors", "1");
include('../autoload.php');

/**
 * Provides functions for test Profiler package
 *
 * @category   UtilityClassTests
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
final class ProfilerTest
{
    /**
     * Used to get the test data
     * It returns test error data
     *
     * @return array $log_data it contains database object and test data which will be saved to database
     *     error_data => array the test error data to be logged
     *     database_obj => object the database object that will be used to store the data to database. it is an object of class Database
     */
    private function GetTestErrorData() : array
    {
        /** The database object is fetched */
        $connection_info                   = array(
									                "dsn" => "mysql:host=localhost;dbname=pakjiddat_crm;charset=utf8",
									                "user_name" => "nadir",
									                "password" => "kcW5eFSCbPXb#7LHvUGG8T8",
									                "debug" => 2,
									                "use_cache" => false,
									                "app_name" => "Test Application"
								                );
        /** The Database Initializer object is created */		
        $dbinitializer                     = UtilitiesFramework::Factory("dbinitializer", $connection_info);

        /** An exception object is created */
        $exception_obj                     = new \Error("Test Exception");
        /** The error data */
        $error_data['error_level']         = $exception_obj->getCode();
        $error_data['error_type']          = "Exception";
        $error_data['error_message']       = $exception_obj->getMessage();
        $error_data['error_file']          = $exception_obj->getFile();
        $error_data['error_line']          = $exception_obj->getLine();
        $error_data['error_details']       = json_encode($exception_obj->getTrace());
        $error_data['error_html']          = $error_data['error_details'];
        $error_data['app_name']    = "Test Application";
        $error_data['server_data']         = json_encode($_SERVER);
        $error_data['mysql_query_log']     = "";
        $error_data['created_on']          = time();
        /** The log data to be returned */
        $log_data                          = array(
													"error_data" => array($error_data),
													"dbinitializer" => $dbinitializer,
													"use_cache" => $connection_info["use_cache"],
													"debug" => $connection_info["debug"]
												);
        return $log_data;
    }
    
	/**
     * Used to test Profiler class
     */
    public function TestProfiler() : void
    {
        /** The profiler object is fetched */
        $profiler       = UtilitiesFramework::Factory("profiler");
        /** The timer is started */
        $profiler->StartProfiling("execution_time");
        /** The GetTestErrorData function is called */
        $this->GetTestErrorData();
        /** The execution time for the function is returned in microseconds */
        $execution_time = $profiler->GetExecutionTime();
        /** The execution time is displayed */
        echo "The function GetTestErrorData took: " . $execution_time . " microseconds";
    }
}

/** An object of class ProfilerTest is created */
$profiler_test            = new ProfilerTest();
/** The TestProfiler function is called */
$profiler_test->TestProfiler();
