<?php

declare(strict_types=1);

namespace Framework\TestManager;

use \Framework\Utilities\UtilitiesFramework as UtilitiesFramework;
use \Framework\Config\Config as Config;

/**
 * This class provides functions for pre processing and post processing test functions
 *
 * @category   Testing
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class TestFunctionProcessor
{    
    /**     
     * This function is used to pre process test functions
     *
     * It is called by the framework before running each test
     * It may be overridden by child class
     * By default the function starts the timer used for calculating execution time
     *
     * @param string $trace_file_name the name of the trace file
     */
    public function PreProcessTestFunction(string $trace_file_name) : void
    {
        /** If the xdebug_start_code_coverage function exists and the code coverage should be generated */
		if (function_exists("xdebug_start_code_coverage") && Config::$config['test']['enable_code_coverage']) {
		    /** The code coverage is started */
		    \xdebug_start_code_coverage( XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);	    
		}
    	/** If the xdebug_start_trace function exists and the function trace should be generated */
		if (function_exists("xdebug_start_trace") && Config::$config['test']['enable_trace']) {
			/** The trace file path */
			$trace_file_path = Config::$config['test']['trace_folder'] . DIRECTORY_SEPARATOR . $trace_file_name;
			/** The function tracing is started */
			xdebug_start_trace($trace_file_path, XDEBUG_TRACE_NAKED_FILENAME);
		}
		/** The execution time profiler is started */
		UtilitiesFramework::Factory("profiler")->StartProfiling("execution_time");
		/** The memory usage profiling is started */
		UtilitiesFramework::Factory("profiler")->StartProfiling("memory_delta");
    }
    
    /**     
     * This function is used to post process test functions
     *
     * It is called by the framework after running a test function
     * It should be overridden by child class
     * By default the function saves the profiling information of the function to database
     *
     * @param json $item_details the item being tested. for example json encoded array containing method and object names
     * @param array $params the test item parameters
     * @param string $cc_file_name the code coverage file name
     * @param bool $stop_cc indicates that code coverage should be stopped
     */
    public function PostProcessTestFunction(
        string $item_details,
        array $params,
        string $cc_file_name,
        bool $stop_cc) : void
    {
    	/** If the xdebug_stop_trace function exists and the function trace should be generated */
		if (function_exists("xdebug_stop_trace") && Config::$config['test']['enable_trace']) {
			/** The function tracing is stopped */
			xdebug_stop_trace();
		}
		/** If the xdebug_stop_code_coverage function exists and the code coverage should be generated and the code coverage should be stopped */
	    if (function_exists("xdebug_stop_code_coverage") && Config::$config['test']['enable_code_coverage'] && $stop_cc) {
	        /** The code coverage for the last unit test */
	        $last_code_coverage                      = \xdebug_get_code_coverage();
	        /** The code coverage data for all unit tests */
	        $total_code_coverage                     = Config::$config['test']['code_coverage'];
            /** The code coverage data for all unit tests is updated */
            $total_code_coverage                     = array_merge($total_code_coverage, $last_code_coverage);            
            /** The total code coverage is updated */
            Config::$config['test']['code_coverage'] = $total_code_coverage;
            
            /** The code coverage is stopped */
            \xdebug_stop_code_coverage();
           
            /** The raw code coverage is json encoded */
            $encoded_code_coverage                   = json_encode($last_code_coverage);
        	/** The code coverage file name */
        	$code_coverage_file                      = Config::$config["test"]["code_coverage_folder"] . 
        	                                           DIRECTORY_SEPARATOR . $cc_file_name;
        	/** The code coverage data is written to file */
        	UtilitiesFramework::Factory("filemanager")->WriteLocalFile(
        	    $encoded_code_coverage,
        	    $code_coverage_file,
        	    "w"
            );
        }
        
	    /** The execution time is fetched */
		$time_taken                                  = UtilitiesFramework::Factory("profiler")->GetExecutionTime();
		/** The memory delta is fetched */
		$memory_delta                                = UtilitiesFramework::Factory("profiler")->GetMemoryDelta();
		/** The memory delta is converted to Mb */
		$memory_delta                                = (float) number_format(($memory_delta/1000000), 4);
		/** The list of included files */
		$included_files                              = get_included_files();
		/** The total time taken is increased by the time taken to run the test */
		Config::$config['test']['time_taken']        += $time_taken;
    	
    	/** The encoded function parameters */
    	$params                                      = json_encode($params);
	    /** The database queries and execution trace are saved to database */
	    Config::GetComponent("testresultsmanager")->SaveTestDetails(
            $item_details,
            $params,
            $time_taken,
            $memory_delta,
            $included_files
        );
    }
}
