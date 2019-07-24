<?php

declare(strict_types=1);

namespace Framework\Application;

use \Framework\Config\Config as Config;
use \Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

/**
 * This class provides the base class for developing browser based applications
 *
 * @category   Application
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
abstract class Web extends \Framework\Application\Application
{    
    /**
     * It creates a url from the given parameters
     * It generates the url by concatenating the site url, controller and action
     *
     * @param string $controller the controller
     * @param string $action the action
     *
     * @return string $url the url is returned
     */
    public function GenerateUrl(string $controller, string $action) : string
    {
        /** The site url is fetched */
        $site_url                    = Config::$config["general"]["site_url"];
        /** The url is built */
        $url                         = $site_url . $controller . "/" . $action;
        
        return $url;
    }        
    
    /**
     * It converts the given parameters to the correct type
     *
     * @param array $params the method parameters
     *
     * @return array $updated_params the converted method parameters
     */
    private function SetParamType(array $params) : array
    {
        /** The updated method params */
        $updated_params = array();
        /** Each argument is checked */
        for ($count = 0; $count < count($params); $count++) {
            /** If the argument is an integer */
            if (is_numeric($params[$count])) {
                /** If the argument contains "." */
                if (strpos($params[$count], ".") !== false) {
                    /** The argument is converted to float */
                    $updated_params[$count] = floatval($params[$count]);
                }
                /** If the argument does not contain "." */
                if (strpos($params[$count], ".") === false) {
                    /** The argument is converted to int */
                    $updated_params[$count] = intval($params[$count]);
                }
            }
            /** If the argument is not numeric */
            else {
                $updated_params[$count] = $params[$count];
            }
        }
        
        return $updated_params;
    }
       
    /**
     * Used to run the method given in the Callbacks file
     *
     * @param array $parameters the application parameters
     *
     * @return string $string the function response
     */
    final public function RunMethod(array $parameters) : string
    {
        /** The custom validator */
        $validator                = Config::$config["general"]["validator"];        
        /** The application parameters */
        $parameters               = Config::$config["general"]["parameters"];
        /** The command callback is fetched */
        $callback                 = Config::$config["general"]["callback"];
        /** The controller object is fetched */
        $callback_obj             = Config::GetComponent($callback[0]);
        /** If the callback is not callable */
        if (!is_callable(array($callback_obj, $callback[1]))) {
            /** The error message */
            $msg  = "Invalid url request sent to application. Object: " . $callback[0] . ". Function: ";
            $msg .= $callback[1] . " is not callable";
            /** An exception is thrown */
            throw new \Error($msg);                
        }
 
        /** If the url request should be logged */
        if (Config::$config["general"]["log_user_access"]) {
            /** The profiling of execution time is started */
            UtilitiesFramework::Factory("profiler")->StartProfiling("execution_time");
        }
        
        /** The class name */
        $class_name           = get_class($callback_obj);
        /** The method to validate */
        $method               = array("class" => $class_name, "method" => $callback[1]);
        /** If the validator object and function are given */
        if (isset($validator[0]) && isset($validator[1])) {
            /** The validator object */
            $obj              = Config::GetComponent($validator[0]);
            /** The validator function */
            $func_name        = $validator[1];
            /** The validator callable is placed in array */
            $validator        = array("callback" => array($obj, $func_name), "params" => array());
        }
        /** The function parameters are validated */
        Config::GetComponent("functionvalidation")->ValidateMethodParameters($method, $parameters, $validator);
        /** The callable method to call */
        $main_method          = array($callback_obj, $callback[1]);
        /** The parameter values are sorted by key */
        ksort($parameters);
        /** The parameter values */
        $arguments            = array_values($parameters);
        /** The arguments are converted to the correct type */
        $arguments            = $this->SetParamType($arguments);
        /** The callback function is called */
        $response             = call_user_func_array($main_method, $arguments);
        /** The function return value is validated */
        Config::GetComponent("functionvalidation")->ValidateMethodReturnValue($method, $response, $validator);
        
        /** If the application response should be sanitized */
        if (Config::$config["general"]["sanitize_response"]) {
            /** The function return value is sanitized */
            $response             = Config::GetComponent("functionvalidation")->SanitizeData($response);
        }            
        /** If the request data should be saved as test data for user interface tests */
        if (Config::$config["test"]["save_ui_test_data"]) {
            /** The request data is saved to database */
            Config::GetComponent("testdatamanager")->SaveUiTestData();
        }
        
        /** If the url request should be logged */
        if (Config::$config["general"]["log_user_access"]) {
            /** The user request is logged */
            Config::GetComponent("loghandling")->LogUserAccess();
        }
       
        return $response;
    }
    
   /**
     * Used to generate parameters for the application
     *
     * It parses the current url
     * The $_REQUEST object is saved to application config    
     * This function may be overriden by a child class in order to customize the url parsing
     */
    public function GenerateParameters() : void
    {
        /** The site url */
        $site_url         = Config::$config["general"]["site_url"];
        /** The information submitted by the user */
        $parameters       = Config::$config["general"]["http_request"];
        
        /** The request url is sanitized */
        Config::$config["general"]["request_uri"] = Config::GetComponent("functionvalidation")->SanitizeText(
                                                        Config::$config["general"]["request_uri"],
                                                        "url"
                                                     );        
        /** The current url request */
        $request_url      = Config::$config["general"]["request_uri"];
        /** The site url is removed from the current url */
        $request_url      = str_replace($site_url, "", $request_url);
        /** The url path list */
        $parts            = explode("/", ltrim($request_url, "/"));
        
        /** Each input parameter is checked */
        foreach ($parameters as $key => $value) {
            /** If the type of the input parameter is given in application configuration */
            $type             = Config::$config["general"]['input_types'][$key] ?? "plain text";
            /** The user input is sanitized */
            $parameters[$key] = Config::GetComponent("functionvalidation")->SanitizeText($value, $type);
        }

        /** The page parameters are saved to application config */
        Config::$config["general"]["parameters"]           = $parameters;
    }
}

