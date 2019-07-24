<?php

declare(strict_types=1);

namespace Framework\Config;

/**
 * Base config class
 * It should be extended by application config class
 *
 * @category   Config
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
abstract class Config
{
    /** @var $config array Specifies the config information required by the application */
    public static $config               = array();
    /** @var $component_list array List of objects that can be used by the application */
    public static $components           = array();
    
    /**
     * It returs the required object from application config
     * If the object does not exist, then it is created and initialized
     *
     * @param string $object_name name of the required object
     * @param array $parameters the optional object parameters
     *
     * @return object $component_object the required component object
     */
    final public static function GetComponent(string $object_name, array $parameters = null) : object
    {
		/** If the object does not exist in the application config */        
        if (!isset(self::$components[$object_name])) {
            /** The object is initialized */
        	self::$components[$object_name] = Initializer::InitializeObject($object_name, $parameters);
        }
        
        /** The required object is set */
        $component_object                   = self::$components[$object_name]; 
        
        return $component_object;
    }
    
    /**
     * Used to determine if the application request should be handled by the current module
     * It should be overriden by a child class
     * It returns true by default
     *
     * @param array $parameters the application parameters
     *
     * @return boolean $is_valid indicates if the application request is valid
     */
    public static function IsValid(array $parameters) : bool
    {
        /** The request is considered as valid */
        $is_valid                       = true;
       
        return $is_valid;
    }
    
    /**
     * Used to run the application
     *
     * This function runs the application
     * It first initializes the application
     * It then runs the application by calling the Main function
     *
     * @param string $folder_path the application folder path
     * @param array $parameters the application parameters
     *     
     * @return string $response the application response
     */
    final public function RunApplication(string $folder_path, array $parameters) : string
    {
        /** The Initializer class object is created */
        $initializer = new Initializer();
        /** The application is initialized */
        $initializer->Initialize($folder_path, $parameters);
        /** The short object name to use for running the method */
        $obj_name    = (php_sapi_name() == "cli") ? "cliapplication" : "application";
        /** The application is run and response is returned */
        $response    = Config::GetComponent($obj_name)->RunMethod($parameters);
               
        return $response;
    }
}
