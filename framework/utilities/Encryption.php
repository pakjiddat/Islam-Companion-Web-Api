<?php

declare(strict_types=1);

namespace Framework\Utilities;

/**
 * This class provides functions for encrypting and decrypting text
 *
 * @category   UtilityClass
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class Encryption
{
    /** @var Encryption $instance The single static instance */
    protected static $instance;
    /** @var string $key Holds the key used for encrypting and decrypting text */
    private $key;
    /**
     * Used to return a single instance of the class
     *
     * Checks if instance already exists. If it does not exist then it is created. The instance is returned
     *
     * @return Encryption static::$instance name the instance of the correct child class is returned
     */
    public static function GetInstance() : Encryption
    {
        if (static ::$instance == null) 
        {
            static ::$instance = new static();
        }
        return static ::$instance;
    }
    /**
     * Initialize the class and set its properties     
     */
    protected function __construct() 
    {
        # The encryption key is generated */
        $this->key = sodium_crypto_secretbox_keygen();
    }
    /**
     * Function used to encrypt given text
     * Taken from: http://php.net/manual/en/intro.sodium.php#122003
     *
     * @param string $text the text to encrypt
     *
     * @return string $ciphertext the encrypted text
     */
    public function EncryptText(string $text) : string
    {
    	/** A random sequence of data is generated */
        $nonce           = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
		/** The data is base 64 encoded */
    	$ciphertext      = base64_encode($nonce . sodium_crypto_secretbox($text, $nonce, $this->key));
    	/** The buffer memory is overwritten with 0s */
    	sodium_memzero($text);
    	/** The buffer memory is overwritten with 0s */
    	//sodium_memzero($this->key);
    	
    	return $ciphertext;
    }
    /**
     * Function used to decrypt given text
     *
     * @param string $ciphertext_base64 the encrypted text
     *
     * @throws Exception an object of type Exception is thrown if the given encrypted text could not be decrypted
     *
     * @return string $decrypted_string the decrypted text
     */
    public function DecryptText(string $ciphertext_base64) : string
    {
    	/** The encrypted message is decoded */
        $decoded                = base64_decode($ciphertext_base64);
        /** If the message could not be decoded */
    	if ($decoded === false) {
    		/** An Exception is thrown */
        	throw new \Exception('The given message could not be decoded');
	    }
	    /** If the length of the decoded text is less than the original length */
	    if (mb_strlen($decoded, '8bit') < (SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES)) {
        	/** An Exception is thrown */
        	throw new \Exception('The given message could not be decoded');
    	}
    	/** The nonce is extracted from the data */
    	$nonce                 = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
    	/** The cipher text is extracted from the data */
	    $ciphertext            = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');
		/** The text is decrypted */
    	$decrypted_string      = sodium_crypto_secretbox_open($ciphertext, $nonce, $this->key);
    	/** If the text could not be decrypted */
    	if ($decrypted_string === false) {
    		/** An Exception is thrown */
        	throw new \Exception('The given message could not be decoded');
	    }
    	/** The buffer memory is overwritten with 0s */
    	sodium_memzero($ciphertext);
    	/** The buffer memory is overwritten with 0s */
    	sodium_memzero($this->key);
    	
	    return $decrypted_string;
    }
    /**
     * Used to encode the given array data
     *
     * It first json encodes the data. Then it applies base64 encoding to the string
     * The resulting string is returned
     *
     * @param array $data the data to be encoded
     *
     * @return string $encoded_data the encoded data
     */
    public function EncodeData(array $data) : string
    {
        /** If the data is an array then it is json encoded */
        if (is_array($data)) 
        {
            $data = json_encode($data);
        }
        /** The data is base64 decoded */
        $encoded_data = base64_encode($data);
        
        return $encoded_data;
    }
     /**
     * Used to decode the given data
     *
     * It first base64 decodes the string
     * If the resulting string is json encoded then it is json decoded
     *
     * @param string $encoded_data the encoded data
     * @param boolean $force_decoding used to indicate that the data is base64 encoded and should be decoded without checking
     *
     * @return array $original_data the original data
     */
    public function DecodeData(string $encoded_data, bool $force_decoding = false) : array
    {
        /** If the given data string is not base64 encoded then it is returned without decoding */
        if (!$force_decoding && !UtilitiesFramework::Factory("stringutils")->IsBase64($encoded_data)) return $encoded_data;
        /** The data is base64 decoded */
        $original_data = base64_decode($encoded_data);
        /** If the data is a json string then it is json decoded */
        if (UtilitiesFramework::Factory("stringutils")->IsJson($original_data)) 
        {
            $original_data = json_decode($original_data, true);
        }
        /** If the decoded data is not a json string, then the data is enclosed in an array */
        else $original_data = array("value" => $original_data);
        
        return $original_data;
    }
    /**
     * Function used to generate random string
     *
     * @param int $string_length the number characters in the generated string
     * @param string $type [alphnum~numeric~alpha] the type of characters to include
     *
     * @return string $random_string the random string
     */
    public function GenerateRandomString(int $string_length, string $type) : string
    {
        /** The start asci decimal value for the characters */
        $start = 48;
        /** The end asci decimal value for the characters */
        $end = 126;
        /** The list of characters used to generate the random string */
        $character_list = array();
        /** Each character is added to an array */
        for ($count = $start;$count <= $end;$count++) 
        {
            /** The type of the character */
            $char_type = array();
            if ($count >= 65 && $count <= 90) 
            {
                $char_type[] = "alpha";
                $char_type[] = "alphanumeric";
            }
            if ($count >= 97 && $count <= 122) 
            {
                $char_type[] = "alpha";
                $char_type[] = "alphanumeric";
            }
            if ($count >= 48 && $count <= 57) 
            {
                $char_type[] = "numeric";
                $char_type[] = "alphanumeric";
            }
            /** If the type of the character is valid */
            if (in_array($type, $char_type) || $type == "all") $character_list[] = chr($count);
        }        
        /** The characters are shuffled */
        shuffle($character_list);
        /** A random string is extracted using array_rand function. It returns random array keys */
        $random_string_arr_keys = array_rand($character_list, $string_length);
        /** The random string array values */
        $random_string_arr_values = array();
        /** The random string values are generated */
        for ($count = 0;$count < count($random_string_arr_keys);$count++) 
        {
            $random_string_arr_values[] = $character_list[$random_string_arr_keys[$count]];
        }
        /** The random string is imploded */
        $random_string = implode("", $random_string_arr_values);
        
        return $random_string;
    }
}

