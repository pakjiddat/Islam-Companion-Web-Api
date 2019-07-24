<?php

declare(strict_types=1);

namespace Framework\Utilities\Examples;

use \Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

error_reporting(E_ALL);
ini_set("display_errors", "1");
include('../autoload.php');

/**
 * Provides functions for test TemplateUtils package 
 *
 * @category   UtilityClassTests
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
final class TemplateUtilsTest
{
	/**
     * Used to test TemplateUtils class
     */
    public function TestTemplateUtils() : void
    {
        /** The TemplateUtils class object is fetched */
        $templateutils           = UtilitiesFramework::Factory("templateutils");
        /** The path to the template file */
        $template_path           = "example.html";
        /** The tag replacement information */
        $tag_replacement_arr     = array(array("title" => "Page title", "body" => "Body title"));
        /** The example template file is rendered */
        $template_file_contents  = $templateutils->GenerateTemplateFile($template_path, $tag_replacement_arr);
        /** The contents of the template file */
        var_export($template_file_contents);
    }
}

/** An object of class TemplateUtils is created */
$templateutils_test            = new TemplateUtilsTest();
/** The TestTemplateUtils function is called */
$templateutils_test->TestTemplateUtils();
