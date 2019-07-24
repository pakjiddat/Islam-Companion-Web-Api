<?php

declare(strict_types=1);

namespace Framework\Config;

use \Framework\Config\Initializer as ConfigInitializer;
use \Framework\Utilities\UtilitiesFramework as UtilitiesFramework;
use \Framework\Config\Base\GeneralConfig as DefaultGeneralConfig;
use \Framework\Config\Base\PathConfig as DefaultPathConfig;
use \Framework\Config\Base\TestConfig as DefaultTestConfig;
use \Framework\Config\Base\RequiredObjectsConfig as DefaultRequiredObjectsConfig;

/**
 * This class is used to manage application config
 *
 * @category   Config
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class Manager
{    
    /**
     * Used to include all files that are defined in application config
     */
    public function IncludeRequiredClasses() : void 
    {
        /** The list of files to be included for application requests is fetched from config */
        $include_files     = Config::$config["path"]["include_files"];

        /** All files that need to be included are included */
        foreach ($include_files as $include_type => $include_files) {
            /** The list of all files for the include type are included */
            for ($count = 0; $count < count($include_files); $count++) {
                /** The file name to include */
                $file_name     = $include_files[$count];
                /** If the include type is equal to vendors */
                if ($include_type == "vendors") 
                    /** The vendor folder path is prepended to the include file path */
                    $file_name = Config::$config["path"]["vendor_folder_path"] . DIRECTORY_SEPARATOR . $file_name;
                /** If the include type is equal to pear */
                if ($include_type == "pear")
                    /** The pear folder path is prepended to the include file path */
                    $file_name = Config::$config["path"]["pear_folder_path"] . DIRECTORY_SEPARATOR . $file_name;
                
                /** If the file exists, then it is included */
                if (is_file($file_name)) require_once ($file_name);
                /** If the file does not exist, then an exception is thrown */
                else 
                    throw new \Error("Invalid include file name: " . $file_name . " given for page action: " . 
                    Config::$config["general"]["action"]);
            }
        }
    }
    
    /**
     * This function is used to read custom config data from application config folder
     * It also sets the value of dev_mode given in general config to the application parameters     
     *
     * @param string $folder_path the application folder path
     * @param array $params the application parameters
     *
     * @return array $custom_config the custom application config   
     */
    public function ReadCustomConfig(string $folder_path, array $params) : array
    {
        /** The custom application config */
        $custom_config                   = array();
		/** The absolute path to the application config folder */
		$folder_path                     = $folder_path . DIRECTORY_SEPARATOR . "config";
		/** The list of custom config files */
		$file_list                       = UtilitiesFramework::Factory("foldermanager")->GetFolderContents(
		                                       $folder_path,
		                                       1
		                                   );
		/** Indicates if the development mode has been set */
		$is_dev_mode_set                 = false;
		/** Each config file is checked */
		for ($count = 0; $count < count($file_list); $count++) {
			/** The absolute path to the file */
			$file_name                   = $file_list[$count];
			/** If the file name does not have a .php extension, then the loop continues */
			if (strpos($file_name, ".php") === false) continue;
			/** The path information */
			$path_information            = pathinfo($file_name);
			/** The folder name is split on DIRECTORY_SEPARATOR */
			$temp_arr                    = explode(DIRECTORY_SEPARATOR, $path_information['dirname']);
			/** The folder namespace */
			$namespace                   = ucfirst($temp_arr[count($temp_arr)-2]) . '\\' . 
			                               ucfirst($temp_arr[count($temp_arr)-1]) . '\\';
			/** If the development mode has not been set */
			if (!$is_dev_mode_set) {
			    /** The class name */
			    $class_name              = $namespace . "General";
			    /** The class object */
			    $class_obj               = new $class_name();
	    		/** The general config is read */
	    		$general_config          = $class_obj->GetConfig($params);
	    		/** The development mode value is set to the parameters */
	    		$params['dev_mode']  = $general_config['dev_mode'] ?? false;
	    		/** Indicates that the development mode has been set */
	    		$is_dev_mode_set         = true;
			}
			/** The class name */
			$class_name                  = $namespace . $path_information['filename'];
			/** The class object */
			$class_obj                   = new $class_name();
			/** The name of the config */
			$config_name                 = strtolower($path_information['filename']);
			/** The config data is fetched and saved to the custom config */
			$custom_config[$config_name] = $class_obj->GetConfig($params);
		}
		
		return $custom_config;
    }
    
    /**
     * Used to get updated application config data
     * It reads the default and custom config settings
     * The custom config is merged with the default config
     *
     * @param array $custom_config custom application config
     *
     * @return array $config the application config information
     */
    public function GetUpdatedConfig(array $custom_config) : array
    {
        /** The general default config is fetched */
        $custom_config['general']              = DefaultGeneralConfig::GetConfig($custom_config);        
        /** The path default config is fetched */
        $custom_config['path']                 = DefaultPathConfig::GetConfig($custom_config);
        /** The test default config is fetched */
        $custom_config['test']                 = DefaultTestConfig::GetConfig($custom_config);
        /** The required frameworks default config is fetched */
        $custom_config['requiredobjects']      = DefaultRequiredObjectsConfig::GetConfig($custom_config);
        /** The php error config is set */
        ConfigInitializer::InitializePhpSettings($custom_config);
        /** The application config is set */
        $config                                = $custom_config;

        return $config;
    }
}
