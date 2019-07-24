<?php

declare(strict_types=1);

namespace Framework\Utilities\Examples;

use \Framework\Utilities\CommentManager\CommentManager as CommentManager;

error_reporting(E_ALL);
ini_set("display_errors", "1");
include('../autoload.php');

/**
 * Provides functions for test CommentManager package
 *
 * @category   UtilityClassTests
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
final class CommentManagerTest
{
    /** 
	 * This function adds the three numbers given as parameters
	 * It returns the sum of the numbers and a random string
	 * The random string is given as the last parameter
     * {@internal context cli}	  
	 *
	 * @param int $number1 range [1-100] the first number
	 * @param int $number2 range [1-100] the second number
	 * @param int $number3 range [1-100] the third number
	 * @param array $data contains the type of the numbers and a string
	 *    type => string list [integer,float] the type of number to be added
	 *    random_string => string custom a random string that is returned by the function
	 * 
	 * @return array $result the result of the function
	 *    sum => int range [1-50] the sum of the three numbers
	 *    random_string => string the random string
 	*/
    public function AddNumbers(int $number1, int $number2, int $number3, array $data) : array
	{
	   /** The result of adding the three numbers */
	   $sum    = $number1 + $number2 + $number3;
	   /** The result of the function */
	   $result = array("sum" => $sum, "random_string" => $data['random_string']);
	   
	   return $result;
	}
	
	/**
	 * It checks if the given function parameter is valid
     * It signals an error if the length of the random string is larger than 10 characters
	 * 
	 * @param string $param_name the name of the parameter
	 * @param string $param_value the value of the parameter
	 * 
	 * @return array $validation_result the result of validating the method parameters
	 *    is_valid => boolean indicates if the parameters are valid
	 *    validation_message => string the validation message if the parameters could not be validated
     */
    public function CustomValidation(
        string $param_name,
        string $param_value
    ) : array {
    
		/** The result of validating the parameter */
	    $validation_result                     = array("is_valid" => true, "message" => "");
		/** If the random_string variable needs to be validated */
		if ($param_value == "random_string") {			
		    /** The length of the random string parameter value */
	        $string_length                     = strlen($param_value);
			/** If the length of the random string is larger than 10 characters then the string is marked as not valid */
			if ($string_length > 80) {
			    /** The validation message is set */
				$validation_result['message']  = "Random string length must be less than 80 characters";
				/** The parameter value is marked as not valid */
				$validation_result['is_valid'] = false;
			}
		}
		
	    return $validation_result;
	}
	
	/**
     * CommentManager function test
     * Used to test CommentManager class
     * It uses reflection to validate the function parameters
     * It can be used to validate complex parameters such as objects and arrays
     * It ensures that the function is called with the correct parameters
     */
    public function TestSafeFunctionCaller() : void
    {
        /** The function that provides custom validation for the test function parameters */
        $custom_validation_callback = array($this, "CustomValidation");
        /** The safe_function_caller closure is fetched from the CommentManager class */
        $safe_function_caller       = CommentManager::GetClosure();
        /** The parameters for the test function */
        $parameters                 = array(
            						      "number1" => 30,
									      "number2" => 10,
									      "number3" => 10,
         								  "data" => array(
                 						    		    "type" => "integer",
                                                        "random_string" => "The result of adding the three integers is: "
									                )
								      );
	    /** The callback */
	    $callback                   = array(
	                                      "class_name" => get_class(),
	                                      "class_object" => $this,
									      "function_name" => "AddNumbers", 
									      "parameters" => $parameters, 
									      "context" => "cli"
								      );
        try {								          
            /** The test function is called through the safe function caller */
            $result                 = $safe_function_caller($callback, $custom_validation_callback);			
        }
        catch (\Error $e) {
            /** The error message is displayed and the script ends */
            die($e->getMessage());
        }
        /** The result of adding the numbers is displayed */
        echo "\n\n" . $result['random_string'] . $result['sum'] . "\n\n\n";
    }
}

/** An object of class CommentManager is created */
$comment_parser                         = new CommentManagerTest();
/** The test function is called */
$comment_parser->TestSafeFunctionCaller();
