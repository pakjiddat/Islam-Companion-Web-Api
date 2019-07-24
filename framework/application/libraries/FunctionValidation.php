<?php

declare(strict_types=1);

namespace Framework\Application\Libraries;

use \Framework\Config\Config as Config;
use \Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

/**
 * This class provides functions for validating methods using information in the method Doc Block comments
 *
 * @category   Libraries
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
class FunctionValidation
{
    /**
     * It checks if the given function parameter is valid
     *
	 * @param string $param_name the name of the parameter
	 * @param string $param_value the value of the parameter
     *
     * @return array $validation_result the result of validating the method parameters
     *    is_valid => boolean indicates if the parameters are valid
     *    message => string the validation message if the parameters could not be validated
     */
    public function ValidateFunctionParameter(
        string $param_name,
        string $param_value
    ) : array {
    
        /** The result of validating the parameter */
        $validation_result = array(
            "is_valid" => true,
            "validation_message" => ""
        );
        
        return $validation_result;
    }
    /**
     * Used to validate the parameters passed to the given method
     *
     * It checks if the parameter values are valid according to the Doc Block comments of the method
     *
     * @param array $method the method to validate
     *    class => string the class name
     *    method => string the method name
     * @param array $parameters the parameters for the method callback function
     * @param array $callback a custom function for validating the method parameters
     *    callback => array the custom callback function. the first index is the object, the second is the function name
	 *    params => array the parameters for the callback function     
     *
     * @return array $validation_result the result of validating the method parameters
     *    is_valid => boolean indicates if the parameters are valid
     *    validation_message => string the validation message if the parameters could not be validated
     */
    public function ValidateMethodParameters(
        array $method,
        array $parameters,
        ?array $callback = null
    ) : array {
    
        /** The current application context */
        $context                    = Config::$config["general"]["context"];
        
        /** The result of validating the parameters against the information in the Doc Block comments */
        $validation_result          = UtilitiesFramework::Factory("commentmanager")->ValidateMethodAndContext(
                                          $method['class'], 
                                          $method['method'],
                                          $context, 
                                          $parameters,
                                          $callback
                                      );

        /** If the method parameters could not be validated */
        if (!$validation_result['is_valid']) {
            /** The validation message */
            $message               = array("message" => $validation_result["message"], "result" => "error");
            /** The message is json encoded */
            $message               = json_encode($message);
            /** Script ends */
            die($message);
        }

        return $validation_result;
    }
    /**
     * It checks if the return value is valid according to the Doc Block comments of the method
     *
     * @param array $method the method to validate
     *    class => string the class name
     *    method => string the method name
     * @param mixed $response the method response value to validate
     * @param array $callback a custom function for validating the method response
     *    callback => array the custom callback function. the first index is the object, the second is the function name
	 *    params => array the parameters for the callback function     
     *
     * @return array $validation_result the result of validating the method parameters
     *    is_valid => boolean indicates if the parameters are valid
     *    validation_message => string the validation message if the parameters could not be validated
     */
    public function ValidateMethodReturnValue(
        array $method,
        $response,
        ?array $callback = null
    ) : array {

        /** The return value is validated against the information in the Doc Block comments */
        $validation_result         = UtilitiesFramework::Factory("commentmanager")->ValidateMethodReturnValue(
                                        $method['class'], 
                                        $method['method'],
                                        $response,
                                        $callback
                                     );
        /** If the method parameters could not be validated */
        if (!$validation_result['is_valid']) {
            /** The validation message */
            $message               = array("message" => $validation_result["message"], "result" => "error");
            /** The message is json encoded */
            $message               = json_encode($message);
            /** Script ends */
            die($message);
        }

        return $validation_result;
    }
    
    /**
     * Used to sanitize the given text
     * It filters the given text according to the given filter type
     *
     * @param string $text the text to sanitize
     * @param string $type [email,plain text,url]
     *
     * @return string $sanitized_text the sanitized text
     */
    public function SanitizeText(string $text, string $type) : string
    {
        /** The sanitized text */
        $sanitized_text = "";
        /** If the type is email */
        if ($type == "email") {
            /** The text is sanitized */
            $sanitized_text = filter_var($text, FILTER_VALIDATE_EMAIL);
        }
        /** If the type is plain text */
        else if ($type == "plain text") {
            /** The text is sanitized */
            $sanitized_text = strip_tags($text);
        }
        /** If the type is url */
        else if ($type == "url") {
            /** The text is sanitized */
            $sanitized_text = filter_var($text, FILTER_SANITIZE_URL);
        }
        
        return $sanitized_text;
    }
    
    /**
     * Used to sanitize the variable
     * It applies the stip_tags function to the data
     * If the data is an array the strip_tags function is applied to each array element recursively
     *
     * @param mixed $data the data to sanitize
     *
     * @return mixed $sanitzed_data the sanitized data
     */
    public function SanitizeData($data)
    {
        /** The sanitized data */
        $sanitized_data = "";
        /** Indicates if the given data is a json string */
        $is_json        = false;
        /** If the variable is a string */
        if (is_string($data)) {
            /** If the data is a json string */
            if (UtilitiesFramework::Factory("stringutils")->IsJson($data)) {
                /** The data is json decoded */
                $data    = json_decode($data, true);
                /** The data is marked as valid json */
                $is_json = true;
            }
        }
        
        
        /** If the variable is a string */
        if (is_string($data)) {
            /** The text is sanitized */
            $sanitized_data = strip_tags($data);
        }
        /** If the variable is an array */
        else if (is_array($data)) {
            /** Each array value is sanitized */
            foreach ($data as $key => $value) {
                /** The array element is sanitized */
                $data[$key] = $this->SanitizeData($value);
            }
            /** The sanitized data is set */
            $sanitized_data = $data;
        }
        /** If the variable is not a string or array */
        else {
            /** The variable is returned */
            return $data;
        }
  
        /** If the data is valid json */
        if ($is_json) {
            /** The data is json encoded */
            $sanitized_data = json_encode($sanitized_data);
        }
        
        return $sanitized_data;
    }
}
