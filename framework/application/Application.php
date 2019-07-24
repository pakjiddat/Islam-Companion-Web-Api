<?php

declare(strict_types=1);

namespace Framework\Application;

use \Framework\Application\CommandLine as CommandLine;
use \Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

/**
 * This class provides the base class for all Applications
 *
 * It provides common functions for all applications
 *
 * @category   Application
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
abstract class Application
{ 
    /**
     * Used to echo the given text
     *
     * @param string $text the text to echo
     */
    public static function DisplayOutput($text) : void 
    {
        echo $text;
    }    
    /**
     * It encodes the given array into json format
     * The json encoded data is then echoed
     *
     * @param array $text the text to echo
     */
    final public function DisplayJsonOutput(array $text) : void
    {
        /** The given data is json encoded */
        $text = json_encode($text);
        
        echo $text;
    }    
    
    /**
     * Used to run the application with the given parameters
     *
     * @param array $params the application parameters
     *
     * @return string $response the application response
     */
    final public static function RunApplication(array $params) : string
    {
        /** Indicates that the request was handled. It is set to false by default */
        $handled           = false;
        /** The path to the framework parent folder */
        $folder_path       = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "..");
        /** The full path to all the application Config.php files */
        $files_path        = UtilitiesFramework::Factory("foldermanager")->GetFolderContents(
                                 $folder_path,
                                 2,
                                 "Config.php",
                                 "framework"
                             );
        /** If the application is being run from command line */
        if (php_sapi_name() == "cli") {
            /** The command line argument is parsed */
            $params        = CommandLine::ParseCommandLineArguments($params);
        }
        /** Each config file is checked to see if it should handle the current request */
        for ($count = 0; $count < count($files_path); $count++) {
        	/** The config file name */
            $file_name     = $files_path[$count];
            /** The folder for the config file */
            $folder_name   = str_replace(DIRECTORY_SEPARATOR . "Config.php", "", $file_name);
            /** The relative file path is determined */
            $rel_file_path = str_replace($folder_path, "", $file_name);
            /** The class name . DIRECTORY_SEPARATOR is removed from the relative file path */
            $class_name    = str_replace(DIRECTORY_SEPARATOR, '\\', $rel_file_path);
            /** The .php extension is removed from the relative file path */
            $class_name    = str_replace(".php", "", $class_name);
            /** If the class name is not valid then the loop continues; */
            if (!class_exists($class_name)) continue;
            /** If the class should handle the current request or the application name matches */
            if ($class_name::IsValid($params)) {
            	/** An instance of the required application is created */
                $config    = new $class_name();
                /** The application output */
                $response  = $config->RunApplication($folder_name, $params);
                /** Indicates that the request was handled */
                $handled = true;
                /** No need to check other applications */
                break;
            }
		}
     
        /** If the application request was not handled then an exception is thrown */
        if (!$handled) {
            /** If the current script is not being run from command line */
            if (php_sapi_name() != "cli") die("Invalid application name given");
            /** If the current script is being run from command line */
            else {
                /** The script usage is shown and script ends */
                CommandLine::HandleUsage();
            }
        }
            
        return $response;
    }
}
