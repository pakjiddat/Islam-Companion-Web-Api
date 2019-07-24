<?php

declare(strict_types=1);

namespace Framework\Utilities\Examples\Helpers;

use Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

error_reporting(E_ALL);
ini_set("display_errors", "1");
include('../autoload.php');

/**
 * Provides functions for test authentication package
 *
 * It provides functions that test authentication package
 *
 * @category   UtilityClassTests
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
final class Authentication
{
	/** 
     * Authentication function test
     * Used to test http digest authentication
     */
    public function AuthenticationTest() : void
    {
        echo "<h2>Testing function: AuthenticationTest </h2>";
        /** List of valid user credentials. used to test the http digest authentication */
        $credentials = array(
            array(
                "user_name" => "admin",
                "password" => "admin"
            ) ,
            array(
                "user_name" => "manager",
                "password" => "manager"
            )
        );
        /** The custom text to use in the authentication box that shows in the browser */
        $authentication_box_title = "Protected Area!";
        /** The authentication object is fetched */
        $authentication = UtilitiesFramework::Factory("authentication");
        /** 
         * If the user presses the cancel button then following message is shown
         * If the user entered the wrong credentials then he will be asked to login again
         */
        if (!$authentication->AuthenticateUser($credentials, $authentication_box_title)) echo "You pressed the cancel button!.";
        /** If the user entered the correct login information then the following message is shown */
        else echo "You entered the correct login information!.";
    }
}
