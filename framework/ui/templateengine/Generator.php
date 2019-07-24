<?php

declare(strict_types=1);

namespace Framework\Ui\TemplateEngine;

use \Framework\Config\Config as Config;
use \Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

/**
 * This class provides functions for generating content based on templates
 *
 * @category   TemplateEngine
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class Generator
{
    /**
     * It renders the template file using the given template parameters
     *
     * @param string $name the template file name
     * @param array $parameters optional the parameters used to render the given html template file
     * @param string $ui_framework the user interface framework to use
     *
     * @throws \Error an object of type Error is thrown if the file could not be found     
     *
     * @return string $template_html the template html string
     */
    public function Generate(
        string $name,
        ?array $parameters = null,
        ?string $ui_framework = null
    ) : string {
        /** The template file name */
        $template_file_name = (strpos($name, ".html") === false) ? $name . ".html" : $name;
        /** The path to the framework template folder is fetched */
        $fw_template_path   = $this->GetTemplateFolderPath($ui_framework);
        /** The path to the application template folder is fetched */
        $app_template_path  = Config::$config["path"]["app_template_path"];
        /** The folders to search */
        $search_folders     = array($app_template_path, $fw_template_path);
        /** The folders are searched for the given template file */
        $template_file_path = UtilitiesFramework::Factory("filemanager")->SearchFile(
                                  $search_folders,
                                  $template_file_name
                              );
        /** If the file does not exist, then an exception is thrown */
        if (!is_file($template_file_path)) 
            throw new \Error("Template file: " . $template_file_name . " could not be found");
        
        /** If the template parameters are given, then they are applied to the file */
        if (is_array($parameters)) {
		    /** The callback function for fetching missing template parameters */
		    $callback       = function ($tag_name) {
		                          /** An exception is thrown */
		                          throw new \Error("Value for tag: " . $tag_name . " could not be found");
		                          /** The tag name is checked in path config */
		                          //return (Config::$config['path'][$tag_name] ?? "");
		                      };
		                      
		    /** The general template is rendered using the given template parameters */
		    $template_html  = UtilitiesFramework::Factory("templateutils")->GenerateTemplateFile(
		                          $template_file_path,
		                          $parameters,
		                          $callback
		                      );
		}
		/** If the template parameters are not given, then the file contents are fetched */
		else 
		    $template_html  = UtilitiesFramework::Factory("filemanager")->ReadLocalFile($template_file_path);
		               
        return $template_html;
    }
    
    /**
     * Used to get the absolute path to the framework template folder for the current template library
     *
     * @param string $ui_framework the library to use
     *     
     * @return string $fw_template_path the absolute path to the framework template folder
     */
    public function GetTemplateFolderPath(?string $ui_framework = null) : string
    {
        /** The path to the framework template folder is fetched */
        $fw_template_path = Config::$config["path"]["fw_template_path"];
        /** If the template library name is not given */
        if ($ui_framework == null) {
            /** The template library name is fetched from application configuration */
            $ui_framework = Config::$config["general"]["ui_framework"];
        }
        /** The framework template folder path for the template library is determined */
        $fw_template_path = str_replace("{ui_framework}", $ui_framework, $fw_template_path);

        return $fw_template_path;
    }
}
