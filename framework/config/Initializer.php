<?php

declare(strict_types=1);

namespace Framework\Config;

use \Framework\Config\Manager as ConfigManager;

/**
 * This class provides functions for initalizing the application
 * It contains functions for initializing Php settings, error handling, reading custom config
 * It contains functions for initializing applications
 *
 * @category   Application
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class Initializer
{    
    /**
     * Used to create the given object
     *
     * Creates the given object
     * The object must be mentioned by the custom in the application config file
     * The object is created using GetInstance method if it is supported or new operator if class is not Singleton
     *
     * @param string $object_name the object name
     * @param array $parameters the optional object parameters
     *
     * @return object $framework_class_obj the initialized object is returned
     */
    public static function InitializeObject(string $object_name, array $parameters = null) : object
    {
        /** The list of required objects */
        $object_information                      = Config::$config['requiredobjects'][$object_name];
        /** If the object parameters are given */
        if (is_array($parameters)) {
            /** The object parameters are set */
            $object_information['parameters']    = $parameters;
        }
        /** If the object parameters are not given */
        else if (!isset($object_information['parameters'])) {
            /** The object parameters are set to empty string */
            $object_information['parameters']    = null;
        }            

        /** The name of the framework class */
        $framework_class_name                    = $object_information['class_name'];
        /** The parent class name is fetched */
        $parent_class_name                       = get_parent_class($framework_class_name);
        /** If the class does not exist, then an exception is throw. If it exists, then it is autoloaded */
        if (!class_exists($framework_class_name, true))
            throw new \Error("Class: " . $framework_class_name . " does not exist for object name: " . $object_name);                
        
        /** The callback for getting Singleton instance of the class */
        $callable_singleton_method               = array($framework_class_name, "GetInstance");
        /** If the method for fetching Singleton instance exists */
        if (is_callable($callable_singleton_method)) {
            /** The method is called and the Sington instance is fetched */
            $framework_class_obj                 = call_user_func_array(
                                                       $callable_singleton_method,
                                                       array($object_information['parameters'])
                                                   );
        }
        /** If the method for fetching Singleton instance does not exist */
        else {
            /** A new object is created using new operator */
            $framework_class_obj                 = new $framework_class_name($object_information['parameters']);
        }
        
        /** The object is saved to object list */
        Config::$components[$object_name] = $framework_class_obj;

        return $framework_class_obj;
    }
    /**
     * If custom error handling callbacks are defined, then this function configures the callbacks        
     */
    private function InitializeErrorHandling() : void
    {
        /** If the custom config includes error manager */
        if (isset(Config::$config['requiredobjects']['errormanager'])) {
            /** The error manager configuration */
            $errormanager_config           = Config::$config['requiredobjects']['errormanager']['parameters'];
            /** The errormanager callback is checked */
            $errormanager_callback         = $errormanager_config['custom_error_handler'];
            /** If the errormanager callback is defined but is not callable */
            if (is_array($errormanager_callback) && !is_callable($errormanager_callback)) {
                /** The error handler callback object is fetched */
                $errormanager_callback[0]  = Config::GetComponent($errormanager_callback[0]);
                /** The error handler callback */
                $errormanager_config['custom_error_handler'] = $errormanager_callback;
            }
            /** The shutdown function callback is checked */
            $shutdown_callback             = $errormanager_config['shutdown_function'];
            /** If the shutdown function callback is defined but is not callable */
            if (is_array($shutdown_callback) && !is_callable($shutdown_callback)) {
                /** The shutdown callback object is fetched */
                $shutdown_callback[0]      = Config::GetComponent($shutdown_callback[0]);
                /** The callback function to call just before the script ends */
                $errormanager_config['shutdown_function'] = $shutdown_callback;
            }
            /** Otherwise the default application shutdown callback is used */
            else {
                /** The callback function to call just before the script ends */
                $errormanager_config['shutdown_function'] = array(
                    $errormanager_callback[0],
                    "CustomShutdownFunction"
                );
            }
            /** The error manager object configuration is updated */
            Config::$config['requiredobjects']['errormanager']['parameters'] = $errormanager_config;
            /** The errormanager class object is created */
            self::InitializeObject("errormanager");
        }
    }
    /**
     * Used to set Php config settings
     * 
     * It sets the Php error reporting value
     * It sets the Php timezone value
     * If the application is in development mode, then the displaying of errors is enabled in Php config
     *
     * @param array $custom_config the custom config
     */
    public static function InitializePhpSettings(array $custom_config) : void 
    {
        /** The time zone used by the application */
        $default_timezone        = $custom_config['general']['timezone'] ?? 'Asia/Karachi';
        /** The error reporting used by the application */
        $default_error_reporting = $custom_config['general']['error_reporting'] ?? E_ALL;
        
        /** The error reporting value is set */
        error_reporting($default_error_reporting);
        /** The default time zone is set */
        date_default_timezone_set($default_timezone);
        
        if ($custom_config['general']['dev_mode']) {
            ini_set('display_errors', '1');
            ini_set('display_startup_errors', '1');
        }
        else {
            ini_set('display_errors', '0');
            ini_set('display_startup_errors', '0');
        }
    }         
    
    /**
     * Used to initialize the application
     *
     * It reads custom application config
     * It initializes error handling
     * It initializes the required objects
     * It registers framework class autoloader
     *
     * @param string $folder_path the application folder path
     * @param array $parameters the application parameters
     */
    public function Initialize(string $folder_path, array $parameters) : void 
    {
        /** The ConfigManager object is created */
        $config_manager           = new ConfigManager();
        /** The custom application config is read */
        $custom_config            = $config_manager->ReadCustomConfig($folder_path, $parameters);
        /** The default config is merged with custom config and the result is returned */
        Config::$config           = $config_manager->GetUpdatedConfig($custom_config);
        /** The application error handling is initialized */
        $this->InitializeErrorHandling();
        /** All required classes are included */
        $config_manager->IncludeRequiredClasses();
        /** The short object name to use for running the method */
        $obj_name                 = (php_sapi_name() == "cli") ? "cliapplication" : "application";
        /** The application is initialized */
        Config::GetComponent($obj_name)->InitializeApplication($parameters);
    }            
}

