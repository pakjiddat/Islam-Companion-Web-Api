<?php

declare(strict_types=1);

namespace IslamCompanionApi\Config;

/**
 * This class provides required objects application configuration
 *
 * @category   Config
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class RequiredObjects
{
    /**
     * Used to return the configuration
     *
     * It returns an array containing requiredobjects configuration data
     *
     * @param array $parameters the application parameters
     *
     * @return array $config the custom configuration
     */
    public function GetConfig(array $parameters) : array
    {
      	/** The required application configuration */
    	$config                                       = array();
    	/** If the application is in development mode */
        if ($parameters['dev_mode']) {
            /** The database name */
            $db                                       = "dev-db-name";
            /** The dsn value */
            $dsn                                      = "mysql:host=localhost;dbname=" . $db . ";charset=utf8";
        	/** The database parameters */
        	$dbparams                                 = array(
		                                                    "dsn" => $dsn,
				                                            "user_name" => "user-name",
				                                            "password" => "password",
				                                            "use_cache" => false,
				                                            "debug" => 2,
				                                            "app_name" => "Islam Companion Api"
	                                                    );
            /** The framework database parameters */
            $fwdbparams                               = $dbparams;
        }
        /** If the application is in production mode */
        else {
            /** The dsn value */
            $dsn                                      = "mysql:host=localhost;dbname=prod-db-name;charset=utf8";
            /** The database parameters */
            $dbparams                                 = array(
                                                            "dsn" => $dsn,
				                                            "user_name" => "user-name",
				                                            "password" => "password",
				                                            "debug" => 2,
				                                            "use_cache" => false,
				                                            "app_name" => "Islam Companion Api"
                                                        );
            /** The framework database parameters */
            $fwdbparams                               = $dbparams;
        }
        
     	/** The application objects */
        $config['holyquranapi']['class_name']         = '\IslamCompanionApi\Lib\HolyQuranApi';
        $config['hadithapi']['class_name']            = '\IslamCompanionApi\Lib\HadithApi';
        $config['application']['class_name']          = '\IslamCompanionApi\Lib\IslamCompanionApi';
        $config['cliapplication']['class_name']       = '\IslamCompanionApi\Scripts\TestIslamCompanionApi';        

		/** The Holy Quran data objects */
        $config['rukus']['class_name']                = '\IslamCompanionApi\Lib\HolyQuran\Rukus';
        $config['ayas']['class_name']                 = '\IslamCompanionApi\Lib\HolyQuran\Ayas';
        $config['suras']['class_name']                = '\IslamCompanionApi\Lib\HolyQuran\Suras';
        $config['holyquranmetadata']['class_name']    = '\IslamCompanionApi\Lib\HolyQuran\MetaData';
        $config['holyqurannavigator']['class_name']   = '\IslamCompanionApi\Lib\HolyQuran\Navigator';        
        $config['btnsel']['class_name']               = '\IslamCompanionApi\Lib\HolyQuran\Navigation\BtnSelection';
        $config['divnumsel']['class_name']            = '\IslamCompanionApi\Lib\HolyQuran\Navigation\DivNumSelection';
        $config['rukusel']['class_name']              = '\IslamCompanionApi\Lib\HolyQuran\Navigation\RukuSelection';
        $config['surasel']['class_name']              = '\IslamCompanionApi\Lib\HolyQuran\Navigation\SuraSelection';
        
       	/** The Hadith data objects */
		$config['hadithbooks']['class_name']          = '\IslamCompanionApi\Lib\Hadith\Books';
        $config['hadithmetadata']['class_name']       = '\IslamCompanionApi\Lib\Hadith\MetaData';
        $config['hadithtext']['class_name']           = '\IslamCompanionApi\Lib\Hadith\Text';
        $config['hadithnavigator']['class_name']      = '\IslamCompanionApi\Lib\Hadith\Navigator';    
		
        /** The framework database object parameters */
        $config['dbinit']['parameters']               = $dbparams;		
        /** The mysql database access class is specified with parameters for the pakjiddat_com database */
        $config['frameworkdbinit']['parameters']      = $fwdbparams;
        
        return $config;
    }

}
