<?php

declare(strict_types=1);

namespace Framework\Utilities\CommentManager;

use \Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

/**
 * Provides functions for validating the value of a method parameter or return value
 * The value is validated against the Doc Block comment for the parameter or return value
 *
 * @category   UtilityClass
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General private License, version 2
 */
final class VariableValidator
{
	/**
     * It checks if the variable value matches the given type	 
     * It checks if the variable value is in given range provided the range is given
	 * 
	 * @param array $tag_details details of the parsed parameter tag
	 * @param mixed $param_value the value of the method parameter
	 * @param string $function_name the name of the function
	 * @param array $callback_data optional the custom validation callback function
	 *    callback => array the custom callback function. the first index is the object, the second is the function name
	 *    params => array the parameters for the callback function
	 * 
	 * @return array $validation_result the result of validating the method parameters
	 *    is_valid => bool indicates that the parameter value is valid
	 *    message => string the message describing the result of validation
     */
    public function ValidateVariable(
        array $tag_details,
        $param_value,
        string $function_name,
        ?array $callback_data = null
    ) : array {
    
    	/** The result of validating the method */
    	$validation_result               = array("is_valid" => true, "message" => "");
        /** The variable name */
        $variable_name                   = $tag_details['variable_name'];
    	/** If the validation rule is custom */
		if ($tag_details["rule"] == "custom") {
		    /** The name of the variable to validate is set */
		    $callback_data["params"]     = array($variable_name, $param_value, $function_name);
		    /** The callback parameters are sorted */
		    ksort($callback_data["params"]);
			/** The validation result. The custom callback function is called */
			$validation_result           = call_user_func_array($callback_data["callback"], $callback_data["params"]);
	    }
        else {	    
		    /** If the parameter type is an integer */
		    if ($tag_details['type'] == "int") {
		        /** The parameter value is converted to int */
		        $param_value                    = (int) $param_value;
		        /** The validation message is set */
		        $validation_result["message"]   = $this->ValidateIntVariable($tag_details, $param_value);
		    }		
		    /** If the parameter type is string */
		    else if ($tag_details['type'] == "string") {
		        /** The validation message is set */
		        $validation_result["message"]   = $this->ValidateStringVariable($tag_details, $param_value);
		    }		
		    /** If the parameter type is bool */
		    else if ($tag_details['type'] == "bool") {
		        /** If the parameter value is not a bool */
		        if (!is_bool($param_value))
		            /** The validation message is set */
		            $validation_result["message"] = "Parameter: " . $variable_name . " is not a bool";		    
		    }		
		    /** If the parameter type is object */
		    else if ($tag_details['type'] == "object") {
		        /** If the parameter value is not an object */
		        if (!is_object($param_value))
		            /** The validation message is set */
		            $validation_result["message"] = "Parameter: " . $variable_name . " is not an object";
		    }		
		    /** If the parameter type is array */
		    else if ($tag_details['type'] == "array") {
		        /** The validation message is set */
		        $validation_result["message"] = $this->ValidateArrayVariable(
		                                            $tag_details,
		                                            $param_value,
		                                            $function_name,
		                                            $callback_data
		                                        );
		    }
		    /** If the parameter type is json */
		    else if ($tag_details['type'] == "json") {
		         /** If the parameter value is not in json format */
		        if (!UtilitiesFramework::Factory("stringutils")->IsJson($param_value)) {
		            /** The validation message is set */
		            $validation_result["message"] = "Parameter: " . $variable_name . " is not in json format";
		        }
		        else {
		            /** The parameter value is json decoded */
		            $param_value                  = json_decode($param_value, true);
		            /** The validation message is set */
		            $validation_result["message"] = $this->ValidateArrayVariable(
		                                                $tag_details,
		                                                $param_value,
		                                                $function_name,
		                                                $callback_data
		                                            );
                }		                                        
		    }
		}

		/** If the validation message is empty */				
		if ($validation_result["message"] != "") {
		    /** The result of validation is set to true */
		    $validation_result["is_valid"] = false;
	    }
	    
		return $validation_result;
    }
    
    /**
     * Used to validate the value of an integer variable
	 * 
	 * @param array $tag_details details of the parsed parameter tag
	 * @param int $param_value the value of the method parameter
	 * 
	 * @return string $message the result of validation
     */
    private function ValidateIntVariable(array $tag_details, int $param_value)
    {
        /** The result of validation */
        $message        = "";
        /** If the parameter value is not an integer */
		if (!is_numeric($param_value)) {
		    /** The validation message is set */
		    $message    = "Parameter: " . $tag_details['variable_name'] . " is not an integer";
        }		    
	    /** If the validation rule is range */
		else if ($tag_details["rule"] == "range") {			
		    /** The minimum and maximum values for the parameter */
		    list($min_value, $max_value)   = explode("-", $tag_details['rule_data']);
		    /** If the parameter value is out of range then the error message is set */
		    if ($param_value < $min_value || $param_value > $max_value) {
		        /** The validation message is set */
		        $message  = "The value: " . $param_value . " for the parameter: ";
		        $message .= $tag_details['variable_name'] . " is out of range";
	        }
        }
        
        return $message;
    }
    
