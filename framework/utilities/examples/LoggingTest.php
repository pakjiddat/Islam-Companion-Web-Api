<?php

declare(strict_types=1);

namespace Framework\Utilities\Examples;

use \Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

error_reporting(E_ALL);
ini_set("display_errors", "1");
include('../autoload.php');

/**
 * Provides functions for test LogManager package
 *
 * @category   UtilityClassTests
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
final class LogManagerTest
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
        $error_data['app_name']            = "Test Application";
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
     * Used to test LogManager class
     */
    public function TestLogManager() : void
    {
        /** The test error data is fetched */
        $log_data            = $this->GetTestErrorData();
        /** The logging information */
        $logmanagerparams    = array(
									"dbinitializer" => $log_data['dbinitializer'],
									"use_cache" => $log_data["use_cache"],
									"debug" => $log_data["debug"]
								);
        /** The parameters used to save the log data */
        $logdata     		 = array(
									"data" => $log_data['error_data'],
									"table_name" => "pakphp_error_data"
								);							
        /** The error data is saved to database */
        UtilitiesFramework::Factory("logmanager", $logmanagerparams)->AddLogData($logdata['data'], $logdata['table_name']);
        /** The parameters used to fetch the log data. All errors of type Exception are fetched */
        $parameters          = array(
									array(
										"field_name" => "app_name",
										"field_value" => "Test Application"
									)
								);
        /** The condition for fetching log data */
        $logdata_condition   = array("placeholder_condition" => "1", "placeholder_values" => null);
        /** The log data is fetched from database */
        $log_data            = UtilitiesFramework::Factory("logmanager", $logmanagerparams)->GetLogData($logdata['table_name'], $logdata_condition);
        print_r($log_data);
    }
}
/** An object of class LogManagerTest is created */
$logmanager_test            = new LogManagerTest();
/** The TestLogManager function is called */
$logmanager_test->TestLogManager();
