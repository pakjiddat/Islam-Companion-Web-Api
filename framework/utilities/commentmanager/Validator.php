<?php

declare(strict_types=1);

namespace Framework\Utilities\CommentManager;

/**
 * Provides functions for validating function parameters and return values
 *
 * @category   UtilityClass
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General private License, version 2
 */
final class Validator
{		
	/**
     * Used to validate the given parameters
     * 
     * It checks if the given parameters are valid
	 * 
     * @param array $param_values the value of the method parameters
	 * @param array $tag_details the parsed param Doc Block comments
	 * @param array $callback optional the custom callback function
     *    callback => array the custom callback function. the first index is the object, the second is the function name
	 *    params => array the parameters for the callback function   	 
	 * 
	 * @return array $validation_result the result of validating the method parameters
	 *    is_valid => bool indicates whether the parameters are valid
	 *    message => string the validation message if the parameters could not be validated
     */
    public function ValidateParameters(
        array $param_values,
        array $tag_details,
        string $function_name,
        ?array $callback = null) : array
    {
        /** An object of class VariableValidator is created */
        $variable_validator                         = new VariableValidator();
    	/** The result of validating the method */
    	$validation_result                          = array("is_valid" => false, "message" => "");
		/** Each parameter is checked */
		for ($count = 0 ; $count < count($tag_details); $count++) {
			/** The parameter name */
			$param_name                             = $tag_details[$count]['variable_name'];
			/** If the parameter name does not exist then an error message is set */		
			if (!isset($param_values[$param_name])) {
				/** The validation message is set */
				$validation_result["message"]       = "Value not given for the parameter: " . $param_name;
				/** The validation result is returned */
				return $validation_result;
			}
			/** If the parameter name matches the parsed parameter name */
			else {
				/** The type of the parameter */
				$variable_type                      = $tag_details[$count]['type'];
				/** The variable is validated */				
				$validation_result                  = $variable_validator->ValidateVariable(
				                                          $tag_details[$count],
				                                          $param_values[$param_name],
				                                          $function_name,
				                                          $callback
				                                      );
			
				/** If the validation message is not empty */
				if ($validation_result['message'] != "") {
				    /** The validation result is returned */
				    return $validation_result;
		        }
			}
		}
		/** The result of validation is set to true and the validation result is returned */
		$validation_result["is_valid"]              = true; 
					
		return $validation_result;
    }
	/**
     * Used to validate the method context
     * 
     * It checks if the method can be called in the current application context
	 * It checks if the application context occurs in the list of allowed method contexts
	 * 
	 * @param string $method_context [any,api,cli,web] the list of allowed context values for the method
	 * @param string $context the current application context
	 * 
	 * @return array $validation_result the result of validating the method context
	 *    is_valid => bool indicates if the context is valid
	 *    message => string the result of validating the method context
     */
    public function ValidateMethodContext(string $method_context, string $context) : array
    {
    	/** The validation result */
		$validation_result                     = array("is_valid" => false, "message" => "");
    	/** The method context list */
		$method_context_list                   = explode(",", $method_context);
		/** If the application context is not in the list of allowed contexts for the method and it is not equal to 'any' */
		if (strpos($method_context, "any") === false && !in_array($context, $method_context_list)) {
			/** The validation message is set */
			$validation_result['message']      = "The method context: " . $method_context;
			$validation_result['message']      .= " does not allow the method to be called in the";
			$validation_result['message']      .= " current application context: " . $context;
		}
		/** If the validation message has not been set */
		if ($validation_result['message'] == "") {
		    /** The is_valid property is set to true */
		    $validation_result['is_valid']     = true;
		}
		
		return $validation_result;
    }
}
