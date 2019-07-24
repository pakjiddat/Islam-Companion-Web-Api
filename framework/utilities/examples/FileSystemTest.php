<?php

declare(strict_types=1);

namespace Framework\Utilities\Examples;

use \Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

error_reporting(E_ALL);
ini_set("display_errors", "1");
include('../autoload.php');

/**
 * Provides functions for test FileSystem package
 *
 * @category   UtilityClassTests
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
final class FileSystemTest
{
	/**
     * Used to test file system functions
     */
    public function TestFileSystem() : void
    {
        /** The html to validate */
		$html_content           = <<<EOT
		                                <!DOCTYPE html>
											<html>
											<head>
											<meta charset="UTF-8">
											<title>Title of the document</title>
											</head>
											
											<body>
											Content of the document......
											</body>
										</html>
EOT;
        										
		/** The carriage return and line feed are removed */								
        $html_content          = str_replace("\r", "", $html_content);
        $html_content          = str_replace("\n", "", $html_content);
        /** The url of the html validator */
        $validator_url         = "https://validator.nu/?out=text";
							
        /** The http headers to be sent with the request */							
        $headers               = array(
                                	"User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/70.0.3538.110 Chrome/70.0.3538.110 Safari/537.36",
                                    "Content-type: text/html; charset=utf-8"
                                );
        /** The html is validated */
        $validation_results    = UtilitiesFramework::Factory("urlmanager")->GetFileContent($validator_url, "POST", $html_content, $headers);

        /** The url to the style.css stylesheet is changed to an absolute url */
        $validation_results    = str_replace("style.css", $validator_url . "style.css", $validation_results);
        
        /** If the document was successfully validated */
        if (strpos($validation_results, "The document validates according to the specified schema(s).") !== false) {
        	$result = "<span style='color:green'><b>The document validates according to the specified schema(s).</span>";
        }
        /** If the document was not successfully validated */
        else {
            $result = "<span style='color:red'><b>The document does not validate according to the specified schema(s).</b></span>";        
        }
        /** The validation results are displayed */
        print_R($result);
    }
}

/** The FileSystemTest class object is created */
$filesystemtest = new FileSystemTest();
/** The TestFileSystem function is called */
$filesystemtest->TestFileSystem();
