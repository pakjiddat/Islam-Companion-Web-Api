<?php

declare(strict_types=1);

namespace Framework\Utilities\Examples;

use Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

error_reporting(E_ALL);
ini_set("display_errors", "1");
include('../autoload.php');

/**
 * Provides functions for test Encryption package
 *
 * @category   UtilityClassTests
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
final class EncryptionTest
{
	/**
     * Used to test encryption and decryption of text
     */
    public function TestEncryption() : void
    {
        /** The encryption object is fetched */
        $encryption     = UtilitiesFramework::Factory("encryption");
        /** The text to be encrypted */
        $original_text  = "test encryption";
        /** The original text is encrypted */
        $encrypted_text = $encryption->EncryptText($original_text);
        /** The encrypted text is decrypted */
        $decrypted_text = $encryption->DecryptText($encrypted_text);
        /** If the original text matches the decrypted text then following message is shown */
        if ($original_text == $decrypted_text) echo "Text sucessfully decrypted";
        else echo "Text could not be decrypted";
    }
}

/** The EncryptionTest class object is created */
$encryption = new EncryptionTest();
/** The TestEncryption function is called */
$encryption->TestEncryption();
