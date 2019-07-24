<?php

declare(strict_types=1);

namespace Framework\TestManager;

use \Framework\Utilities\UtilitiesFramework as UtilitiesFramework;
use \Framework\Application\CommandLine as CommandLine;
use \Framework\Config\Config as Config;

/**
 * Provides functions for running white box unit tests
 *
 * @category   Testing
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
final class WhiteBoxTesting
{
    /** @var int $assert_count The number of assert statements that have been run */
    public $assert_count = 0;
    	
	/**
     * Used to test the given test class method
     *
     * @param string $object_name the name of the object to test
     * @param string $class_method the class method to test
     *
     * @return array $method_results the results of running the class method
     *    assert_count => int the total number of asserts by the method
     */
    private function RunTestClassMethod(string $object_name, string $class_method) : array
    {
    	/** The total number of asserts is set to 0 */
    	$this->assert_count     = 0;

        /** The class object is fetched from application config */
        $test_object            = Config::GetComponent($object_name);               
		/** The test callback function is defined */
        $test_callback          = array($test_object, $class_method);        
        /** If the callback function is not callable */
        if (!is_callable($test_callback)) {
            /** Script ends */
            die("Test function: " . $class_method . " does not exist for the object: " . $object_name);
        }
        
        /** The test function is pre processed */
       	Config::GetComponent("testfunctionprocessor")->PreProcessTestFunction($class_method);
       	/** The class method is called */
   	 	call_user_func_array($test_callback, array(array()));
   	 	
   	 	/** The item being tested */
   	 	$item_details           = array("method" => $class_method, "object" => $object_name);
   	 	/** The item details are json encoded */
   	 	$item_details           = json_encode($item_details);
   	 	/** The code coverage file name */
   	 	$file_name              = $class_method . ".txt";
        /** The post processing test function is called */
		Config::GetComponent("testfunctionprocessor")->PostProcessTestFunction($item_details, array(), $file_name);
		
		$method_results         = array("test_cases" => $test_cases, "assert_count" => $this->assert_count);
		
		return $method_results;
	}
	
    /**
     * It runs white box unit tests
     *
     * It tests all the classes given in application config
     * Only functions that start with "Test" will be run
     */
    public function RunWhiteBoxTests() : void 
    {
        /** The number of unit tests run */
        $test_count                  = 0;
        /** The total number of asserts */
        $total_assert_count          = 0;
        /** The test classes */
        $test_classes                = Config::$config['test']['test_classes'];
        /** The text to display to the console */
        $console_text                = "";
        /** For each class all functions that start with Test are called */
        for ($count1 = 0; $count1 < count($test_classes); $count1++) {
        
            /** If the current method is the first one being tested, then code coverage is enabled */
            Config::$config['test']['enable_code_coverage'] = ($test_count == 0) ? true : false;
            /** If the current method is the first one being tested, then function tracing is enabled */
            Config::$config['test']['enable_trace']         = ($test_count == 0) ? true : false;
                
        	/** The test object name */
            $object_name             = $test_classes[$count1];
            /** The name of the class to test */
            $class_name              = Config::$config['requiredobjects'][$object_name]['class_name'];
            /** The class methods are fetched */
            $class_methods           = get_class_methods($class_name);
            
            /** The output text */
            $console_text            .= ($count1 + 1) . ". Testing class: " . $class_name . "\n";
   		    
            /** Each object function that starts with "Test" is called */
            for ($count2 = 0; $count2 < count($class_methods); $count2++) {
            	/** If the class method does not start with "Test", then the loop continues */
        		if (strpos($class_methods[$count2], "Test") !== 0) continue;
        				
        		/** The output text */
		        $console_text        .= "--Testing method: " . $class_methods[$count2] . "\n\n";    		    
            	/** The class method is tested */
                $method_results      = $this->RunTestClassMethod($object_name, $class_methods[$count2]);
				/** The total number of asserts is increased */
				$total_assert_count  += $method_results['assert_count'];
	        	/** The test count is increased by 1 */
    	        $test_count++;				
            }
        }
        
        /** The progress of unit test is displayed */
		CommandLine::DisplayOutput($console_text);
        /** The test results are saved to application configuration */
        Config::$config['custom']['test_results'] = $console_text;
        /** The unit test results summary is displayed */
        Config::GetComponent("testresultsmanager")->DisplayUnitTestSummary($test_count, $total_assert_count, null);
    }        
}
