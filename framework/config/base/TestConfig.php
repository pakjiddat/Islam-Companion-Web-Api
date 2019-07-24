<?php

declare(strict_types=1);

namespace Framework\Config\Base;

/**
 * It provides default test config
 *
 * @category   Config
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class TestConfig
{
    /**
     * Used to get default test config settings
     * Custom config may override the default config
     *
     * @param array $custom_config the custom application config
     *
     * @return array $config the default config information
     */
    public static function GetConfig(array $custom_config) : array
    {
        /** The default general config */
        $def_config                         = array();
        
        /** The unit test classes */
  	    $def_config['test_classes']         = array();        
        /** Indicates if application is in test mode */
        $def_config['test_mode']            = false;
        /** Indicates the test type. Supported test types are 'blackbox', 'whitebox' and 'ui' */
        $def_config['test_type']            = "whitebox";                		
        /** Indicates the files that need to be included during test */
        $def_config['include_files']        = array();
        /** The url of html validator to use during test */
        $def_config['validator_url']        = "https://validator.nu";
        /** Used to indicate if the application should save test results */
        $def_config['save_test_results']    = true;
        /** Used to indicate if the current request data should be saved to database for user interface testing */
        $def_config['save_test_results']    = true;
        /** Indicates if the ui test data should be saved */
        $def_config['save_ui_test_data']    = false;
        /** The list of broken links to ignore */
        $def_config['ignore_links']         = false;
        /** Indicates if the code coverage should be generated */
        $def_config['enable_code_coverage'] = true;        
        /** Indicates if the function trace should be generated */
        $def_config['enable_trace']         = false;            

        /** The path to the application test folder */
        $def_config['test_folder']          = $custom_config['path']['app_path'] . DIRECTORY_SEPARATOR . 'test';
        /** The path to the test results folder */
        $def_config['results_folder']       = $def_config['test_folder'] . DIRECTORY_SEPARATOR . "results";
        /** The path to the application documentation folder */
        $def_config['documentation_folder'] = $custom_config['path']['app_path'] . DIRECTORY_SEPARATOR . "docs";
        /** The full path to the test results file is set */
        $def_config['test_results_file']    = $def_config['results_folder'] . DIRECTORY_SEPARATOR . "test_results.txt";
        /** The full path to the trace folder is set */
        $def_config['trace_folder']         = $def_config['results_folder'] . DIRECTORY_SEPARATOR . "tracelogs";
        /** The full path to the code coverage folder is set */
        $def_config['code_coverage_folder'] = $def_config['results_folder'] . DIRECTORY_SEPARATOR . "codecoverage";
        /** The path to the test data folder */
        $def_config['test_data_folder']     = $def_config['test_folder'] . DIRECTORY_SEPARATOR . "testdata";
        /** Indicates the method to be tested */
        $def_config['only_test']            = array("object" => "", "method" => "");
        
		/** If custom test config has been provided */
        if (isset($custom_config['test'])) {
	        /** User config is merged */
	        $config                         = array_replace_recursive($def_config, $custom_config['test']);
		}		
		/** If custom test config has not been provided, then the default config is used */
		else $config                        = $def_config;
		
        return $config;
    }
}
