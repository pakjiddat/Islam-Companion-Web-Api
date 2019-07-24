<?php

declare(strict_types=1);

namespace Framework\TestManager;

use \Framework\Config\Config as Config;

/**
 * Provides functions for measuring code coverage
 *
 * @category   Testing
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
final class CodeCoverageGenerator
{
    /**
     * Used to create a summary of the code coverage
     *
     * It creates a summary of the code coverage
     * The summary contains for each code file, the percentage of the executable code that was run
     * It also saves the raw code coverage data to file
     *
     * @param array $code_coverage the code coverage for all the unit tests
     *
     * @return string $cc_summary the code coverage summary
     */
    public function GetCodeCoverage(array $code_coverage) : string
    {
    	/** The file count */
    	$file_count               = 0;
    	/** The required code coverage summary */
    	$cc_summary               = array();
    	/** The application folder path */
    	$folder_path              = Config::$config["path"]["app_path"];
    	/** The line break */
    	$line_break               = Config::$config["general"]["line_break"];
    	/** Each file is checked */
    	foreach ($code_coverage as $file_name => $coverage_details) {
    		/** If the file is not in the application folder, then the loop continues */
    		if (strpos($file_name, $folder_path) === false) continue;
    		/** The total lines of executable code */
    		$total_lines          = count($coverage_details);
    		/** The number of lines of code that were executed */
    		$executed_code        =	0;
    		/** Each line is checked */
    		foreach ($coverage_details as $line_number => $status) {
    		    /** If the line was executed */
    		    if ($status == '1') {
	    			/** The executed code count is increased */
	    			$executed_code++;
	    		}
    		}
    		/** The percentage of the executable code that was run */
    		$file_coverage        = number_format(($executed_code/$total_lines) * 100, 2);
    		/** The code coverage summary */
    		$summary              = $file_name . " (<green>" . $file_coverage . "%</green> - <green>" . 
    		                       $executed_code . "</green> out of <cyan>" . $total_lines . "</cyan>)";
    		/** The code coverage summary is updated */
    		$cc_summary[$summary] = ($file_coverage);
    	}
    	/** The code coverage is sorted */
    	arsort($cc_summary);    	
    	/** The code coverage summary text */
    	$summary_text             = "    <underline>Code Coverage Summary</underline>"  . $line_break;
    	
    	/** Each file is checked */
    	foreach ($cc_summary as $summary => $file_coverage) {
    		/** The file count is incremented */
    		$file_count++;
    		/** The summary text is updated */
    		$summary_text        .= ($line_break . "    " . $file_count . ". " . $summary);
    	}
    	/** The code coverage summary */
    	$cc_summary              = $summary_text;
    	
    	return $cc_summary;
    }
}
