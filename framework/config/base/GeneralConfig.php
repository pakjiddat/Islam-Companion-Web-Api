<?php

declare(strict_types=1);

namespace Framework\Config\Base;

/**
 * It provides default general config
 *
 * @category   Config
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class GeneralConfig
{
    /**
     * Used to get default general config settings
     * Custom config may override the default config
     *
     * @param array $custom_config the custom application config
     *
     * @return array $config the default config information
     */
    public static function GetConfig(array $custom_config) : array
    {
        /** The default general config */
        $def_config                            = array();
        /** The default config parameters are initialized */
        $def_config['parameters']              = array();
        /** The callback for handling the current request */
        $def_config['parameters']              = array();
        /** The validator callback for validating the application parameters */
        $def_config['validator']               = array();                
        /** The application context */
        $def_config["context"]                 = (php_sapi_name() == "cli") ? "cli" : "web";
        /** The custom commands defined by the application */
        $def_config["commands"]                = array();
	    /** The development mode is set to true by default */        
	    $def_config['dev_mode']                = true;				
        /** The name of the user interface framework to use */
	    $def_config['ui_framework']            = "w3css";
        /** The uploaded file information */
        $def_config['parameters']['uploads']   = $_FILES ?? array();
        /** The HTTP Request Method */
        $def_config['request_method']          = $_SERVER['REQUEST_METHOD'] ?? "";
        /** The HTTP_HOST  */
        $def_config['http_host']               = $_SERVER['HTTP_HOST'] ?? "";
        /** The REQUEST_URI  */
        $def_config['request_uri']             = $_SERVER['REQUEST_URI'] ?? "";
        /** The POST data  */
        $def_config['http_post']               = $_POST;
        /** The GET data  */
        $def_config['http_get']                = $_GET;
        /** The REQUEST data  */
        $def_config['http_request']            = $_REQUEST;
        /** The type for each user input. It should be set for each url request */
        $def_config['input_types']             = array();
        /** Indicates that the user access should be logged */
        $def_config['log_user_access']         = false;
        /** Indicates that the application should log error to database */
        $def_config['log_error_to_database']   = true;        
        /** Indicates that the error should be emailed */
        $def_config['email_error']             = true;
        /** Indicates if application should use sessions */
        $def_config['enable_sessions']         = false;
        /** Indicates if application should use session authentication */
        $def_config['enable_session_auth']     = false;
        /** The custom css files */
        $def_config['custom_css_files']        = array();
        /** The custom javascript files */
        $def_config['custom_js_files']         = array();
        /** The custom font files */
        $def_config['custom_font_files']       = array();        
        /** The site url */
        $def_config['site_url']                = "";
        /** The option for enabling cross domain ajax calls */
        $def_config['enable_cors']             = false;
        /** It indicates if the application response should be sanitized */
        $def_config['sanitize_response']       = false;        
        /** The folder name of the application */
        $def_config['app_name']                = "";     
        /** Indicates if the application text should be translated */
        $def_config['translate_text']          = false;
        /** The default application language */
        $def_config['language']                = "en";
        /** The site text */
        $def_config['site_text']               = array();
		/** Indicates if application should exit on error */
    	$def_config['exit_on_error']           = true;
    	/** The maximum number of errors after which application should exit */
    	$def_config['max_error_emails']        = 10;
        /** The total number of error emails sent by script */
        $def_config['error_email_count']       = 0;        
        /** The email address at which the email should be sent */
        $def_config['error_email_to']          = "nadir@dev.pakjiddat.pk";
        /** The email address from which the email should be sent */
        $def_config['error_email_from']        = "admin@dev.pakjiddat.pk";
        /** The names of the MySQL database tables used by the framework */
        $def_config['mysql_table_names']       = array(
												     "test_data" => "pakphp_test_data",            
													 "error_data" => "pakphp_error_data",
													 "access_data" => "pakphp_access_data",
													 "cache_data" => "pakphp_cache_data",
													 "test_results" => "pakphp_test_results",
													 "test_details" => "pakphp_test_details",
													 "users" => "pakphp_users"
												);
        /** The session authentication information */
        $def_config['session_auth']            = array(
                                                     "table_name" => "users",
                                                     "columns" => array(
                                                                      "first_name" => "first_name",
                                                                      "user_name" => "user_name",
                                                                      "password" => "password"
                                                                  ),
                                                     "login_validation_url" => "/login/validate",
                                                     "login_url" => "/login",
                                                     "post_login_url" => "/"
                                                 );
        /** The line break character for the application is set */
        $def_config['line_break']              = ($def_config["context"] != "cli") ? "<br/>" : "\n";
        /** The application display name */
        $def_config['app_display_name']        = "";
        /** The application name */
        $def_config['app_name']                = "";
        
        /** If custom general config has been provided */
        if (isset($custom_config['general'])) {
	        /** The custom config is merged with default config */
	        $config                            = array_replace_recursive($def_config, $custom_config['general']);
		}
		/** If custom config has not been provided then the default config is used */
		else $config                           = $def_config;
		
        return $config;
    }
}
