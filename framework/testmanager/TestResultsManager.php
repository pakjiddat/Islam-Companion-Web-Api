<?php

declare(strict_types=1);

namespace Framework\TestManager;

use \Framework\Application\CommandLine as CommandLine;
use \Framework\Utilities\UtilitiesFramework as UtilitiesFramework;
use \Framework\Config\Config as Config;

/**
 * Provides functions for generating test results
 *
 * @category   Testing
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
final class TestResultsManager
{
    /**
     * Used to save the test results to test folder and database
     *
     * It saves the results of test to file given in application config
     * It also saves the test results to database
     *
     * @param float $time_taken the time taken to run the test
     */
    public function SaveTestResults(float $time_taken) : void 
    {
        /** The absolute path of the test results file */
        $test_file_name          = Config::$config["test"]["test_results_file"];
        /** The application name */
        $app_name                = Config::$config["general"]["app_name"];
        /** The test results table name */
        $table_name              = Config::$config["general"]["mysql_table_names"]["test_results"];
        
        /** The html is removed from the test results. The <br/> is replaced with new line */
        $formatted_test_results  = strip_tags(str_replace("<br/>", "\n", Config::$config['custom']['test_results']));
        /** The application test results are written to test file */
        UtilitiesFramework::Factory("filemanager")->WriteLocalFile($formatted_test_results, $test_file_name);
        
        /** The test results data that needs to be logged */
        $test_results_data       = array(
                                        array(
										    "app_name" => $app_name,
										    "results" => $formatted_test_results,
										    "time_taken" => $time_taken,
										    "created_on" => time()
									    )
							        );

        /** The parameters for creating the LogManager object */
        $parameters              = array("dbinit" => Config::GetComponent("frameworkdbinit"));
        
        /** The test data is saved to database */
        UtilitiesFramework::Factory("logmanager", $parameters)->InsertLogData($test_results_data, $table_name);
    }
    /**
     * Used to save the database queries to database. It saves the given database queries to database
     *
     * @param json $item_details the item being tested. for example json encoded array containing method and object names
     * @param string $method_params the method parameters
     * @param float $time_taken the time taken to run the function
     * @param float $memory_delta the estimated amount of real memory taken by the function
     * @param array $included_files the list of included files
     */
    public function SaveTestDetails(
        string $item_details,
        string $method_params,
        float $time_taken,
        float $memory_delta,
        array $included_files
    ) : void {
        
    	/** The included file count */
    	$included_files_count            = count($included_files);
    	/** The included file list is formatted */
    	$included_files                  = implode("\n", $included_files);

        /** The parameters for creating the LogManager object */
        $parameters                      = array("dbinit" => Config::GetComponent("frameworkdbinit"));
        /** The LogManager object is fetched */
        $logmanager                      = UtilitiesFramework::Factory("logmanager", $parameters);
    	/** The DbLogManager object is fetched */
        $dblogmanager                    = Config::GetComponent("dbinit")->GetDbManagerClassObj("DbLogManager");
        /** The Database query log is fetched */
        $query_log_text                  = $dblogmanager->DisplayQueryLog(false);
        /** The Database query log array is fetched */
        $query_log                       = $dblogmanager->GetQueryLog();
     	/** The application name */
        $app_name                        = Config::$config["general"]["app_name"];
        /** The test details table name */
        $table_name                      = Config::$config["general"]["mysql_table_names"]["test_details"];

        /** The database query data that needs to be logged */
        $log_data                        = array(
										        "item" => $item_details,
										        "params" => $method_params,
										        "sql_queries" => $query_log_text,
										        "sql_query_count" => count($query_log),
										        "app_name" => $app_name,
										        "time_taken" => $time_taken,
										        "memory_delta" => $memory_delta,
										        "included_files" => $included_files,
										        "included_files_count" => $included_files_count,
										        "created_on" => time()
									        );


        /** The condition for fetching the log data */
        $condition                       = array(
                                               "condition" => "item=? AND app_name=?", 
                                               "values" => array($item_details, $app_name)
                                           );
        /** The log data is checked to see if it exists */
        $data_exists                     = $logmanager->LogDataExists($table_name, $condition);
        /** If the log data exists, then it is updated */
        if ($data_exists) {
            /** The test data is updated */
            $logmanager->UpdateLogData($log_data, $table_name, $condition);
        }
        /** If the log data does not exist */
        else {
            /** The log data is enclosed in array */
            $log_data                    = array($log_data);
            /** The test data is saved to database */
            $logmanager->InsertLogData($log_data, $table_name);
        }
    }
    
   	/**
     * It displays the unit test summary to the console
     *
     * @param int $test_count the number of functions tested
     * @param int $total_ac the total number of asserts
     * @param int $total_tc the total number of test cases
     */
    public function DisplayUnitTestSummary(int $test_count, ?int $total_ac = null, ?int $total_tc = null) : void 
    {        
        /** The time taken for the tests */
        $time_taken                  = Config::$config['test']['time_taken'];
        /** The time taken is formatted */
        $time_taken                  = (float) number_format($time_taken, 3);
        
        /** The test results */
        $console_text                = <<< EOT
        
    Result of unit testing:
    
    Number of functions tested: <green>$test_count</green>
EOT;

        /** If the assert count is given */
        if ($total_ac != null) {
            /** The test results are updated */
            $console_text            .= <<< EOT
            
    Number of asserts: <green>$total_ac</green>
EOT;
        }

        /** If the total number of test cases is given */
        if ($total_tc != null) {
            /** The test case count is updated */
            $console_text            .= <<< EOT
            
    Number of test cases: <cyan>$total_tc</cyan>
EOT;
        }
                
        /** The test results are updated */
        $console_text                .= "\n    Time taken: <red>" . $time_taken . " sec</red>";
                
    	/** If the xdebug extension has been enabled and code coverage should be generated */
        if (function_exists("xdebug_start_code_coverage") && Config::$config['test']['enable_code_coverage']) {
            /** The code coverage for all the unit tests */
            $code_coverage           = Config::$config['test']['code_coverage'];
    	    /** The code coverage information is fetched and appended to the test results */
    	    $code_coverage_summary   = Config::GetComponent("codecoveragegenerator")->GetCodeCoverage($code_coverage);
	        /** The code coverage is appended to the test results */
    	    $console_text            .= "\n\n" . $code_coverage_summary;
    	}
        /** The results of test are saved to file */
        if (Config::$config['test']['save_test_results']) {
            /** The test results are updated */
            Config::$config['custom']['test_results'] = $console_text;
        	/** The test results are saved to file and database */
        	Config::GetComponent("testresultsmanager")->SaveTestResults($time_taken);
        	/** The line breaks are appended to the test results */
			$console_text            .= "\n\n    ";
			/** The information message is appended to the test results */
			$console_text            .= "<bold>Test results were saved to file and database</bold>";
            /** The line breaks are appended to the test results */
			$console_text            .= "\n\n    ";
		}
		
       	/** The test results are displayed */
	    CommandLine::DisplayOutput($console_text);
    }
}
