<?php

declare(strict_types=1);

namespace Framework\Utilities\Examples;

use \Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

error_reporting(E_ALL);
ini_set("display_errors", "1");
include('../autoload.php');

/**
 * Provides functions for test ErrorManager package
 *
 * @category   UtilityClassTests
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
final class ErrorManagerTest
{
    /**
     * Custom error handling function
     *
     * @param string $log_message the error message formatted by the ErrorManager class. it contains the stack trace including function parameters
     * @param array $error_parameters the error parameters
     *    error_level => int the error level
     *    error_message => string the error message
     *    error_file => string the error file name
     *    error_line => int the error line number
     *    error_context => array the error context
     */
    public function CustomErrorHandler(string $log_message, array $error_parameters) 
    {
    	/** The custom error message thrown by the test exception */
        echo "Custom error message: " . $log_message;
    }
    /**
     * Custom shutdown function
     * Its automatically called when the script exits
     */
    public function CustomShutdown() 
    {
    	/** Message that is shown when the script execution ends */
        echo "Custom shudown function. Script has ended!";
    }
	/**
     * Error handling test
     * Used to test error handling
     */
    public function TestErrorManager() : void
    {
        /** The parameters for ErrorHandler object */
        $parameters                             = array();
        /** Custom shutdown function. It is automatically called just before script exits */
        $parameters['shutdown_function']        = array($this, "CustomShutdown");
        /** Used to indicate if the error message should be in plain text format */
        $parameters['use_plain_text_format']     = true;
        /** The list of folders to ignore */
        $parameters['ignored_folders']          = "";
        /** Custom error handling function */
        $parameters['custom_error_handler']     = array($this, "CustomErrorHandler");
        /** The ErrorManager class object is fetched */
        $errormanager                           = UtilitiesFramework::Factory("errormanager", $parameters);
        /** Throw an exception for test the error handling */
        throw new \Error("Test exception!", 10);
    }
}

/** The ErrorManagerTest class object is created */
$errormanager_test = new ErrorManagerTest();
/** The TestErrorManager function is called */
$errormanager_test->TestErrorManager();
