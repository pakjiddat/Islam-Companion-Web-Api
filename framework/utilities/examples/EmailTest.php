<?php

declare(strict_types=1);

namespace Framework\Utilities\Helpers;

use Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

error_reporting(E_ALL);
ini_set("display_errors", "1");
include('../autoload.php');

/**
 * Provides functions for test Email package
 *
 * @category   UtilityClassTests
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
final class EmailTest
{
	/**
     * Used to test email function
     */
    public function TestEmail() : void
    {        
        /* The Email class requires Mail and Mail_Mime pear package */
        include_once ("Mail.php");
        include_once ("Mail/mime.php");
        
        /* Change the from and to emails to your email address */
        $from_email       = "nadir@dev.pakjiddat.pk";
        $to_email         = "nadir@dev.pakjiddat.pk";
        /** The parameters for the email object */
        $parameters       = array("params" => "", "backend" => "mail");
        /* The Email class object is fetched */
        $email            = UtilitiesFramework::Factory("email", $parameters);
        /** The email is sent */
        $is_sent          = $email->SendEmail($from_email, $to_email, "Utilitiesframework Test",
                                                  "<h3>test html content</h3>", 
                                                  null,
                                                  array("/var/www/html/pakjiddat/islamcompanion/framework/utilities/examples/test.xls")
                            );
		/** If the email was sent, then information message is shown */
        if ($is_sent) echo "Email was successfully sent";
        else echo "Email could not be sent";
    }
}

/** The EmailTest class object is created */
$email_test = new EmailTest();
/** The TestEmail function is called */
$email_test->TestEmail();
