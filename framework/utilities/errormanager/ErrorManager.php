<?php

declare(strict_types=1);

namespace Framework\Utilities\ErrorManager;

/**
 * This class provides functions for managing errors and exception
 *
 * @category   UtilityClass
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class ErrorManager
{
    /** @var ErrorManager $instance The single static instance */
    protected static $instance;
    /** Contains the error level. e.g E_WARNING */
    private $error_level   = 0;
    /** @var string $error_message The error message */
    private $error_message = 'N.A';
    /** @var string $error_file The error file */
    private $error_file    = 'N.A';
    /** @var int $error_line The error line */
    private $error_line    = 0;
    /** @var array $error_context The error context */
    private $error_context = 'N.A';
    /** @var string $type The type of the error. i.e error or exception */
    private $type          = '';
    /** @var array $email_information The email information used to send the email */          
    private $email_information;
    /** @var string $ignored_folders The list of folders to exclude from error handling */
    private $ignored_folders;
    /** @var Exception $first_exception_obj The original exception object that raised the first exception */
    private $first_exception_obj;
    /** @var Exception $exception_obj The exception object included with the exception */
    private $exception_obj;
    /** @var callback $custom_error_handler Custom error handler call back */
    private $custom_error_handler;
    /** @var string $use_plain_text_format Used to indicate if the displayed error message should be in plain text format */
    private $use_plain_text_format;
    /**
     * Used to return a single instance of the class
     *
     * Checks if instance already exists. If it does not exist then it is created. The instance is returned
     *
     * @param array $parameters it contains the config information for the ErrorManager class object
     *    ignored_folders => string if a script's path lies in the list of ignored folders, then its exceptions will not be handled
     *    use_plain_text_format => boolean used to indicate if the displayed error message should be in plain text format
     *    custom_error_handler => callback used to specify a custom error handling function
     *    shutdown_function => callback used to specify a custom shutdown function. it will be called when the current script ends
     *    email_information => array the email information for sending error email
     *        from_email => string the email address from which the email should be sent
     *        to_email => string the email address to which the email should be sent
     *
     * @return ErrorManager static::$instance name the single instance of the class is returned
     */
    public static function GetInstance(array $parameters) : ErrorManager
    {
        if (self::$instance == null) {
            self::$instance = new self($parameters);
        }
        return self::$instance;
    }
    /**    
     * Used to implement Singleton class. It initialize the object variables from the constructor parameters
     *
     * @param array $parameters it contains the config information for the ErrorManager class object
     *    ignored_folders => string if a script's path lies in the list of ignored folders, then its exceptions will not be handled
     *    use_plain_text_format => boolean used to indicate if the displayed error message should be in plain text format
     *    custom_error_handler => callback used to specify a custom error handling function
     *    shutdown_function => callback used to specify a custom shutdown function. it will be called when the current script ends
     *    email_information => array the email information for sending error email
     *        from_email => string the email address from which the email should be sent
     *        to_email => string the email address to which the email should be sent
    */
    protected function __construct(array $parameters) 
    {
        /** The custom error handler */
        $this->custom_error_handler   = $parameters['custom_error_handler'] ?? '';
        /** The list of folders to exclude from error handling */
        $this->ignored_folders        = $parameters['ignored_folders'] ?? '';
        /** Used to indicate if application is in development mode */
        $this->use_plain_text_format  = $parameters['use_plain_text_format'] ?? true;
        /** The information used to send error email */
        $this->email_information      = $parameters['email'] ?? '';

        /** The error handler function is registered */
        set_error_handler(array($this, "ErrorHandler"));
        /** The exception handler function is registered */
        set_exception_handler(array($this, "ExceptionHandler"));
        /** The shutdown handler function is registered */
        register_shutdown_function(array($this, "ShutdownFunction"));
       
        /** The custom shutdown function is registered */
        if (isset($parameters['shutdown_function']) && $parameters['shutdown_function'] != "" && !(is_callable($parameters['shutdown_function']))) throw new \Error("Invalid custom shutdown function");
        /** If the custom shutdown function is callable, then it is registered */
        else if ($parameters['shutdown_function'] != "") register_shutdown_function($parameters['shutdown_function']);
    }
    
    /**
     * Error handling function
     *
     * The function can be registered as an error handler using set_error_handler php function
     * The function sets the error variables of the object and logs the error
     *
     * @param int $error_level the error level
     * @param string $error_message the error message
     * @param string $error_file the error file name
     * @param int $error_line the error line number
     * @param array $error_context the error context
     */
    public function ErrorHandler(int $error_level, string $error_message, string $error_file, 
                                int $error_line, array $error_context) : void
    {
        /** The object error properties are set to the error information */
        $this->error_level   = (string) $error_level;
        $this->error_message = $error_message;
        $this->error_file    = $error_file;
        $this->error_line    = $error_line;
        $this->error_context = $error_context;
        $this->type          = "Error";
        /** The error message is logged */
        $this->LogError();
    }
    /**
     * Exception handling function
     *
     * The function can be registered as an exception handler using set_exception_handler php function
     * The function sets the error variables of the object including the object that raised the exception and logs the error
     *
     * @param \Throwable $exception_obj the exception object that contains the error information
     */
    public function ExceptionHandler(\Throwable $exception_obj) : void
    {
        /** The last exception objects are set to the exception_obj property */
        $this->first_exception_obj = $this->exception_obj = $exception_obj;
        /** The first object that raised the exception is fetched */
        while ($e = $this->first_exception_obj->getPrevious()) {
            /** The first exception object is set */
            $this->first_exception_obj = $e;
        }
        /** The exception object is set to the first exception object */
        $this->exception_obj = $this->first_exception_obj;
        /** The error level is set */
        $this->error_level   = (string) $this->first_exception_obj->getCode();
        /** The exception message is set */
        $this->error_message = $this->first_exception_obj->getMessage();
        /** The error file is set */
        $this->error_file    = $this->first_exception_obj->getFile();
        /** The error line is set */
        $this->error_line    = $this->first_exception_obj->getLine();
        /** The error context is set */
        $this->error_context = $this->first_exception_obj->getTrace();
        /** The error type is set */
        $this->type          = "Exception";
        /** The exception is logged */
        $this->LogError();
    }
    /**
     * This function is to be registered as a shutdown handling function
     * It is called after the script execution ends
     */
    public function ShutdownFunction() : void
    {
        /** The last error message is fetched */
        $error = error_get_last();
        /** If there was an error then it is handled using the ErrorHandler function */
        if (isset($error["type"])) {
            $this->ErrorHandler(
                $error["type"],
                $error["message"],
                $error["file"],
                $error["line"],
                array("Fatal error in script")
            );
        }
    }
    
    /**
     * It calls the custom error handler if one is given
     * If a custom error handler is not given, then the error is emailed and script execution ends
     */
    private function LogError() : void
    {
        try {
            /** If the error file is in one of the folders to ignore */
            if ($this->ignored_folders != "" && strpos($this->error_file, $this->ignored_folders) !== false) {
                /** The function returns */
                return;
            }
         
            /** The email format is set to plain if use_plain_text_format was set to true */
		    $message_format                  = ($this->use_plain_text_format) ? "plain" : "html";

            /** The ErrorFormatter class object is created */
            $errorformatter                  = new ErrorFormatter($this->use_plain_text_format);
            /** The error message is returned */
            $log_message 					 = $errorformatter->FormatError(
            														$this->type, 
            														$this->error_level, 
										                            $this->error_message, 
										                            $this->error_file, 
										                            $this->error_line, 
										                            $this->error_context
                                               );
                             
            /** If a custom error handling function is defined then it is called */
            if ($this->custom_error_handler && is_callable($this->custom_error_handler)) {
                $error_parameters = array(
                    "error_level" => $this->error_level,
                    "error_message" => $this->error_message,
                    "error_file" => $this->error_file,
                    "error_line" => $this->error_line,
                    "error_details" => $log_message[$message_format],
                    "error_type" => $this->type,
                    "error_html" => $log_message["html"]
            	);
            	
		        /** Calls the user defined error handler if one is defined */
		        call_user_func_array($this->custom_error_handler, array(
		            $log_message[$message_format],
		            $error_parameters
		        ));
		    }   
            /** If the custom error handler is defined but is not valid then an exception is thrown */
            else if ($this->custom_error_handler) 
                throw new \Error("Invalid custom error handler type given");
            /** If the custom error handling function is not defined */
            else {                                                
                /** If the email address is given */
                if ($this->email_information['to_email'] != "") {
                    /** The parameters for the email object */
                    $parameters = array("backend" => "mail", "params" => "");
                    /** An object of the email class is created */
                    $email      = UtilitiesFramework::Factory("email", $parameters);
                    /** The email is sent to the user */
                    $email->SendEmail(
                        $this->email_information['from_email'],
                        $this->email_information['to_email'],
                        "Pak Php Framework has reported an error !",
                        $error_message
                    );
                }
                /** The error message is displayed and program execution ends */
                die($log_message[$message_format]);
            }
        }
        catch(\Error $e) {
            die($e->GetMessage());
        }
    }
}
?>
