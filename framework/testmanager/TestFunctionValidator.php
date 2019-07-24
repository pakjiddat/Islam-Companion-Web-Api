<?php

declare(strict_types=1);

namespace Framework\TestManager;

use \Framework\Config\Config as Config;
use \Framework\Application\CommandLine as CommandLine;
use \Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

/**
 * Provides functions for validating data
 *
 * @category   Testing
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
final class TestFunctionValidator
{
    /**
     * It validates the method return value
     * If the return value is not valid, then it displays a message and ends script execution
     *
     * @param mixed $return_value the return value of the method enclosed in array
     * @param mixed $exp_return_value the expected return value of the method
     * @param string $return_type [array,int,float,string] the type of the return value
     * @param string $rule [count,length,equals] the rule to be used for validating the return value
     * @param string $object_name the name of the object to test
     * @param string $method_name the class method to test     
     */
    public function ValidateMethodReturnValue(
        $return_value,
        $exp_return_value,
        string $return_type,
        string $rule,
        string $object_name,
        string $method_name
    ) : void {
        
        /** If the rule is count */
        if ($rule == "count") {
            /** The expected number of items in the return value */
            $exp_item_count           = $exp_return_value['count'];
            /** The comparision operator */
            $operator                 = ($exp_return_value['operator']) ?? "==";

            /** If the expected return value key is set */
            if (isset($exp_return_value['key'])) {
                /** The expected return value key */
                $key                  = $exp_return_value['key'];
                /** The expected return value count */
                $item_count           = count($return_value[$key]);
            }
            else {
                /** The item count for the return value */
                $item_count           = count($return_value);
            }
            
            /** The comparision is evaluated */
            $is_valid                 = eval("return (" . $item_count . $operator . $exp_item_count . ");");
            /** If the comparision is not valid */
            if (!$is_valid) {
                /** The expected value */
                $expected_value       = ($operator . " " . (string) $exp_item_count);
                /** The value mismatch error is shown */
                $this->DisplayValueMismatchError(
                    "Invalid number of items in return value",
                    (string) $item_count,
                    $expected_value,
                    $object_name,
                    $method_name
                );
            }
        }
        /** If the rule is length */
        else if ($rule == "length") {
            /** The return value length is validated */
            $this->ValidateRetValLength($return_value, $exp_return_value, $return_type, $object_name, $method_name);
        }
        /** If the rule is equals */
        else if ($rule == "equals") {
            /** The return value is checked */
            $this->ValidateRetVal($return_value, $exp_return_value, $return_type, $object_name, $method_name);
        }
    }
    
    /**
     * It validates the length of the return value
     *
     * @param mixed $ret_val the method return value
     * @param mixed $exp_ret_val the expected method return value
     * @param string $object_name the object name 
     * @param string $class_name the class name
     */
    private function ValidateRetValLength(
        $return_value,
        $exp_return_value,
        string $return_type,
        string $object_name,
        string $method_name
    ) : void {
    
        /** If the return type is string */
        if ($return_type == "string") {
            /** The expected length value */
            $exp_len            = $exp_return_value;
            /** The length of the return value */
            $return_len         = mb_strlen($return_value);
            /** If the return value does not match expected return value */
            if ($exp_len != $return_len) {
               /** The value mismatch error is shown */
               $this->DisplayValueMismatchError(
                  "The length of the return value does not match the expected length",
                  (string) $return_len,
                  (string) $exp_len,
                  $object_name,
                  $method_name
               );
            }
        }
        /** If the return type is array */
        else if ($return_type == "array") {
            /** The expected value is checked */
            for ($count = 0; $count < count($exp_return_value); $count++) {
                /** The expected value */
                $exp_val                  = $exp_return_value[$count];
                /** The key to check */
                $key                      = $exp_val['key'];
                /** The length of the return value */
                $return_len               = mb_strlen($return_value[$key]);
                /** The expected length */
                $exp_len                  = $exp_val['length'];
                /** If the return value does not match expected return value */
                if ($exp_len != $return_len) {
                    /** The value mismatch error is shown */
                    $this->DisplayValueMismatchError(
                        "The length of the return value does not match the length of the expected value",
                        (string) $return_len,
                        (string) $exp_len,
                        $object_name,
                        $method_name
                    );
                }
            }
        }
    }
    
    /**
     * It checks if the return value matches the expected return value
     *
     * @param mixed $ret_val the method return value
     * @param mixed $exp_ret_val the expected method return value
     * @param string $object_name the object name 
     * @param string $class_name the class name
     */
    private function ValidateRetVal(
        $return_value,
        $exp_return_value,
        string $return_type,
        string $object_name,
        string $method_name
    ) : void {
    
        /** If the return type is int */
        if ($return_type == "int") {
            /** The expected return value is converted to integer */
            $exp_return_value      = (int) $exp_return_value;
            /** The return value is converted to integer */
            $ret_val               = (int) $return_value;
            /** If the return value does not match expected return value */
            if ($ret_val != $exp_return_value) {
                /** The value mismatch error is shown */
                $this->DisplayValueMismatchError(
                    "The return value does not match expected return value",
                    (string) $ret_val,
                    (string) $exp_return_value,
                    $object_name,
                    $method_name
               );
            }
        }
        /** If the return type is bool */
        else if ($return_type == "bool") {
            /** The expected return value is converted to boolean */
            $exp_return_value      = (bool) $exp_return_value;
            /** The return value is converted to boolean */
            $ret_val               = (bool) $return_value;
            /** If the return value does not match expected return value */
            if ($ret_val != $exp_return_value) {
                /** The value mismatch error is shown */
                $this->DisplayValueMismatchError(
                    "The return value does not match expected return value",
                    (string) $ret_val,
                    (string) $exp_return_value,
                    $object_name,
                    $method_name
                );
            }
        }
        /** If the return type is string */
        else if ($return_type == "string") {
            /** The expected return value is converted to integer */
            $exp_return_value       = (string) $exp_return_value;
            /** The return value is converted to integer */
            $ret_val                = (string) $return_value;
            /** If the return value does not match expected return value */
            if ($ret_val != $exp_return_value) {
                /** The value mismatch error is shown */
                $this->DisplayValueMismatchError(
                    "The return value does not match expected return value",
                    $ret_val,
                    $exp_return_value,
                    $object_name,
                    $method_name
                );
            }
        }
        /** If the return type is array */
        else if ($return_type == "array") {
            /** Each returned value is checked in the expected return value data */
            foreach ($exp_return_value as $key => $value) {
                /** If the return value was not found */
                if (!isset($return_value[$key]) || $exp_return_value[$key] != $return_value[$key]) {
                    /** The value mismatch error is shown */
                    $this->DisplayValueMismatchError(
                        "The return value for the key: " . $key . " does not match expected return value",
                        (string) $return_value[$key],
                        (string) $exp_return_value[$key],
                        $object_name,
                        $method_name
                    );
                }                
            }     
        }
    }
    
    /**
     * It displays a message to the user indicating the error
     *
     * @param string $message the error message to display to the console
     * @param mixed $ret_val the method return value
     * @param mixed $exp_ret_val the expected method return value
     * @param string $object_name the object name 
     * @param string $class_name the class name
     */
    private function DisplayValueMismatchError(
        string $message,
        string $ret_val,
        string $exp_ret_val,
        string $object_name,
        string $method_name
    ) : void {
    
        /** The console text */
        $console_text         = "\n    Object: " . $object_name;
        $console_text        .= "\n    Method: " . $method_name;
        $console_text        .= "\n    Message: " . $message;
        $console_text        .= ". Expected: " . $exp_ret_val . " got " . $ret_val . "\n\n";
        /** The console message is displayed */
       	CommandLine::DisplayOutput($console_text);
        /** The script ends */
        die();
    }
    
    /**
     * Used to validate the given html using validator.nu service
     *
     * @param string $html_content the html test to validate
     *
     * @return array $result the results of validation
     *    message => string the response text from validation server
     *    is_valid => bool indicates if the given html text conforms to the html5 standard
     */
    public function ValidateHtml(string $html_content) : array
    {        									
        /** Indicates if the html is valid */
        $is_valid              = false;	
		/** The carriage return and line feed are removed */								
        $html_content          = str_replace("\r", "", $html_content);
        $html_content          = str_replace("\n", "", $html_content);
        /** The url of the html validator */
        $validator_url         = Config::$config['test']['validator_url'] . "/?out=text";
							
        /** The user agent header */
        $ua_header             = "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko)";
        $ua_header             .= " Ubuntu Chromium/70.0.3538.110 Chrome/70.0.3538.110 Safari/537.36";							
        /** The http headers to be sent with the request */							
        $headers               = array(
                                	$ua_header,
                                    "Content-type: text/html; charset=utf-8"
                                );
        /** The html is validated */
        $validation_results    = UtilitiesFramework::Factory("urlmanager")->GetFileContent(
                                     $validator_url,
                                     "POST",
                                     $html_content,
                                     $headers);

        /** If the document was successfully validated */
        if (strpos($validation_results, "The document validates according to the specified schema(s).") !== false) {
            /** The document is marked as not valid */
        	$is_valid = true;
        }
        
        /** The validation results */
        $result                = array("message" => $validation_results, "is_valid" => $is_valid);

        return $result;
    }
}
