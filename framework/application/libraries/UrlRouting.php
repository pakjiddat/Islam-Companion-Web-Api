<?php

declare(strict_types=1);

namespace Framework\Application\Libraries;

use \Framework\Utilities\UtilitiesFramework as UtilitiesFramework;
use \Framework\Config\Config as Config;

/**
 * This class provides functions for routing urls
 *
 * @category   Libraries
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class UrlRouting
{
    /**
     * It parses the Callbacks.txt file
     * It finds the callback to use for the current application request
     * The callback is saved to application config
     */
    public function GetCallback() : void
    {
        /** The required callback */
        $callback                           = array();
        /** The validator callback for the current request */
        $validator                          = array();        
        /** The current url */
        $url                                = Config::$config["general"]["request_uri"];
        /** The site url */
        $site_url                           = Config::$config["general"]["site_url"];
        /** The current url is updated */
        $url                                = str_replace($site_url, "", $url);
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
            list($label, $content)          = explode(":", $data[$count]);
            /** If the label is url */
            if ($label == "url") {
                /** The url pattern is formatted */
                $url_pattern                = "/" . trim(str_replace("/", "\/", $content)) . "/";
                /** The url pattern is matched with the current url */
                preg_match($url_pattern, $url, $matches);
                /** If there is a match */
                if (count($matches) > 0) {
                    /** The callback to use for the request */
                    $text                 = str_replace("callback: ", "", $data[$count+1]);
                    /** The callback is json decoded */
                    $text                 = json_decode($text, true);
                    /** The callback object is set */
                    $callback             = array($text['object'], $text['function']);
                    /** If the next line contains validator information */
                    if (strpos($data[$count+2], "validator: ") === 0) {
                        /** The validator callback to use for the request */
                        $text                 = str_replace("validator: ", "", $data[$count+2]);
                        /** The validator is json decoded */
                        $text                 = json_decode($text, true);
                        /** The callback object is set */
                        $validator            = array($text['object'], $text['function']);
                    }                        
                    /** The loop ends */
                    break;
                }
           }
        }

        /** If the url mapping for the current url could not be found */
        if (!isset($callback[0])) {
            /** The default function for handling missing page */
            Config::GetComponent("application")->HandlePageNotFound();
        }

        /** The callback is set to application config */
        Config::$config["general"]["callback"]  = $callback;
        /** The validator is set to application config */
        Config::$config["general"]["validator"] = $validator;
    }
}
