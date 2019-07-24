<?php

declare(strict_types=1);

namespace Framework\TestManager;

use \Framework\Config\Config as Config;
use \Framework\Application\CommandLine as CommandLine;
use \Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

/**
 * Provides functions for managing test data
 *
 * @category   Testing
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
final class TestDataManager
{
    /**
     * This function loads test data for the given method
     * It reads the test data file for the method
     *
     * @return array $test_data the formatted test data
     *    params => array the method parameter values
     *    return_info => array the return value information
     *        return_value => string the return value of the function encoded in json format
     *        return_type => string [array,int,string,float,bool] the return type
     *        rule => string the rule for validating the return value
     */
    public function LoadTestDataForMethod(string $object_name, string $class_method) : array
    {
        /** The formatted test data */
        $test_data    = array();
        /** The path to the test data folder */
        $folder_path  = Config::$config['test']['test_data_folder'] . DIRECTORY_SEPARATOR . $object_name;
        /** The path to the test data file */
        $file_path    = $folder_path . DIRECTORY_SEPARATOR . $class_method . ".txt";
        /** The file is opened */
        $fh           = fopen($file_path, "r");
        /** The first line is read */
        $header       = fgetcsv($fh, 0, "|");
        /** The number of method parameters in the test data */
        $param_count  = 0;
        /** The parameter count is updated */
        array_map(
            function ($value) use (&$param_count) {
                /** If the value starts with param_ */
                if (strpos($value, "param_") === 0)
                    $param_count++;
            },
            $header
        );

        /** The line count */
        $line_count   = 0;
        /** The test data is read */
        while (($line = fgetcsv($fh, 0, "|")) !== false) {
            /** The method parameters */
            $method_params                          = array_slice($line, 0, $param_count);
            /** The parameters are set */
            $test_data[$line_count]['params']       = $this->ConvertArrayParams($method_params, $header);
            /** The return value is set */
            $test_data[$line_count]['return_value'] = $line[$param_count];
            /** The return type is set */
            $test_data[$line_count]['return_type']  = $line[$param_count + 1];
            /** The return value rule is set */
            $test_data[$line_count]['rule']         = $line[$param_count + 2];
            /** Indicates if the return value is JSON */
            $is_ret_val_json                        = UtilitiesFramework::Factory("stringutils")->IsJson(
                                                          $test_data[$line_count]['return_value']
                                                      );
            /** If the return type is array and the return value is a string */
            if ($test_data[$line_count]['return_type'] == "array" && $is_ret_val_json) {
                /** The return value is json decoded */
                $test_data[$line_count]['return_value'] = json_decode($test_data[$line_count]['return_value'], true);
            }
            /** The line count is increased */
            $line_count++;
        }
        
        return $test_data;
    }
    
    /**
     * This function formats the method parameters
     * If a parameter's type is array and its value is given as json string, then value is converted to array
     *
     * @param array $param_values the method parameter values
     * @param array $param_names the method parameter names
     *
     * @return array $formatted_params the formatted method params
     */
    private function ConvertArrayParams(array $param_values, array $param_names) : array
    {
        /** The formatted method params */
        $formatted_params                 = $param_values;
        /** Each method parameter value is checked */
        for ($count = 0; $count < count($param_values); $count++) {
            /** The method parameter value */
            $param_value                  = $param_values[$count];
            /** The parameter name is parsed */
            $temp_arr                     = explode("_", $param_names[$count]);
            /** The parameter type */
            $type                         = $temp_arr[1];
            /** The parameter name */
            $name                         = implode("_", array_slice($temp_arr, 2));
            /** If the parameter type is array and value is in JSON format */
            if ($type == "array" && UtilitiesFramework::Factory("stringutils")->IsJson($param_value)) {
                /** The json value is decoded to array */
                $formatted_params[$count] = json_decode($param_value, true);
            }
        }

        return $formatted_params;
    }
    
    /**
     * This function loads test data
     * It reads the list of folders and files within each folder
     * Each folder name is the name of the object
     * Each file is the name of an object method
     *
     * @return array $test_data the formatted test data
     *    test_count => int the total number of test cases
     *    data => array the test data
     *        object_name => string the name of the class object
     *        method_list => array the list of methods to test
     */
    public function LoadTestDataForBboxTesting() : array
    {
        /** The name of the test data folder */
        $folder_name                 = Config::$config['test']['test_data_folder'];
        /** The list of sub folders is fetched */
        $folder_list                 = scandir($folder_name);
        /** The formatted test data */
        $test_data                   = array("data" => array());
        /** Indicates the method to be tested */
        $only_test                   = Config::$config['test']['only_test'];
        /** The total number of test cases */
        $test_count                  = 0;
        /** If a particular method needs to be tested */
        if ($only_test['object'] != "" && $only_test['method'] != "") {
            /** The object to test */
            $object                       = $only_test['object'];
            /** The method to test */
            $method                       = $only_test['method'];            
            /** The test data is set */
            $test_data['data'][$object]   = array($method);
        }
        else {       
            /** Each folder is checked */
            for ($count = 0; $count < count($folder_list); $count++) {
                /** The folder name */
                $name                             = $folder_list[$count];
                /** If the folder name is '.' or '..', then loop continues */
                if ($name == "." || $name == "..") continue;
                /** The absolute path to the folder */
                $path                             = $folder_name . DIRECTORY_SEPARATOR . $name;
                /** The list of files in the folder */
                $file_list                        = scandir($path);
                /** The list of files is formatted */
                $file_list                        = array_slice($file_list, 2);
                /** The test data is formatted. The file extension is removed */
                $test_data['data'][$name]         = str_replace(".txt", "", $file_list);
            }
        }
        
        /** The required test data */
        
        return $test_data;
    }

    /**
     * This function generates test data files from source code
     * It reads list of all required objects from application config
     * For each object it reads list of methods and generates test data files
     */
    public function GenerateTestData() : void
    {
        /** The list of objects for which the test data will be generated */
        $testobjectlist          = Config::$config['test']['testobjectlist'];
        /** Each object is checked */
        for ($count1 = 0; $count1 < count($testobjectlist); $count1++) {
            /** The object name */
            $object_name         = $testobjectlist[$count1];
            /** The object class */
            $class_name          = Config::$config['requiredobjects'][$object_name]['class_name'];
            /** The leading slash is removed */
            $class_name          = ltrim($class_name, "\\");
            /** The name of the folder is set to the object name */
            $folder_name         = Config::$config['test']['test_data_folder'] . DIRECTORY_SEPARATOR . $object_name;
            /** If the folder does not exist */
            if (!is_dir($folder_name)) {
                /** The folder is created */
                mkdir($folder_name, 0755);
            }
            /** The class methods are fetched */
            $class_methods       = get_class_methods($class_name);
            /** Each class method is checked */
            for ($count2 = 0; $count2 < count($class_methods); $count2++) {
                /* Get a reflection object for the class method */
                $reflect         = new \ReflectionMethod($class_name, $class_methods[$count2]);
                /** The name of the class that declares the method */
                $dec_cname       = $reflect->getDeclaringClass()->getName();
                /** If the method is public and not a constructor and the class name is same as declared class name */
                if ($reflect->isPublic() && $class_name == $dec_cname && $class_methods[$count2] != "__construct") {
                    /** The method's DocBlock comments are parsed */
                    $comments    = UtilitiesFramework::Factory("parser")->ParseMethodDocBlockComments(
                                       $class_name,
                                       $class_methods[$count2]
                                   );
                                   
                    /** The CSV file contents */
                    $csv_data    = array_map(
                                       function($param) {
                                           return "param_" . $param['type'] . "_" . $param['variable_name'];
                                       }, 
                                       $comments['parameters']
                                   );
                    /** The return data is appended */
                    $csv_data[]  = "return_value";
                    $csv_data[]  = "return_type";
                    $csv_data[]  = "rule";                                        
                    /** The file name */
                    $file_name   = $folder_name . DIRECTORY_SEPARATOR . $class_methods[$count2] . ".txt";
                    /** The file handle is created */
                    $fh          = fopen($file_name, "w");
                    /** The data is written to file */
                    fputcsv($fh, $csv_data, "|");
                    /** The file is closed */
                    fclose($fh);
                }
            } 
        }      
        
        /** The output text */
        $console_text            = "\n  Test data was successfully generated\n\n";
      	/** The progress of unit test is displayed */
       	CommandLine::DisplayOutput($console_text);
    }
    
    /**
     * It saves the current request information to database as test data
     */
    public function SaveUiTestData() : void 
    {
        /** The current url */
        $url        = Config::$config["general"]["site_url"] . Config::$config["general"]["request_uri"];
        /** The current application parameters are json encoded */
        $params     = json_encode(Config::$config["general"]["parameters"]);

        /** The mysql table list */
        $table_name = Config::$config["general"]["mysql_table_names"]["test_data"];
		/** The application name */
        $app_name   = Config::$config["general"]["app_name"];
        
        /** The test data that needs to be logged */
        $test_data  = array(
                          "url" => $url,
                          "params" => $params,
                          "app_name" => $app_name,                                   
                          "created_on" => time()
                      );
        
        /** The log data is enclosed in array */
        $log_data   = array($test_data);
        /** The parameters used to create logmanager object */
        $parameters = array("dbinit" => Config::GetComponent("frameworkdbinit"));
        
        /** The condition for checking if log data exists */
        $condition  = array("condition" => "url=? AND params=?", "values" => array($url, $params));
        /** If the log data does not exist */
        if (!UtilitiesFramework::Factory("logmanager", $parameters)->LogDataExists($table_name, $condition)) {
            /** The test data is saved to database */
            UtilitiesFramework::Factory("logmanager", $parameters)->InsertLogData($log_data, $table_name);
        }
    }
    
    /**
     * This function loads test data for the current application
     *
     * @return array $test_data the formatted test data
     *    url => string the page url
     *    params => string the method params in json format
     */
    public function LoadTestDataForUiTesting() : array
    {
        /** The test data for the user interface tests */
        $test_data  = array();
        
        /** The mysql table list */
        $table_name = Config::$config["general"]["mysql_table_names"]["test_data"];
		/** The application name */
        $app_name   = Config::$config["general"]["app_name"];

        /** The DbInit class object */
        $dbinit     = Config::GetComponent("frameworkdbinit");        
        /** The condition for fetching the test data */
        $condition  = array("condition" => " is_checked=0 ORDER BY id ASC", "values" => null);
        /** The parameters used to create logmanager object */
        $parameters = array("dbinit" => $dbinit);
        /** The log data is fetched */
        $test_data  = UtilitiesFramework::Factory("logmanager", $parameters)->GetLogData($table_name, $condition);
        
        return $test_data;
    }
}
