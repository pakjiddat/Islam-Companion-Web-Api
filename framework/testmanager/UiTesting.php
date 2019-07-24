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
final class UiTesting
{   
    /**
     * It scrapes all links from the given html
     * For each link it fetches the http response headers
     * If the response header is not valid, then an error message is shown and the script ends
     *
     * @param string $page_contents the contents of the page to validate
     * @param string $url the page url
     */
    public function CheckBrokenLinks(string $page_contents, string $url) : void 
    {
        /** The regular expression for scraping the links */
        $regex = "/href=['\"](.+)['\"]/iU";
        /** The page links are scrapped */
        preg_match_all($regex, $page_contents, $matches);
        /** Each link is checked */
        for ($count = 0; $count < count($matches[1]); $count++) {
            /** The link url */
            $link                = $matches[1][$count];
            /** If the link does not start with http:// or https://, then the loop continues */
            if (strpos($link, "http://") === false && strpos($link, "https://") === false)
                continue;
            /** If the link is included in list of broken links to ignore, then the loop continues */
            if (in_array($link, Config::$config['test']['ignore_links']))
                continue;                                        
            
            /** The link is downloaded. Ony the http headers are fetched */
            $http_response       = UtilitiesFramework::Factory("urlmanager")->GetFileContent(
                                      $link,
                                      "GET",
                                      "",
                                      array(),
                                      true
                                   );
            /** The http response is json decoded */
            $dec_http_response   = json_decode($http_response, true);
            /** If the http status code is not 200 or 301 */
            if (
                strpos($dec_http_response[0], "200") === false && 
                strpos($dec_http_response[0], "301") === false &&
                strpos($dec_http_response[0], "302") === false
            ) {
                /** The message text */
                $message  = "\n\n<red>Error</red>. The link: " . $link . " on the page: " . $url;
                $message .= " could not be fetched. Details: " . $http_response . "\n\n";
                /** The error test is shown */
                CommandLine::DisplayOutput($message);
                /** Script ends */
                die();
            }
        }
    }

    /**
     * It marks the given url in database as checked
     *
     * @param string $url the url to be marked as checked
     */
    private function MarkUrlAsChecked(string $url) : void 
    {
        /** The mysql table list */
        $table_name       = Config::$config["general"]["mysql_table_names"]["test_data"];
        /** The DbInit class object */
        $dbinit           = Config::GetComponent("frameworkdbinit");        
        /** The update query */
        $sql              = "UPDATE " . $table_name . " SET is_checked=1 WHERE url=?";
        /** The Database class object is fetched */
        $database         = $dbinit->GetDbManagerClassObj("Database");
        /** The query parameters */
        $query_params     = array($url);
        /** The url is marked as invalid */
        $database->Execute($sql, $query_params);
    }
        
    /**
     * It runs unit tests using information given in database
     *
     * For each url in database, the handler functions for the url are called one at a time
     * The html output for each function is validated using validator.nu
     */
    public function RunUiTests() : void 
    {
        /** The execution time profiler is started */
		UtilitiesFramework::Factory("profiler")->StartProfiling("execution_time");
        /** The number of unit tests run */
        $test_count        = 0;
        /** The test data */
        $test_data         = Config::GetComponent("testdatamanager")->LoadTestDataForUiTesting();
        /** For each test object */
        for ($count = 0; $count < count($test_data); $count++) {
                            
            /** The url whoose user interface components will be tested */
            $url           = $test_data[$count]['url'];
            /** The parameters for the request */
            $params        = $test_data[$count]['params'];
            /** The output text */
            $console_text  = "    --Testing url: " . $url . "\n";
            /** The progress of unit test is displayed */
            CommandLine::DisplayOutput($console_text);
            
            /** The page contents are fetched */
            $page_contents = UtilitiesFramework::Factory("urlmanager")->GetFileContent(
                                 $url,
                                 "POST",
                                 $params
                             );
                             
            /** The page is checked for broken links */
            $this->CheckBrokenLinks($page_contents, $url);
		    
            /** The html response is validated using validator.nu service */
            $result        = Config::GetComponent("testfunctionvalidator")->ValidateHtml($page_contents);
            /** If response is not valid */
            if (!$result['is_valid']) {
                /** The message text */
                $message = "\n\n<red>Error</red>. The url: " . $url . " could not be validated. Details:\n\n";
                $message .= $result['message'];
                /** The error test is shown */
                CommandLine::DisplayOutput($message);
                /** Script ends */
                die();
            }
            
            /** The test count is increased by 1 */
            $test_count++;
            /** The url is marked in database as checked */
            $this->MarkUrlAsChecked($url);
        }
        
        /** The execution time is fetched */
		Config::$config['test']['time_taken'] = UtilitiesFramework::Factory("profiler")->GetExecutionTime();
        
        /** The unit test results summary is displayed */
        Config::GetComponent("testresultsmanager")->DisplayUnitTestSummary($test_count);
    }
}
