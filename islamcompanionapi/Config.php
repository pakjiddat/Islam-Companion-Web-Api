<?php

declare(strict_types=1);

namespace IslamCompanionApi;

ini_set("include_path", '/home/pakjidda/php:' . ini_get("include_path") );

/**
 * Application configuration class
 *
 * Contains application configuration information
 * It provides configuration information and helper objects to the application
 *
 * @category   IslamCompanionApi
 * @package    Config
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class Config extends \Framework\Config\Config
{
    /**
     * Used to determine if the application request should be handled by the current module
     *
     * It returns true if the host name contains islamcompanion.pk
     * Otherwise it returns false
     *
     * @param array $parameters the application parameters
     *
     * @return boolean $is_valid indicates if the application request is valid
     */
    public static function IsValid(array $parameters) : bool
    {
        /** The request is marked as not valid by default */
        $is_valid = false;
        
        /** If the application is being run from command line */
        if (php_sapi_name() == "cli") {
            /** If the application name is "islamcompanionapi" */
            if ($parameters['application'] == "islamcompanionapi") {
                $is_valid = true;
            }
        }
        /** If the host name is www.pakjiddat.pk or dev.pakjiddat.pk */
        else if (($_SERVER['HTTP_HOST'] == "islamcompanion.pakjiddat.pk" || 
            $_SERVER['HTTP_HOST'] == "dev.islamcompanion.pakjiddat.pk")
            && strpos($_SERVER['REQUEST_URI'], "/api/") !== false) {
        	$is_valid = true;
        }        

        return $is_valid;
    }
}