    /**
     * Used to validate the value of a string variable
	 * 
	 * @param array $tag_details details of the parsed parameter tag
	 * @param string $param_value the value of the method parameter
	 * 
	 * @return string $message the result of validation
     */
    private function ValidateStringVariable(array $tag_details, string $param_value)
    {
        /** The result of validation */
        $message                = "";
        
        /** If the parameter value is not a string */
		if (!is_string($param_value)) {
		    /** The validation message is set */
		    $message  = "Parameter: " . $tag_details['variable_name'] . " is not a string";
		}
	    /** If the validation rule is list */
		else if ($tag_details["rule"] == "list") {
		    /** The possible values for the string. The values must be separated with ',' */
		    $possible_string_values  = explode(",", $tag_details['rule_data']);
		    /** If the parameter value is not one of the possible values then the error message is set */
		    if (!in_array($param_value, $possible_string_values)) {
		        /** The validation message is set */
		        $message  = "Parameter value: " . $param_value . " for the parameter: " .
		                               $tag_details['variable_name'] . " is not an allowed value. " . 
			                           "Allowed values: " . $tag_details['rule_data'];
			}
        }
        /** If the validation rule is email */
		else if ($tag_details["rule"] == "email") {
		    /** If the parameter value is not one of the possible values then the error message is set */
		    if (!filter_var($param_value, FILTER_VALIDATE_EMAIL)) {
		        /** The validation message is set */
		        $message  = "Parameter value: " . $param_value . " for the parameter: " .
		                               $tag_details['variable_name'] . " is not a valid email";
			}
        }
        
        return $message;
    }
    
    /**
     * Used to validate the value of an array variable
	 * 
	 * @param array $tag_details details of the parsed parameter tag
	 * @param mixed $param_value the value of the method parameter
	 * @param string $function_name the name of the function	 
	 * @param array $callback_data the custom validation callback function
	 *    callback => array the custom callback function. the first index is the object, the second is the function name
	 *    params => array the parameters for the callback function
	 * 
	 * @return string $message the result of validation
     */
    private function ValidateArrayVariable(
        array $tag_details,
        $param_value,
        string $function_name,        
        ?array $callback_data = null
    ) :string {
    
        /** The result of validation */
        $message               = "";
        /** If the parameter value is not an array */
	    if (!is_array($param_value)) {
		    /** The validation message is set */
		    $message = "Parameter: " . $tag_details['variable_name'] . " is not an array";
        }		    
	    /** If the parameter value range is not given */
		else if (isset($tag_details['values'])) {		      
		    /** Each parsed comment is checked */
		    for ($count = 0; $count < count($tag_details['values']); $count++) {
		      	/** The sub option name */
		       	$sub_option_name      = $tag_details['values'][$count]['variable_name'];		        	
		       	/** An array element */
		   	    $array_element        = $tag_details['values'][$count];
		        /** The associative array is validated */
		        $message              = $this->ValidateAssociativeArray(
		                                    $sub_option_name,
		                                    $param_value,
		                                    $array_element,
		                                    $function_name,
		                                    $callback_data
		                                );
		        /** If the array element is not valid, then the loop ends */
		        if ($message != '') break;
	        }
        }
       
        return $message;
    }
    
    /**
     * Used to validate the value of a given associative array key
     * If the array is an array of associative array, then the value of each array is checked
	 * 
	 * @param string $key_name the name of the key to validate
	 * @param array $array_values the associative array values. it can be an array of associative arrays
	 * @param array $tag_details the parsed array tag element details
	 * @param string $function_name the name of the function	 
	 * @param array $callback_data the custom validation callback function
	 *    callback => array the custom callback function. the first index is the object, the second is the function name
	 *    params => array the parameters for the callback function
	 * 
	 * @return string $message the result of validation
     */
    private function ValidateAssociativeArray(
        string $key_name,
        array $array_values,
        array $tag_details,
        string $function_name,
        ?array $callback_data = null
    ) : string {
    
        /** The result of validation */
        $message         = "";
        /** If the array has numeric index and each element contains an associative array */
		if (isset($array_values[0][$key_name])) {
            /** Each array element is validated */
            for ($count = 0; $count < count($array_values); $count++) {
		        /** If the array element does not contain the string index */	        	
			    if (!isset($array_values[$count][$key_name])) {
			        /** The validation message is set */
		   	        $message  = "Array element: " . $key_name . " could not be found";
		   	        /** The loop ends */
					break;
                }
		   	    /** The array element value is validated */
				$validation_result      = $this->ValidateVariable(
				                              $tag_details,
				                              $array_values[$count][$key_name],
				                              $function_name,
				                              $callback_data
				                          );
		        /** If the validation message is not empty then it is updated and the loop ends */
		        if ($validation_result['message'] != "") {
			        $message = "Invalid value: " . var_export($array_values[$count][$key_name], true);
			        $message .= " for array element: " . $tag_details['variable_name'];
			        $message .= ". Details: " . $message ;
			        /** The loop ends */
				    break;
				}	
            }
        }
		/** If the array is an associative array */
		else {
            /** If the array element does not contain the string index */	        	
			if (!isset($array_values[$key_name])) {
                /** The validation message is set */
                $message   = "Array element: " . $key_name . " could not be found";		   	    
            }

		   	/** The array element value is validated */
			$validation_result        = $this->ValidateVariable(
			                                $tag_details,
			                                $array_values[$key_name],
			                                $function_name,
			                                $callback_data
			                            );
					    															          
		    /** If the validation message is not empty then it is updated and the loop ends */
			if ($validation_result['message'] != "") {
			    $message = "Invalid value: " . var_export($array_values[$key_name], true);
			    $message .= " for array element: " . $tag_details['variable_name'];
			    $message .= ". Details: " . $validation_result["message"];
		    }
        }
       	
       	return $message;  					
    }
}
