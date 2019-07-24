<?php

declare(strict_types=1);

namespace Framework\Config\Base;

use \Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

/**
 * It provides default path config
 *
 * @category   Config
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class PathConfig
{
    /**
     * Used to get default path config settings
     * Custom config may override the default config
     *
     * @param array $custom_config the custom application config
     *
     * @return array $config the default config information
     */
    public static function GetConfig(array $custom_config) : array
    {
        /** The short name for the directory separator is defined */
        $sep                                = DIRECTORY_SEPARATOR;
        /** The default general config */
        $def_config                         = array();
        /** The StringUtils class object is fetched */
        $stringutils                        = UtilitiesFramework::Factory("stringutils");
        /** The document root of the application is set */
        $def_config['document_root']        = $_SERVER['DOCUMENT_ROOT'];
        
        /** The relative path to the framework folder */
        $rel_path                           = dirname(__FILE__) . $sep . ".." . $sep . ".." . $sep . "..";
        /** The base folder path is set. All the application files including the framework are in this folder */
        $def_config['base_path']            = realpath($rel_path);
        /** The template library name */
        $template_lib                       = $custom_config['general']['ui_framework'];
        /** If the application folder name has not been customized */
        if (!isset($custom_config['path']['app_folder'])) {
	        /** The application folder name is derived from the application name */
	        $app_name                       = $stringutils->CamelCase($custom_config['general']['app_name']);
    	    $def_config['app_folder']       = strtolower($app_name);
    	}
    	/** If the application folder name has been customized, then it is used */
    	else {
	    	$def_config['app_folder']       = $custom_config['path']['app_folder'];
    	}
      
        /** If the HTTPS server var is set */
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') 
            $def_config['web_domain']       = "https://" . $_SERVER['HTTP_HOST'];
        /** If the HTTP_HOST server var is set */
        else if (isset($_SERVER['HTTP_HOST'])) 
            $def_config['web_domain']       = "http://" . $_SERVER['HTTP_HOST'];
        /** If both server vars are not set */
        else
            $def_config['web_domain']       = "http://example.com";
                        
        /** The relative path to the framework */
        $rel_path                           = str_replace($def_config['document_root'], "", $def_config['base_path']);
        /** The "/" is appended to the relative path */
        $rel_path                           = "/" . $rel_path;
        /** The path to the framework folder */
        $fw_path                            = $def_config['base_path'] . $sep . "framework";        
        /** The framework url is set */
        $def_config['fw_url']               = $def_config['web_domain'] . $rel_path . "index.php";
        /** The framework folder url is set */
        $def_config['fw_folder_url']        = $def_config['web_domain'] . $rel_path. "framework";     
        /** The url to the framework's vendors folder */
        $def_config['fw_vendors_url']       = $def_config['web_domain'] . $rel_path . "framework/vendors";
        
        /** The relative path to the framework's template folder */
        $def_config['fw_template_folder']   = "ui" . $sep . "widgets" . $sep . "{ui_framework}";
        /** The path to the framework templates folder */
        $def_config['fw_template_path']     = $fw_path . $sep . $def_config['fw_template_folder'];
        /** The path to the framework ui folder */
        $def_config['fw_ui_url']            = $def_config['fw_folder_url'] . "/" . $def_config['fw_template_folder'];
                        
        /** The url to the application */
        $def_config['app_folder_url']       = $def_config['web_domain'] . $rel_path . $def_config['app_folder'];
        /** The url to the application's template folder */
	    $def_config['ui_folder']            = "ui";		 
        /** The relative path to the application's template folder */
        $def_config['app_template_folder']  = $def_config['ui_folder'] . $sep . "html";
        
  		/** The url to the user interface folder of the application */
        $def_config['app_ui_url']           = $def_config['app_folder_url'] . "/" . $def_config['ui_folder'];
        
        /** The application template url is set */
        $def_config['app_template_url']     = $def_config['web_domain'] . $rel_path . $def_config['app_folder'];
        $def_config['app_template_url']     .= "/" . $def_config['app_template_folder'];
        /** The default vendors folder name */
        $def_config['vendor_folder']        = "vendors";
        /** The default language folder name */
    	$def_config['language_folder']      = $def_config['ui_folder'] . $sep . "i18n";
    	
        /** The url to the application's vendors folder */
        $def_config['vendor_folder_url']    = $def_config['app_folder_url'] . "/" . $def_config['vendor_folder'];
        /** The path to the application folder */
        $def_config['app_path']             = $def_config['base_path'] . $sep . $def_config['app_folder'];
        /** The path to the application templates folder */
        $def_config['app_template_path']    = $def_config['app_path'] . $sep . $def_config['app_template_folder'];

        /** The default callback file name */       
        $def_config['callback_file']        = $def_config['app_path'] . $sep . "config" . $sep . "Callbacks.txt";
        /** The default widget config file name */       
        $def_config['widget_config_file']   = $def_config['app_path'] . $sep . "config" . $sep . "Widgets.txt";
        /** The path to the vendor folder */
        $def_config['vendor_folder_path']   = $def_config['app_path'] . $sep . $def_config['vendor_folder'];
        /** The path to the languages folder */
        $def_config['language_folder_path'] = $def_config['app_path'] . $sep . $def_config['language_folder'];
        /** The path to the pear folder */
        $def_config['pear_folder_path']     = $sep . "usr" . $sep . "share" . $sep . "pear";
        /** Indicates the files that need to be included for all application requests */
        $def_config['include_files']        = array();
        
        /** If custom path config has been provided */
        if (isset($custom_config['path'])) {
	        /** The custom config is merged with default config */
	        $config                         = array_replace_recursive($def_config, $custom_config['path']);
		}
		/** If custom config has not been provided then the default config is used */
		else $config                        = $def_config;
		
        return $config;
    }
}
