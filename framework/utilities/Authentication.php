<?php

declare(strict_types=1);

namespace Framework\Utilities;

/**
 * This class provides authentication related functions
 * 
 * @category   UtilityClass
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class Authentication
{
    /** @var object The single static instance */
    protected static $instance;
    /**
     * Used to return a single instance of the class
     * 
     * Checks if instance already exists. If it does not exist then it is created. The instance is returned
     *
     * @param array $parameters an array containing class parameters
     * 
     * @return Authentication static::$instance name the instance of the correct child class is returned 
     */     
    public static function GetInstance($parameters) : Authentication
    {
        if (static::$instance == null) {
            static::$instance = new static($parameters);
        }
        return static::$instance;
    }
	
    /**
     * Parses http digest authentication response from user
     * Used to parse the http digest authentication data sent by the user
     * The digest string is parsed into associative array
     *
     * @param string $digest_str the http digest string returned by the user
     * 
     * @return array $data the http digest string returned by the web server			
     */
    private function HttpDigestParse(string $digest_str) : array
    {
        /** Contains default value for the digest data */
        $needed_parts = array(
            'nonce' => 1,
            'nc' => 1,
            'cnonce' => 1,
            'qop' => 1,
            'username' => 1,
            'uri' => 1,
            'response' => 1
        );
        $data         = array();
        $keys         = implode('|', array_keys($needed_parts));
        
        preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $digest_str, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $m) {
            $data[$m[1]] = $m[3] ? $m[3] : $m[4];
            unset($needed_parts[$m[1]]);
        }
        
        return $needed_parts ? false : $data;
	}
	
    /**
     * Used to implement browser based digest http authentication			 
     *
     * Authenticates the user using digest authentication
     * 
     * @param array $credentials list of valid user credentials
     *    user_name => string user name
     *    password => string user password	  
     * @param string $authentication_box_title the title of the authentication box			
     *
     * @throws Exception throws an exception if the current script is run from command line	
     * 
     * @return boolean $is_valid used to indicate if the user entered a valid user name and password
     */
    public function AuthenticateUser(array $credentials, string $authentication_box_title) : bool
    {
        /** If the application is run from browser then an exception is thrown */
        if (!isset($_SERVER['HTTP_HOST']) && !isset($_SERVER['HTTPS_HOST']))
            throw new \Exception("Application must be run from browser!");
        
        $is_valid = true;
        $realm    = $authentication_box_title;
        /** This implies user hit the cancel button */
        if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
            header('HTTP/1.1 401 Unauthorized');
            header('WWW-Authenticate: Digest realm="' . $realm . '",qop="auth",nonce="' . uniqid() . '",opaque="' . md5($realm) . '"');
            
            return false;
        }
        if ($is_valid) {
            /** Analyze the PHP_AUTH_DIGEST variable */
            if (!($data = $this->HttpDigestParse($_SERVER['PHP_AUTH_DIGEST'])))
                $is_valid = false;
            
            $user_password = false;
            for ($count = 0; $count < count($credentials); $count++) {
                $user_login = $credentials[$count];
                if ($user_login['user_name'] == $data['username']) {
                    $user_password = $user_login['password'];
                    break;
                }
            }
            /** If the user does not exist then return false */
            if (!$user_password)
                $is_valid = false;
            
            if ($is_valid) {
                /** Generate the valid response */
                $A1             = md5($data['username'] . ':' . $realm . ':' . $user_password);
                $A2             = md5($_SERVER['REQUEST_METHOD'] . ':' . $data['uri']);
                $valid_response = md5($A1 . ':' . $data['nonce'] . ':' . $data['nc'] . ':' . $data['cnonce'] . ':' . $data['qop'] . ':' . $A2);
                
                /** If the response from user is not valid then function returns false */
                if ($data['response'] != $valid_response)
                    $is_valid = false;
            }
        }
        
        if (!$is_valid) {
            header('WWW-Authenticate: Basic realm="' . $authentication_box_title . '"');
            header('HTTP/1.0 401 Unauthorized');
        }
        
        return true;
    }
}
