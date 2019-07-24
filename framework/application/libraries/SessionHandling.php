<?php

declare(strict_types=1);

namespace Framework\Application\Libraries;

use \Framework\Config\Config as Config;

/**
 * This class provides function for handling sessions
 *
 * @category   Libraries
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class SessionHandling
{
    /**
     * This function enables php sessions
     */
    public function EnableSessions() : void 
    {
       /** If the session is not started then it is started */
       if (!$this->IsSessionStarted()) {
           /** The session is started */
           session_start();
           /** A new session id is generated */
           session_regenerate_id();
       }
	   /** The session data is set to application config */
       Config::$config["general"]["session"]   = $_SESSION;
    }
    
    /**
     * Used to authenticate the user
     * It checks the value of the is_logged_in session variable
     * If the user is not logged in and the current page is not the login page or the login validation url
     * Then the user is redirected to the login page
     */
    public function RedirectIfNotLoggedIn() : void 
    {
        /** The login validation url */
        $login_validation_url    = Config::$config["general"]["session_auth"]["login_validation_url"];
		/** The login url */
        $login_url               = Config::$config["general"]["session_auth"]["login_url"];
        /** The current url */
        $url                     = Config::$config["general"]["request_uri"];
        /** If the current page is not same as login url */        
        if ($url != $login_validation_url && $url != $login_url && $this->GetSessionConfig('is_logged_in') != "yes") {
            /** The user is redirected to the login page */
            Config::GetComponent("application")->Redirect($login_url);
        }
    }
    
    /**
     * Used to logout the user
     *
     * It unsets the is_logged_in session variable
     * It redirects the user to the login page
     */
    public function Logout() : void 
    {
        /** The session variable is_logged_in is unset */
        Config::GetComponent("sessionhandling")->SetSessionConfig("is_logged_in", "", true);
        /** The login url */
        $login_url = Config::$config["general"]["session_auth"]["login_url"];
        /** The user is redirected to the login page */
        Config::GetComponent("application")->Redirect($login_url);
    }
    
    /**
     * Used to check if the given user name and password match the login information in application config
     *
     * @param string $user_name the user name
     * @param string $password the user password
     *
     * @return boolean $is_valid indicates if the given credentials are valid
     */
    public function CheckConfigCredentials(string $user_name, string $password) : bool
    {
        /** Indicates if the given credentials are valid */
        $is_valid         = false;
        /** The valid credentials are fetched */
        $credentials      = Config::$config["general"]["session_auth"]["credentials"];
        /** Each valid credential is checked against the given credentials */
        for ($count = 0; $count < count($credentials); $count++) {
            /** If the user name and/or password is incorrect then the user is marked as not logged in */
            if ($credentials[$count]['user_name'] == $user_name && $credentials[$count]['password'] == $password) {
                /** is_valid is set to true */
                $is_valid  = true;
                /** The session variable is_logged_in is set */
                $this->SetSessionConfig("is_logged_in", "yes", false);
                /** The session variable full_name is set */
                $this->SetSessionConfig("full_name", $credentials[$count]['full_name'], false);
            }
        }
       
        return $is_valid;
    }
    
    /**
     * Used to set the given session config
     *
     * @param string $config_name name of the required session config
     * @param string $config_value value of the required session config
     * @param boolean $unset indicates if the session variable should be unset
     */
    public function SetSessionConfig(string $config_name, string $config_value, bool $unset) : void 
    {
        /** If the session variable should be unset */
        if ($unset) {
            unset($_SESSION[$config_name]);
        }
        else {
            /** Sets the given session variable */
            $_SESSION[$config_name] = $config_value;
        }
    }
    /**
     * Used to get the given session config     
     *
     * @param string $config_name name of the required session config
     *
     * @return string $session_config_value the session config value
     */
    public function GetSessionConfig(string $config_name) : string
    {
        /** If the given session variable is not set then function returns empty */
        if (!isset($_SESSION[$config_name])) {
            $session_config_value = "";
        }
        else {
            /** Returns the given session variable */
            $session_config_value = $_SESSION[$config_name];
        }
        return $session_config_value;
    }
    
    /**
     * Used to determine if a session has been started
     *
     * @return boolean $is_session_started true if session is already started. false if session has not been started
     */
    public function IsSessionStarted() : bool
    {
        /** Indicates if the session has been started */
        $is_session_started = false;
        /** If the php is not being run from command line */
        if (php_sapi_name() !== 'cli') {
            /** If the current php version is greater than or equal to 5.4.0 */
            if (version_compare(phpversion() , '5.4.0', '>=')) {
                $is_session_started = session_status() === PHP_SESSION_ACTIVE ? true : false;
            }
            else {
                $is_session_started = session_id() === '' ? false : true;
            }
        }
        return $is_session_started;
    }
    
    /**
     * Used to authenticate the user submitted login credentials
     *
     * It checks if the credentials submitted by the user match the credentials in database
     * If the credentials match, then the user is redirected to the url given in application configuration
     * If the credentials do not match then the function returns false
     *
     * @return json $validation_result the result of validating the user credentials
     */
    public function ValidateLoginCredentials() : string
    {
        /** The application parameters are fetched */
        $parameters              = Config::$config["general"]["parameters"];
        /** The user name is base64 decoded */
        $parameters['user_name'] = base64_decode($parameters['user_name']);
        /** The password is base64 decoded */
        $parameters['password']  = base64_decode($parameters['password']);
        /** The redirect url is fetched */
        $redirect_url            = Config::$config["general"]["session_auth"]["post_login_url"];
        
        /** Indicates if the login information is valid */
        $is_valid                = false;
        /** If the authentication type is set to db" */        
        if (Config::$config["general"]["session_auth"]["type"] == "db")
            $is_valid = $this->CheckDbCredentials($parameters['user_name'], $parameters['password']);
        /** If the authentication type is set to config" */
        else if (Config::$config["general"]["session_auth"]["type"] == "config")
            $is_valid = $this->CheckConfigCredentials($parameters['user_name'], $parameters['password']);            
        
        /** If the given credentials are not valid then an error message is displayed to the user */
        if (!$is_valid) {
			/** The result of validating the user credentials */
			$validation_result = array(
			    "message" => "Please enter a valid user name and password",
			    "is_valid" => "no"
			);
        }
        /** If the credentials were valid */
        else {
            /** The result of validating the user credentials */
			$validation_result = array(
			    "message" => $redirect_url,
			    "is_valid" => "yes"
			);
        }
       
        /** The validation results are json encoded */
        $validation_result = json_encode($validation_result);
        
        return $validation_result;
    }
    /**
     * Used to authenticate the user submitted login credentials
     *
     * It checks if the credentials submitted by the user match the credentials given in database
     * The database table name and column are given in application config
     * If the credentials match, then the user is redirected to the given url
     * If the credentials do not match then the function returns false
     *
     * @param string $user_name the user name
     * @param string $password the user password
     *
     * @return boolean $is_valid used to indicate if the given credentials are valid
     */
    public function CheckDbCredentials(string $user_name, string $password) : bool
    {
        /** The login columns */
        $fields     = Config::$config["general"]["session_auth"]["columns"];
        /** The short table name of the login table */
        $table_name = Config::$config["general"]["session_auth"]["table_name"];
        /** The mysql user table name is fetched */
        $table_name = Config::$config['general']['mysql_table_names'][$table_name];
     	/** The dbinit object is fetched */
        $dbinit     = Config::GetComponent("dbinit");
        /** The Database class object is fetched */
        $database   = $dbinit->GetDbManagerClassObj("Database");
        /** The sql query for fetching the user record */
        $sql        = "SELECT " . $fields['password'] . "," . $fields['first_name'];
        $sql        .= " FROM " . $table_name . " WHERE " . $fields["user_name"] . "=?";        
        /** The sql query parameters */
        $params     = array($user_name);
        /** The first row is fetched */
        $row        = $database->FirstRow($sql, $params);
        
        /** If the password matches */
        if (isset($row[$fields['password']]) && password_verify($password, $row[$fields['password']])) {
            /** The password is valid */
            $is_valid   = true;
        }
        /** The password is not valid */
        else {
            $is_valid   = false;
        }
        /** If the password is valid */
        if ($is_valid) {
            /** The session configuration is set */
            $this->SetSessionConfig("is_logged_in", 'yes', false);
            /** The session configuration is set */
            $this->SetSessionConfig("first_name", $row[$fields['first_name']], false);
        }    
        
        return $is_valid;
    }
}
