<?php

declare(strict_types=1);

namespace Framework\Utilities\ErrorManager;

use \Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

/**
 * Provides functions for formatting error information
 *
 * @category   UtilityClass
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
class ErrorFormatter
{
    /** @var string $template_folder_path The path to the error message template folder */
    private $template_folder_path;
    /**
     * Class constructor
     * It sets the path to the error message templates
     */
    public function __construct() 
    {
        /** The path to the folder containing error message templates */
        $this->template_folder_path   = realpath(__DIR__ . DIRECTORY_SEPARATOR . "templates");
    }
    
	/**
     * It generates the method parameter information for the given method
     *
     * @param array $trace the stack trace entry containing method parameter information
     * @param string $class_name name of the class that contains the method
     * @param string $method the method name
     *
     * @return array $method_params the function parameter information
     *    plain => string the function parameters in plain text format
     *    html => string the function parameters in html format
     */
    private function FormatMethodParams(array $trace, string $class_name, string $method) : array
    {
        /** The template utilities object */
        $templateutils                        = UtilitiesFramework::Factory("templateutils");
        /** The commentmanager utilities object */
        $commentmanager                       = UtilitiesFramework::Factory("commentmanager");
        /** The stack trace information for rendering the template file */
        $template_params                      = array("html" => array(), "plain" => array());

        /** If the class name does not exist or the method name contains {closure} */
        if (!class_exists($class_name) || strpos($method, "{closure}") !== false) {
            /** Parameter count is set to 0 */
            $param_count                      = 0;
        }
        else {
            /** The method parameter count */
            $param_count                      = $commentmanager->GetMethodParamCount($class_name, $method);
        }
        
        /** If the method parameter count is 0 */
        if ($class_name == "N.A" || $method == "N.A" || $param_count == 0) {
            /** The function returns */
            return $template_params;
        }
        
        /** Each function parameter is formatted */
        for ($count = 0; $count < $param_count; $count++) {
            
            /** The parameters for a single function */
            $parameters                       = array();
            /** Gets function parameter value from stack trace */
            $param_value                      = $trace['args'][$count] ?? '';

            /** If parameter value is an array then it is converted to string */
            if (is_array($param_value) || is_object($param_value)) 
                $param_value                  = json_encode($param_value);
            /** If the parameter value is numeric then it is converted to a string */
            else if (is_numeric($param_value)) 
                $param_value                  = strval($param_value);
                
            /** If the class exists */
            if (class_exists($class_name)) {
                try {
                    /** An object of ReflectionParameter class is created */
                    $parameters_information   = new \ReflectionParameter(array($class_name, $method) , $count);
                    /** The method name */
                    $parameter_name           = $parameters_information->getName();
                } catch(\Error $e) {
                    /** If the parameter information could not be fetched then the parameter name is set to N.A */
                    $parameter_name           = "N.A";
                }
            }
            /** If the class does not exist */
            else 
                $parameter_name               = "N.A";
            
            /** If the parameter value is an object */
            if (is_object($param_value)) 
                /** The parameter value is replaced with object class name */
                $param_value                  = "Object of class: " . get_class($param_value);
                
            /** The parameter number */
            $parameters['param_number']       = ($count + 1);
            /** The parameter name */
            $parameters['param_name']         = $parameter_name;
            
            /** The parameter values for generating parameter data in plain text format */
            $params_plain                     = $parameters;
            /** The parameter values for generating parameter data in html format */            
            $params_html                      = $parameters;
            
            /** If the parameter value is a string with length more than 120 chars */
            if (is_string($param_value) && strlen($param_value) > 120) {
                /** The string is wrapped to 120 chars */
                $params_plain['param_value']  = wordwrap($param_value, 100, "\n", true);
                /** The string is wrapped to 120 chars */
                $params_html['param_value']   = wordwrap($param_value, 100, "<br/>", true);                
            }
            /** If the parameter value is not a string or it is a string with length less than 120 chars */
            else {
                /** The string is wrapped to 120 chars */
                $params_plain['param_value']  = $param_value;
                /** The string is wrapped to 120 chars */
                $params_html['param_value']   = $param_value;
            }        
            /** The html template parameters are updated */
            $template_params['html'][]        = $params_html;
            /** The plain text template parameters are updated */            
            $template_params['plain'][]       = $params_plain;            
        }
        /** If the trace contained items */
        if (count($template_params['plain']) > 0) {
            /** The name of the plain text template file */
            $plain_file                       = "function_params_plain.html";
            /** The name of the html template file */
            $html_file                        = "function_params_html.html";
            /** The funtion parameters in plain text format */
            $method_params['plain']           = $templateutils->GenerateTemplateFile(
                                                    $this->template_folder_path . DIRECTORY_SEPARATOR . $plain_file,
                                                    $template_params['plain']
                                                );
            /** The funtion parameters in html format */
            $method_params['html']            = $templateutils->GenerateTemplateFile(
                                                    $this->template_folder_path . DIRECTORY_SEPARATOR . $html_file,
                                                    $template_params['html']
                                                );
        }
        /** If the trace was empty */
        else {
            $method_params                    = array("plain" => "", "html" => "");;
        }
        
        return $method_params;
    }
    /**
     * This function is used to format error message text
     *
     * @param array $stack_trace the stack trace for the exception
     *
     * @return array $formatted_stack_trace the complete formatted error message
     *    plain => string the stack trace in plain text format
     *    html => string the stack trace in html format
     */
    private function GetStackTrace(array $stack_trace) : array
    {
        /** The template utils object */
        $templateutils                         = UtilitiesFramework::Factory("templateutils");
        /** The formatted stack trace */
        $formatted_stack_trace                 = array("html" => "", "plain" => "");
        /** The stack trace information. Used to render the error_message.html template file **/
        $template_params                       = array("html" => array(), "plain" => array());
        /** Each strace trace entry is fetched and formatted */
        for ($count = 0; $count < count($stack_trace); $count++) {
            /** A stack trace entry */
            $trace                             = $stack_trace[$count];
            /** The parameters for a single function */
            $params                            = array();
            /** The file name */
            $params['file_name']               = $trace['file'] ?? "N.A";
            /** The line number */
            $params['line_number']             = $trace['line'] ?? "N.A";
            /** The function name */
            $params['function']                = $trace['function'] ?? "N.A";
            /** The class name */
            $params['class_name']              = $trace['class'] ?? "N.A";
            /** The strack item number */
            $params['counter']                 = ($count + 1);            

            /** The function parameters are formatted */
            $method_params                     = $this->FormatMethodParams(
                                                     $trace,
                                                     $params['class_name'],
                                                     $params['function']
                                                 );

            /** The parameters for generating stack trace in plain format */
            $params_plain                      = $params;
            /** The parameters for generating stack trace in html format */
			$params_html                       = $params;
			
			/** The value for the params tag is set for the html stack trace */
			$params_html['params']             = $method_params['html'];
            /** The value for the params tag is set for the plain text stack trace */
			$params_plain['params']            = $method_params['plain'];			
			
            /** The list of parameters for generating html track trace is updated */
            $template_params['html'][]     = $params_html;
            /** The list of parameters for generating plain text track trace is updated */            
			$template_params['plain'][]    = $params_plain;
        }
        /** If the stack trace has data */
        if (count($template_params['html']) > 0) {
            /** The template file for plain text stack trace */
            $plain_file                        = "stack_trace_plain.html";
            /** The template file for html stack trace */
            $html_file                         = "stack_trace_html.html";
            /** The stack trace in html format */
            $formatted_stack_trace["html"]     = $templateutils->GenerateTemplateFile(
                                                    $this->template_folder_path . DIRECTORY_SEPARATOR . $html_file,
                                                    $template_params['html']
                                                 );
            /** The stack trace in plain text format */
            $formatted_stack_trace["plain"]    = $templateutils->GenerateTemplateFile(
                                                    $this->template_folder_path . DIRECTORY_SEPARATOR . $plain_file,
                                                    $template_params['plain']
                                                 );
        }
        else {
            $formatted_stack_trace             = array("plain" => "", "html" => "");
        }
        
        return $formatted_stack_trace;
    }
    /**
     * This function is used to format the given error
     *
     * It uses html template files to format the error
     * It calls the callback object if one is given
     *
     * @param string $error_type the error type
     * @param string $error_level the error level
     * @param string $error_message the error message
     * @param string $error_file the error file name
     * @param int $error_line the error line number
     * @param array $error_context the error context
     *
     * @return array $log_message the error message in plain text and html format
     *    plain => string the error message in plain text
     *    html => string the error message in html format
     */
    public function FormatError(
        string $error_type,
        string $error_level,
        string $error_message,
        string $error_file,
        int $error_line,
        array $error_context) : array
    {
        /** The template utils object */
        $templateutils                        = UtilitiesFramework::Factory("templateutils");
        /** The error log template parameters */
		$template_params                      = array();
		/** The error date */
		$template_params['date']              = date("d-m-Y H:i:s");
		/** The error level */		
		$template_params['error_level']       = (string) $error_level;
		/** The error file */		
		$template_params['error_file']        = $error_file;
		/** The error line */		
		$template_params['error_line']        = (string) $error_line;
		/** The error message */		
		$template_params['error_message']     = $error_message;

		/** If the error context does not contain a stack trace */
		if (!isset($error_context[0]['function'])) {
		    /** The debug back trace is fetched */
		    $stack_trace                          = debug_backtrace();
		    /** The first 3 elements of the trace are removed, since they contain the error manager functions */
		    $stack_trace                          = array_slice($stack_trace, 3);
        }
        /** If the error context contains a stack trace */
		else {
		    $stack_trace                          = $error_context;
		}

        /** The stack trace is fetched */		    
		$stack_trace                              = $this->GetStackTrace($stack_trace);
		
		/** The parameters for generating plain text stack trace */
		$template_params['stack_trace']           = $stack_trace['plain'];
		
		/** The template file for plain text stack trace */
		$plain_file                               = "error_message_plain.html";
		/** The template file for html stack trace */
		$html_file                                = "error_message_full_page_html.html";

		/** The stack message in plain text */
		$log_message['plain']                     = $templateutils->GenerateTemplateFile(
		                                                $this->template_folder_path . DIRECTORY_SEPARATOR . $plain_file,
		                                                $template_params
		                                            );
		                                            
        /** The stack trace formatted as html */	                                            
		$template_params['stack_trace']           = $stack_trace['html'];
		/** The log message in html format */
		$log_message['html']                      = $templateutils->GenerateTemplateFile(
		                                                $this->template_folder_path . DIRECTORY_SEPARATOR . $html_file,
		                                                $template_params
		                                            );
        
        return $log_message;
    }
}
