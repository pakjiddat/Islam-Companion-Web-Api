<?php

declare(strict_types=1);

namespace Framework\Application;

use \Framework\Config\Config as Config;

/**
 * This class provides the base class for applications that require command line interface
 *
 * It provides functions that are commonly used by command line applications
 *
 * @category   Application
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
abstract class CommandLine extends \Framework\Application\Application
{
    /**
     * Used to echo the text in the given color
     * The text may be formatting using attribute tags     
     * For example use <bold>test</bold> to make text bold
     * Supported formatting attributes are given assigned to $set_codes
     *
     * @param string $text the text to echo
     * @param string $color the text color
     */
    final public static function DisplayOutput($text, $color = "white") : void 
    {
        /** The color codes */
        $color_codes        = array(
                                  "default" => 39,
                                  "black" => 30,
                                  "red" => 31,
                                  "green" => 32,
                                  "yellow" => 33,
                                  "blue" => 34,
                                  "magenta" => 35,
                                  "cyan" => 36,
                                  "lightgray" => 37,
                                  "darkgray" => 90,
                                  "lightred" => 91,
                                  "lightgreen" => 92,
                                  "lightyellow" => 93,
                                  "lightblue" => 94,
                                  "lightmagenta" => 95,
                                  "lightcyan" => 96,
                                  "white" => 97
                            );
        /** The codes for setting formatting attributes */
        $set_codes          = array(
                                  "bold" => 1,
                                  "dim" => 2,
                                  "underline" => 4,
                                  "blink" => 5,
                                  "reverse" => 7,
                                  "hidden" => 8
                              );
                        
        /** The codes for resetting formatting attributes */
        $reset_codes        = array(
                                  "all" => 0,
                                  "bold" => 21,
                                  "dim" => 22,
                                  "underline" => 24,
                                  "blink" => 25,
                                  "reverse" => 27,
                                  "hidden" => 28 
                              );
                        
        /** If the color is not supported, then an exception is thrown */
        if (!isset($color_codes[$color]))
            throw new \Error("Invalid color: " . $color);                        
                        
        /** The color code for the given color */
        $color_code         = $color_codes[$color];
        /** The formatted text */
        $formatted_text     = "\e[" . $color_code . "m" . $text . "\e[0m";
        
        /** Each tag is replaced by its formatting code */
        foreach ($set_codes as $attribute => $code) {
            /** The reset code */
            $reset_code     = $reset_codes[$attribute];
            /** The attribute opening tag is replaced by its code */
            $formatted_text = str_replace("<" . $attribute . ">", "\e[" . $code . "m", $formatted_text);
            /** The attribute closing tag is replaced by its reset code */
            $formatted_text = str_replace("</" . $attribute . ">", "\e[0m", $formatted_text);            
        }
        
        /** Each tag is replaced by its color code */
        foreach ($color_codes as $attribute => $code) {
            /** The attribute opening tag is replaced by its code */
            $formatted_text = str_replace("<" . $attribute . ">", "\e[" . $code . "m", $formatted_text);
            /** The attribute closing tag is replaced by its reset code */
            $formatted_text = str_replace("</" . $attribute . ">", "\e[0m", $formatted_text);            
        }
        
        /** The code for resetting all formatted text is appended */
        $formatted_text     .= "\e[" . $reset_codes["all"] . "m";
        
        echo $formatted_text;
    } 
    
	/**
     * Used to display the basic usage of the framework
     *
     * It outputs usage information to the console
     */
    public static function HandleUsage() : void
    {
        /** The application usage */
        $usage             = <<< EOT
                
  Usage: php index.php --application="[app-name]" --command="[command]" [--parameter-name="parameter-value"]

  Applications: 
EOT;

        /** The list of all applications */
        $app_names         = self::GetApplicationList();
        /** The application list is appended to the usage information */
        $usage            .= implode(", ", $app_names);
        /** The default framework commands */
        $default_commands  = array(
                                 "Unit Test (run unit tests. specify type of test. i.e unit or ui in app config)",
                                 "Generate Test Data (generates test data files from method comments)",
                                 "Help (displays all available commands for the given application"
                             );
        /** The commands defined by the application */
        $custom_commands  = (isset(Config::$config["general"])) ? Config::$config["general"]["commands"] : array();
        /** The valid commands are formed by merging default commands with application commands */
        $valid_commands   = array_merge($default_commands, $custom_commands);
        /** The list of all commands */
        $command_str      = "";
        /** Each valid command is checked */        
        for ($count = 0; $count < count($valid_commands); $count++) {
            /** The command list is updated */
            $command_str .= "  " . ($count + 1) . ". " . $valid_commands[$count] . "\n";
        }

        /** The usage information is updated */
        $usage            .= "\n\n  Commands: \n\n" . $command_str . "\n\n";
        
        die($usage);
    }
    
    /**
     * Used to return the list of all applications supported by the current framework installation
     *
     * It returns an array containing the folder names of all the applications
     *
     * @return array $app_names the list of all application names
     */
    private static function GetApplicationList() : array
    {        
        /** The path to the framework parent folder */
        $folder_path         = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "..");
        /** The contents of the framework folder */
        $dir_list            = scandir($folder_path);
        /** The list of application folder names */
        $app_names           = array();
        /** Each application name is checked */
        for ($count = 0; $count < count($dir_list); $count++) {
        	/** If the application name is 'framework', '.' or '..' or it is a file, then the loop continues */
        	if ($dir_list[$count] == 'framework' || 
        	    $dir_list[$count] == '.' || 
        	    $dir_list[$count] == '..' || 
        	    is_file($folder_path . DIRECTORY_SEPARATOR . $dir_list[$count])
        	) 
        	    continue;
        	/** The application name */
        	$app_names[]     = $dir_list[$count];
        }
        
        return $app_names;
    }
    
    /**
     * Used to validate the default command line arguments
     *
     * It checks if the command line arguments are correct
     * If the arguments are not correct, then the default script usage is shown and application ends
     *
     * @param array $parameters the application parameters
     */
    private static function CheckCommandLineParameters(array $parameters) : void
    {
    	/** The list of valid test types */
    	$default_commands      = array("Unit Test", "Generate Test Data");
	    /** The list of all applications */
        $app_names            = self::GetApplicationList();
        /** If the application name was not given, then the default usage information is shown and application ends */
    	if (!isset($parameters['application'])) {
          	/** Warning message is shown */
           	self::DisplayOutput("\n  <bold>Application name was not given</bold>\n", "red");
           	self::HandleUsage();
       	}
       	/** If the wrong application name was given, then the default usage information is shown and application ends */
    	if (!in_array(strtolower($parameters['application']), $app_names)) {
          	/** Warning message is shown */
           	self::DisplayOutput("\n  <bold>Invalid application name !</bold>\n", "red");
           	self::HandleUsage();
       	}
       	/** If the command was not given, then the default usage information is shown and application ends */
   		else if (!isset($parameters['command'])) {
         	/** Warning message is shown */
           	self::DisplayOutput("\n  <bold>Command was not given !</bold>\n", "red");
           	self::HandleUsage();
        }
    }
    
    /**
     * It parses the command line arguments and saves the data to application config
     * The application is terminated if a command line argument is not of the form --key=value
     * The command line parameters are also checked
     *
     * @param array $parameters the command line arguments
     *
     * @return array $updated_parameters the parsed command line arguments in key, value format
     */
    public static function ParseCommandLineArguments(array $parameters) : array
    {
        /** The updated application parameters in key, value format */
        $updated_parameters               = array();
        /** The application parameters are determined */
        for ($count = 1; $count < count($parameters) && isset($parameters[1]); $count++) {
            /** Single command line argument */
            $command                      = $parameters[$count];
            /** If the command does not contain equal sign then an exception is thrown. Only commands of the form --key=value are accepted */
            if (strpos($command, "--") !== 0 || strpos($command, "=") === false) 
                die("Invalid command line argument was given. Command line arguments: " . var_export($parameters, true));
            else {
                /** The '--' is removed from the command line parameter */
                $command                  = str_replace("--", "", $command);
                /** The command line parameters is split on '=' character' */
                list($key, $value)        = explode("=", $command);
                /** The name and value of the parameters are saved */
                $updated_parameters[$key] = $value;
            }
        }
        /** The parameters are set */
        $parameters                       = $updated_parameters;
        /** The command line parameters are checked */
        self::CheckCommandLineParameters($parameters);
        
        return $updated_parameters;
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

        /** The callable method to call */
        $method               = array($callback_obj, $callback[1]);
        /** The callback function is called */
        $response             = call_user_func_array($method, array());        
        /** If the method response is not given, then response is set to empty string */
        if ($response == null) 
            $response = "";
            
        return $response;
    }
    
    /**
     * Used to initialize the application
     *
     * It generates application parameters from the command line arguments given by the user
     *
     * @param array $parameters the application parameters     
     */
    public function InitializeApplication($parameters) : void 
    {
   		/** The url routing information is generated */
        Config::GetComponent("cliparsing")->GetCallback($parameters);
    }
}
