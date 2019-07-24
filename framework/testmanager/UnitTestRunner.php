<?php

declare(strict_types=1);

namespace Framework\TestManager;

use \Framework\Utilities\UtilitiesFramework as UtilitiesFramework;
use \Framework\Application\CommandLine as CommandLine;
use \Framework\Config\Config as Config;

/**
 * Provides functions for unit test an application
 *
 * @category   Testing
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
final class UnitTestRunner
{
	/**
     * It starts the timer for measuring execution time of unit tests
     * It displays informational messages to the console
     *
     * @return string $test_results the output text
     */
    private function InitUnitTests() : void 
    {
        /** The code coverage data is initialized */
        Config::$config['test']['code_coverage'] = array();
        /** The timer for measuring execution time of all tests is started */
        UtilitiesFramework::Factory("profiler")->StartProfiling("execution_time");
        /** The total execution time is initialized */
        Config::$config['test']['time_taken']     = 0;
        
        /** The application name */
        $app_name                                 = Config::$config['general']['app_name'];
        /** The current date */
        $current_date                             = date("d-m-Y H:i:s");
        /** The output text */
        $test_results                             = <<< EOT
    <bold>    
    Date:                $current_date
    Application:         $app_name
    </bold>
    Running Unit Tests\n\n
EOT;
        /** The output is shown */
        CommandLine::DisplayOutput($test_results);
        
        /** The test results are saved to application configuration */
        Config::$config['custom']['test_results'] = $test_results;
    }
    
    /**
     * Used to run unit tests
     * If the test type is database, then test data is read from database
     * If the test type is class, then the test classes given in application config are unit tested
     */
    public function RunUnitTests() : void 
    {    
        /** The test type */
        $test_type                   = Config::$config['test']['test_type'];
        /** The unit test is initialized */
        $this->InitUnitTests(); 
        /** If the test type is 'blackbox' */
        if ($test_type == 'blackbox')
            Config::GetComponent("blackboxtesting")->RunBlackBoxTests();
        /** If the test type is 'whitebox' */
        else if ($test_type == 'whitebox')
            Config::GetComponent("whiteboxtesting")->RunWhiteBoxTests();
        /** If the test type is 'ui' */
        else if ($test_type == 'ui')
            Config::GetComponent("uitesting")->RunUiTests();                 
    }
}
