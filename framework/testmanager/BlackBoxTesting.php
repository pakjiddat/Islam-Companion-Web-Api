<?php

declare(strict_types=1);

namespace Framework\TestManager;

use \Framework\Application\CommandLine as CommandLine;
use \Framework\Config\Config as Config;
use \Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

/**
 * Provides functions for running black box unit tests
 *
 * @category   Testing
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
final class BlackBoxTesting
{
	/**
     * Used to test the given class method with test data loaded from database
     *
     * @param string $object_name the name of the object to test
     * @param string $class_method the class method to test
     *
     * @return array $method_results the results of running the class method
     *    test_cases => int the number of test cases
     */
    private function RunClassMethodWithTestData(string $object_name, string $class_method) : array
    {
        /** The testdatamanager object */
        $testdatamanager        = Config::GetComponent("testdatamanager");
        /** The testfunctionprocessor object */
        $testfunctionprocessor  = Config::GetComponent("testfunctionprocessor");
                
        /** The test data for the given object and method is fetched */
        $test_data              = $testdatamanager->LoadTestDataForMethod($object_name, $class_method);

        /** The name of the class to test */
        $class_name             = Config::$config['requiredobjects'][$object_name]['class_name'];
        /** The output text */
        $console_text           = "    --Testing method: " . $class_method . " of class: " . $class_name;
        $console_text          .= " (<cyan>" . count($test_data) . "</cyan> test cases)\n";        
        /** The progress of unit test is displayed */
        CommandLine::DisplayOutput($console_text);
                       		    
        /** The class object is fetched from application config */
        $test_object            = Config::GetComponent($object_name);               
		/** The test callback function is defined */
        $test_callback          = array($test_object, $class_method);        
        /** If the callback function is not callable */
        if (!is_callable($test_callback)) {
            /** Script ends */
            die("Test function: " . $class_method . " does not exist for the object: " . $object_name);
        }
                    
        /** The method is tested for each test data */
        for ($count = 0; $count < count($test_data); $count++) {
            /** The method parameters */
            $method_params      = $test_data[$count]['params'];
            /** The expected method return value */
            $exp_return_value   = $test_data[$count]['return_value'];
            /** The method return type */
            $return_type        = $test_data[$count]['return_type'];
            /** The method return value rule */
            $rule               = $test_data[$count]['rule'];
            /** The test function is pre processed */
           	$testfunctionprocessor->PreProcessTestFunction($class_method);   
           	/** The class method is called */
       	 	$return_value       = call_user_func_array($test_callback, $method_params);
       	 	/** If the current test data item is the last one */
       	 	$stop_cc            = ($count == (count($test_data) -1)) ? true : false;
       	 	
       	 	/** If the return value is in json format */
            if (is_string($return_value) && UtilitiesFramework::Factory("stringutils")->IsJson($return_value)) {
                /** The value is json decoded */
                $return_value   = json_decode($return_value, true);
            }
		    /** The method return value is validated */
		    Config::GetComponent("testfunctionvalidator")->ValidateMethodReturnValue(
		        $return_value,
		        $exp_return_value,
		        $return_type,
		        $rule,
		        $object_name,
		        $class_method
		    );
		    
		    /** The item being tested */
       	 	$item_details       = array("method" => $class_method, "object" => $object_name);
       	 	/** The item details are json encoded */
       	 	$item_details       = json_encode($item_details);
       	 	/** The file name for code coverage */
            $file_name          = $class_method . ".txt";
            
           	/** The post processing test function is called */
		    $testfunctionprocessor->PostProcessTestFunction($item_details, $method_params, $file_name, $stop_cc);
	    }		
		
		/** The test results are saved to application configuration */
        Config::$config['custom']['test_results'] .= $console_text;
        
		$method_results         = array("test_cases" => count($test_data));
		
		return $method_results;
	}
    
    /**
     * It runs unit tests using information given in database
     * It validates the return value of the function
     */
    public function RunBlackBoxTests() : void 
    {
        /** The number of unit tests run */
        $test_count                  = 0;
        /** The total number of test cases */
        $total_test_cases            = 0;
        /** The test data */
        $test_data                   = Config::GetComponent("testdatamanager")->LoadTestDataForBboxTesting();
        /** For each test object */
        foreach ($test_data['data'] as $object_name => $method_list) {
            
            /** Each test data item is checked */
            for ($count = 0; $count < count($method_list); $count++) {
            	/** The method name */
                $method_name         = $method_list[$count];                
               	/** The class method is tested */
                $method_results      = $this->RunClassMethodWithTestData($object_name, $method_name);
	            /** The total number of test cases is increased */
	        	$total_test_cases    += $method_results['test_cases'];
			    /** The test count is increased by 1 */
       	        $test_count++;
            }
        }
        
        /** The unit test results summary is displayed */
        Config::GetComponent("testresultsmanager")->DisplayUnitTestSummary($test_count, null, $total_test_cases);
    }
}
