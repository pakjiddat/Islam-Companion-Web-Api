<?php

declare(strict_types=1);

namespace IslamCompanionApi\Lib;

use \Framework\Config\Config as Config;
use \IslamCompanionApi\Lib\HolyQuran\MetaData as HolyQuranMetaData;
use \Framework\Application\Api as Api;

/**
 * This class provides the base class for the Islam Companion API
 * It provides common functions used to handle api requests
 * The functions include caching of api response, validation of api request parameters and error handling
 *
 * @category   Lib
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
class IslamCompanionApi extends Api
{
    /**
     * It checks if the narrator, language, source and book parameter values are valid
     *
	 * @param string $param_name the name of the parameter
	 * @param string $param_value the value of the parameter
     * @param string $function_name the name of the function to validate
     *
     * @return array $validation_result the result of validating the method parameters
     *    is_valid => boolean indicates if the parameters are valid
     *    message => string the validation message if the parameters could not be validated
     */
    public function ValidateParameter(string $param_name, string $param_value, string $function_name) : array
    {        
        /** The result of validating the parameter */
        $validation_result      = array("is_valid" => true, "message" => "");
        /** The validation message */
        $message                = "";
        /** The Holy Quran MetaData object */
        $holyquranmetadata      = Config::GetComponent("holyquranmetadata");
        /** The Hadith MetaData object */
        $hadithmetadata         = Config::GetComponent("hadithmetadata");
        
        /** If the translator value needs to be validated */
        if ($param_name == "narrator" && !$holyquranmetadata->IsTranslatorValid($param_value)) {
            /** The error message is set */
            $message            = "Invalid narrator value";
        }
        /** If the language value needs to be validated */
        else if ($param_name == "language") {
            /** If the function name is HandleGetHadithBooks, HandleGetHadithTitles or HandleGetHadithNavigatorConfig */
            if ($function_name == "HandleGetHadithBooks" 
                || $function_name == "HandleGetHadithTitles" 
                || $function_name == "HandleGetHadithNavigatorConfig"
            ) {
                /** If the language is not a supported language for hadith translations */
                if (!$hadithmetadata->IsLanguageValid($param_value)) {
                    /** The error message is set */
                    $message    = "Invalid language value";
                }
            }
            /** If it is another function */
            else {
                /** If the language is not a supported language for quranic translations */
                if (!$holyquranmetadata->IsLanguageValid($param_value)) {
                    /** The error message is set */
                    $message    = "Invalid language value";
                }
            }
        }
        /** If the source value needs to be validated */
        else if ($param_name == "source") {
            /** If the language is not a valid Hadith language */
            if (!$hadithmetadata->IsSourceValid($param_value)) {
                /** The error message is set */
                $message    = "Invalid source value";
            }
        }
        
        /** If a validation error was set */
        if ($message != "") {
            /** The validation result is set to not valid */
            $validation_result["is_valid"]   = false;
            /** The validation message is set */
            $validation_result["message"]    = $message;
        }
        
        return $validation_result;
    }
    
    /**
     * Custom error handling function
     *
     * @param string $log_message the error log message
     * @param array $error_parameters the error parameters. it contains following keys:
     *    error_level => int the error level
     *    error_type => int [Error~Exception] the error type. it is either Error or Exception
     *    error_message => string the error message
     *    error_file => string the error file name
     *    error_line => int the error line number
     *    error_context => array the error context
     */
    public function CustomErrorHandler(string $log_message, array $error_parameters) : void
    {
        /** The server information is fetched */
        $server_database_information         = $this->GetServerAndDatabaseInformation();
        /** The database information is added to the error parameters */
        $error_parameters['mysql_query_log'] = $server_database_information['mysql_query_log'];
        /** The server data is added to the error parameters */
        $error_parameters['server_data']     = $server_database_information['server_data'];
        /** If the error message was generated on the api server, then it is logged to database */
        if (Config::$config["custom"]["is_api_server"]) {
            /** The error message is logged to database */
            $this->LogErrorToDatabase(
                $error_parameters,
                $error_parameters["server_data"],
                $error_parameters["mysql_query_log"]
            );
        }
        /** If the error message was not generated on the api server, then the error message is logged using a web hook */
        else {
            $this->LogErrorToWebHook("IslamCompanionApi", $error_parameters, false);
        }
        /** The custom error handler function of the parent api class is called */
        parent::CustomErrorHandler($log_message, $error_parameters);
    }
    /** 
     * Used to save the cached data
     * It checks if the data needs to be cached
     * If the data does not need to be cached, then the function returns
     * If the data needs to be cached, then it is saved to database
     *
     * @param string $function_name the name of the function to cache
     * @param array $function_parameters the function parameters
     * @param array $function_data the function data to be cached
     */
    private function SaveDataToCache(string $function_name, array $function_parameters, array $function_data) : void
    {
        /** Indicates if function output should be cached */
        $enable_function_caching = Config::$config["general"]["enable_function_caching"];
        /** If the function output should be cached */
        if ($enable_function_caching) {
	        /** The cached data is saved to database */
    	    Config::GetComponent("caching")->SaveDataToCache($function_name, $function_parameters, $function_data);
    	}
    }
    /** 
     * Used to return the cached data
     * It returns the cached data for the given function name and function parameters
     *
     * @param string $function_name the name of the cached function
     * @param array $parameters the function parameters
     *
     * @return array $cached_data the cached data is returned. null is returned if the cached data does not exist
     */
    private function GetCachedData(string $function_name, array $parameters) : ?array
    {
        /** Indicates if function output should be cached */
        $enable_function_caching           = Config::$config["general"]["enable_function_caching"];
        /** If the function output should not be cached */
        if (!$enable_function_caching) return false;
        /** The application name */
        $application_name                  = Config::$config["general"]["application_name"];
        /** The pdo object is fetched */
        $db_obj                            = Config::GetComponent("database")->GetId();
        /** The db link is set in the caching object */
        Config::GetComponent("caching")->SetDbObj($db_obj);
        /** The application name is set in the caching object */
        Config::GetComponent("caching")->SetApplicationName($application_name);
        /** The data is fetched from database cache if it exists */
        $cached_data                       = Config::GetComponent("caching")->GetCachedData(
                                                 $function_name,
                                                 $parameters,
                                                 true
                                             );
        
        /** The cached cached is returned */
        return $cached_data;
    } 
}
