<?php

declare(strict_types=1);

namespace Framework\Utilities\CommentManager;

/**
 * This class provides functions for parsing doc block comments of methods
 * 
 * @category   Main
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class CommentManager
{ 
    /** @var CommentManager $instance The single static instance */
    protected static $instance;   
    /**
     * Used to return a single instance of the class
     *
     * Checks if instance already exists. If it does not exist then it is created. The instance is returned
	 * 
     * @return Reflection static::$instance name the instance of the correct child class is returned 
     */
    public static function GetInstance() : CommentManager
    {        
        if (static::$instance == null) {
            static::$instance = new static();
        }
        return static::$instance;       
    }
	/**
     * It returns a closure that calls the given function and validates it
	 * It allows the function to be called in a safe way
     * 
     * It returns a closure that validates the parameters of the user given function
	 * It then calls the function
	 * After that it validates the return value of the function
	 * In case of validation errors, the closure displays an error message and ends script execution	 
	 * 
	 * @return \Closure $closure an object of class Closure is returned. The closure function calls the given function with the given parameters	 
     */
    public static function GetClosure() : \Closure
    {
    	/** 
    	 * The closure function that validates and calls the user given function
    	 *
    	 * @param array $callback the information about the function to call
    	 *    class_name => string the class name
    	 *    class_object => object the callback object
    	 *    function_name => string the name of the function
    	 *    parameters => array the function parameters
    	 *    context => string the current application context
    	 * @param array $validation_callback the custom function for validating the function parameters
	     *    callback => array the custom callback function. the first index is the object, the second is the function name
	     *    params => array the parameters for the callback function
    	 *
    	 * @return array $result the result of calling the function
    	 *    value => mixed the function return value
    	 */
    	$closure = function (array $callback, array $validation_callback) {

    		/** The Parser object is created */
			$parser                       = new Parser();
			/** The Validator object is created */
			$validator                    = new Validator();
            /** The CommentManager object is created */
			$commentmanager               = new CommentManager();
						
			/** The parsed method comments */			
	    	$parsed_comments              = $parser->ParseMethodDocBlockComments(
	    	                                    $callback['class_name'],
	    	                                    $callback['function_name']
	    	                                );

			/** The application context is validated */
			$validation_result            = $validator->ValidateMethodContext(
			                                    $parsed_comments['internal']['context'],
			                                    $callback['context']
			                                );
			
			/** The validation result is checked */
			if ($validation_result['is_valid'] === false) {
			    /** An exception is thrown */
				throw new \Error("Invalid method context. Details: " . $validation_result['message']);
			}
			
    		/** The test function parameters are validated */
			$validation_result                = $validator->ValidateParameters(
			                                        $callback['parameters'],
			                                        $parsed_comments['parameters'],
			                                        $callback['function_name'],
			                                        $validation_callback			                                        
			                                    );
			
			/** The validation result is checked */
			if ($validation_result['is_valid'] === false) {
			    /** The validation message */
			    $msg  = $validation_result['message'];
    			/** An exception is thrown */
				throw new \Error("Function parameters could not be validated. Details: " . $msg);
			}
			
			/** The parameter values are extracted */
			$parameters                   = array_values($callback['parameters']);
			/** The function whoose comments are to be validated */
			$function_callback            = array($callback["class_object"], $callback["function_name"]);
			/** The test function is called */
			$result                       = call_user_func_array($function_callback, $parameters);
			/** The test function return value is validated */
			$validation_result            = $commentmanager->ValidateMethodReturnValue(
			                                    $callback['class_name'],
			                                    "AddNumbers",
			                                    $result,
			                                    $validation_callback
			                                );
			
			/** The validation result is checked */
			if ($validation_result['is_valid'] === false) {
			    /** The validation message */
			    $msg  = $validation_result['message'];
			    /** The exception is thrown */
				throw new \Error("Function return value could not be validated. Details: " . $msg);
			}
			
			return $result;
		};
		
		return $closure;    	
    }	
   	/**
     * Used to parse and validate the method parameters
	 * 
	 * @param string $class_name the class name
	 * @param string $function_name the function name
	 * @param array $parameters the parameters for the callback function
	 * @param callable $callback the custom validation callback function
	 * 
	 * @return array $validation_result the result of validating the method parameters
	 *    is_valid => boolean indicates if the parameters are valid
	 *    message => the validation message if the parameters could not be validated
     */
    private function ValidateMethodParams(
        string $class_name,
        string $function_name,
        array $parameters,
        ?array $callback = null
    ) : array {
 
        /** An object of class Validator is created */
        $validator                = new Validator();
        /** The Parser class object is created */
        $parser                   = new Parser();           
    	/** The method doc block comments are parsed */
		$parsed_comments          = $parser->ParseMethodDocBlockComments(
		                                $class_name,
		                                $function_name
		                            );
		                            
		/** The parsed parameter information */
		$parsed_parameters        = $parsed_comments['parameters'];
		/** The result of validating the method parameters against the parsed parameters */ 
		$validation_result        = $validator->ValidateParameters(
		                                $parameters,
		                                $parsed_parameters,
		                                $function_name,		                                
		                                $callback
		                            );
		/** If the validation message is not empty */
		if ($validation_result['message'] != "") {
		    /** The message is marked as not valid */
			$validation_result['is_valid']     = false;
		}
		return $validation_result;
    }
    /**
     * Used to validate the given method
     * 
     * It first parses the Doc Block comments of the method
	 * It then validates the parameters
	 * Then it validates the method context if one was set in the long description
	 * 
	 * @param string $class_name the class name
	 * @param string $function_name the function name
	 * @param string $context the current application context
	 * @param array $parameters the parameters for the callback function
	 * @param callable $callback the custom validation callback function
	 * 
	 * @return array $validation_result the result of validating the method parameters
	 *    is_valid => boolean indicates if the parameters are valid
	 *    message => string the validation message if the parameters could not be validated
     */
    public function ValidateMethodAndContext(
        string $class_name,
        string $function_name,
        string $context,
        array $parameters,
        ?array $callback = null) : array
    {
        /** The Parser class object is created */
        $parser                 = new Parser();
    	/** The method doc block comments are parsed */
		$parsed_comments        = $parser->ParseMethodDocBlockComments(
		                              $class_name,
		                              $function_name
		                          );

		/** The parsed parameter information */
		$parsed_parameters      = $parsed_comments['parameters'];
		/** If the function does not accept any parameters */
		if (!isset($parsed_parameters[0])) {
		    /** The function is considered as valid */
		    $validation_result  = array("is_valid" => true, "message" => "");
		}
		else {
		    /** The result of validating the method parameters against the parsed parameters */ 
		    $validation_result  = $this->ValidateMethodParams(
		                              $class_name,
		                              $function_name,
		                              $parameters,
		                              $callback
		                          );
		}
		/** If the parameters are valid and the method context is given */
		if ($validation_result['is_valid'] && isset($parsed_comments['internal']['context'])) {
		    /** The Validator object is created */
			$validator          = new Validator();
		    /** The method context information */
		    $method_context     = $parsed_comments['internal']['context'];
		    /** The method context is validated */
		    $validation_result  = $validator->ValidateMethodContext($method_context, $context);
		}
		return $validation_result;
    }
    
    /**
     * It returns the number of parameters required by the given method
     *
     * @param string $class name of the class that contains the method
     * @param string $method the method name
     *
     * @return int $param_count the number of method parameters. includes both optional and required parameters
     */
    public function GetMethodParamCount(string $class, string $method) : int
    {
        /** The reflection class object is created */
        $reflector   = new \ReflectionClass($class);
        /** The number of method parameters is fetched */    
        $param_count = $reflector->getMethod($method)->getNumberOfParameters();
        
        return $param_count;
    }
    
    /**
     * Used to validate the return value of the given method
     * 
     * It first parses the Doc Block comments of the method
	 * It then validates the given return value
	 *  
	 * @param string $class_name the class name
	 * @param string $function_name the function name
	 * @param mixed $return_value the return value of the function
	 * @param callable $callback a custom function for validating the method response
	 *  
	 * @return array $validation_result the result of validating the method parameters
	 *    is_valid => bool indicates if the parameters are valid
	 *    message => string the validation message if the parameters could not be validated
     */
    public function ValidateMethodReturnValue(
        string $class_name,
        string $function_name,
        $return_value,
        ?array $callback = null
    ) : array {
    
        /** An object of class VariableValidator is created */
        $variable_validator                     = new VariableValidator();
        /** The Parser class object is created */
        $parser                                 = new Parser();
    	/** The method doc block comments are parsed */
		$parsed_comments                        = $parser->ParseMethodDocBlockComments($class_name, $function_name);
		/** The parsed return tag */
		$parsed_return_tag                      = $parsed_comments['return_value'];

		/** The validation callback is formatted */
		$callback_data                          = array("callback" => $callback, "params" => array());
		
		/** If the function returns a value then it is validated */
		if (isset($parsed_return_tag["variable_name"])) {
		    /** The result of validating the return value */
		    $validation_result                  = $variable_validator->ValidateVariable(
		                                              $parsed_return_tag,
		                                              $return_value,
		                                              $function_name,
		                                              $callback
		                                          );
		}
		else {
			$validation_result                  = array("is_valid" => true, "message" => "");
		}
		
	
		return $validation_result;
    }
}
