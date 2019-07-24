<?php

declare(strict_types=1);

namespace Framework\Config\Base;

/**
 * It provides default required objects config
 *
 * @category   Config
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class RequiredObjectsConfig
{
    /**
     * Used to get default required objects config settings
     * Custom config may override the default config
     *
     * @param array $custom_config the custom application config
     *
     * @return array $config the default config information
     */
    public static function GetConfig(array $custom_config) : array
    {
        /** The parameters array is initialized */
        $parameters                                         = array(
                                                                  "errormanager" => array(),
                                                                  "dbinitializer" => array()
                                                              );
        /** The shutdown function callable */
        $parameters['errormanager']['shutdown_function']    = "";
        /** Used to indicate if application is in development mode */
        $parameters['errormanager']['dev_mode']     = $custom_config['general']['dev_mode'];
        /** Custom error handler callback */
        $parameters['errormanager']['custom_error_handler'] = array("errorhandling", "CustomErrorHandler");
        /** Used to indicate how the application is being run */
        $parameters['errormanager']['context']              = (php_sapi_name() == 'cli') ? 'cli' : 'web';
        /** The application folder path */
        $parameters['errormanager']['app_folder']           = $custom_config['path']['base_path'];
        
        /** The email address at which the email should be sent */
        $email_to                                           = "nadir@dev.pakjiddat.pk";
        /** The email address from which the email should be sent */
        $email_from                                         = "admin@dev.pakjiddat.pk";
        /** The email handler information */
        $parameters['errormanager']['email_information']   = array(
                                                                 "to_email" => $email_from, 
                                                                 "from_email" => $email_from
                                                             );
        /** The dsn */
        $dsn                                               = "mysql:host=localhost;dbname=pakjiddat_admin;charset=utf8";        
        /** The dbinitializer class parameters are set */
        $parameters['dbinit']                              = array(
															     "dsn" => $dsn,
																 "user_name" => "nadir",
																 "password" => "kcW5eFSCbPXb#7LHvUGG8T8",
																 "debug" => 2,
																 "use_cache" => true,
																 "app_name" => $custom_config["general"]["app_name"]
															 );

        $def_config['dbinit']['class_name']                = '\Framework\Utilities\DatabaseManager\DbInitializer';
        $def_config['dbinit']['parameters']                = $parameters['dbinit'];
        $def_config['frameworkdbinit']['class_name']       = '\Framework\Utilities\DatabaseManager\DbInitializer';
        $def_config['frameworkdbinit']['parameters']       = $parameters['dbinit']; 

        $def_config['unittestrunner']['class_name']        = '\Framework\TestManager\UnitTestRunner';
        $def_config['testfunctionprocessor']['class_name'] = '\Framework\TestManager\TestFunctionProcessor';
        $def_config['testresultsmanager']['class_name']    = '\Framework\TestManager\TestResultsManager';
        $def_config['testfunctionvalidator']['class_name'] = '\Framework\TestManager\TestFunctionValidator';
        $def_config['codecoveragegenerator']['class_name'] = '\Framework\TestManager\CodeCoverageGenerator';
        $def_config['testdatamanager']['class_name']       = '\Framework\TestManager\TestDataManager';
        $def_config['blackboxtesting']['class_name']       = '\Framework\TestManager\BlackBoxTesting';
        $def_config['whiteboxtesting']['class_name']       = '\Framework\TestManager\WhiteBoxTesting';
        $def_config['uitesting']['class_name']             = '\Framework\TestManager\UiTesting';        
        
        $def_config['application']['class_name']           = '';
        $def_config['application']['parameters']           = array();
        $def_config['errorhandling']['class_name']         = '\Framework\Application\Libraries\ErrorHandling';
        $def_config['loghandling']['class_name']           = '\Framework\Application\Libraries\LogHandling';
        $def_config['translation']['class_name']           = '\Framework\Application\Libraries\Translation';
        $def_config['urlrouting']['class_name']            = '\Framework\Application\Libraries\UrlRouting';
        $def_config['cliparsing']['class_name']            = '\Framework\Application\Libraries\CliParsing';        
        $def_config['sessionhandling']['class_name']       = '\Framework\Application\Libraries\SessionHandling';
        $def_config['functionvalidation']['class_name']    = '\Framework\Application\Libraries\FunctionValidation';        
        $def_config['configinitializer']['class_name']     = '\Framework\Application\Config\Initializer';
        $def_config['configmanager']['class_name']         = '\Framework\Application\Config\Manager';        
        $def_config['templateengine']['class_name']        = '\Framework\Ui\TemplateEngine\Generator';

        $def_config['widgetmanager']['class_name']         = '\Framework\Ui\Widgets\Manager';
        $def_config['w3css_headertags']['class_name']      = '\Framework\Ui\Widgets\W3css\HeaderTags\Widget';
        $def_config['w3css_404']['class_name']             = '\Framework\Ui\Widgets\W3css\Four0Four\Widget';
        $def_config['w3css_login']['class_name']           = '\Framework\Ui\Widgets\W3css\Login\Widget';
        $def_config['w3css_alert']['class_name']           = '\Framework\Ui\Widgets\W3css\Alert\Widget';
        $def_config['w3css_scrolltop']['class_name']       = '\Framework\Ui\Widgets\W3css\ScrollTop\Widget';        
        $def_config['w3css_table_list']['class_name']      = '\Framework\Ui\Widgets\W3css\TableList\Widget';                
                
        /** If error handling has not been disabled by custom config */
        if (!isset($custom_config['general']['parameters']['disable_error_handling'])) {
            /** The error manager class name */
            $class_name                                    = "\Framework\Utilities\ErrorManager\ErrorManager";
            /** The error manager configuration */
    		$def_config["errormanager"] 	  		       = array(
															     "class_name" => $class_name,
																 "parameters" => $parameters['errormanager']
														     );
        }
        
        /** If custom required objects config has been provided */
        if (isset($custom_config['requiredobjects'])) {
	        /** User config is merged */
	        $config                                        = array_replace_recursive(
	                                                             $def_config, 
	                                                             $custom_config['requiredobjects']
	                                                         );
		}
		/** If custom required objects config has not been provided, then the default config is used */
		else $config                                       = $def_config;
		
        return $config;
    }
}
