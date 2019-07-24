<?php

declare(strict_types=1);

namespace Framework\Application\Libraries;

use \Framework\Utilities\UtilitiesFramework as UtilitiesFramework;
use \Framework\Application\CommandLine as CommandLine;
use \Framework\Config\Config as Config;

/**
 * This class provides functions for parsing command line arguments
 *
 * @category   Libraries
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class CliParsing
{
    /**
     * It parses the Callbacks.txt file
     * It finds the callback to use for the command entered by the user
     * The callback is saved to application config
     *
     * @param array $parameters the application parameters
     */
    public function GetCallback(array $parameters) : void
    {
        /** The required callback */
        $callback                           = array();
        /** The absolute path to the callback file */
        $callback_file_path                 = Config::$config["path"]["callback_file"];
        /** The contents of the Url Mapping file are fetched */
        $file_contents                      = UtilitiesFramework::Factory("filemanager")->ReadLocalFile(
                                                  $callback_file_path
                                              );
        /** The file contents are read to an array */
        $data                               = explode("\n", $file_contents);
        /** Each line in the data is checked */
        for ($count = 0; $count < count($data); $count++) {
            /** If the line is empty then the loop continues */
            if ($data[$count] == "") continue;
            /** The line is checked */
            list($label, $content)          = explode(": ", $data[$count]);
            /** If the label is command */
            if ($label == "command") {
                /** If there is a match */
                if ($content == $parameters["command"]) {
                    /** The callback to use for the request */
                    $text                 = str_replace("callback: ", "", $data[$count+1]);
                    /** The callback is json decoded */
                    $text                 = json_decode($text, true);
                    /** The callback object is set */
                    $callback             = array($text['object'], $text['function']);                    
                    /** The loop ends */
                    break;
                }
           }
        }

        /** If the callback for the command could not be found */
        if (!isset($callback[0])) {
            /** If the command was "Unit Test" */
            if ($parameters['command'] == "Unit Test")
                $callback             = array("unittestrunner", "RunUnitTests");
            /** If the command was "Generate Test Data" */
            else if ($parameters['command'] == "Generate Test Data")  
                $callback             = array("testdatamanager", "GenerateTestData");
            /** The default application usage is shown */
            else {
                /** If the command is not "Help" */
                if ($parameters['command'] != "Help") {
                    /** Warning message is shown */
               	    CommandLine::DisplayOutput("\n  <bold>Invalid command !</bold>\n", "red");
                }               	    
           	    /** The application usage is shown */
                Config::GetComponent("cliapplication")->HandleUsage();
            }
        }

        /** The callback is set to application config */
        Config::$config["general"]["callback"]  = $callback;
    }
}
