<?php

declare(strict_types=1);

namespace IslamCompanionApi\Config;

/**
 * This class provides general application configuration
 *
 * @category   Config
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class General
{
    /**
     * Used to return the configuration
     *
     * It returns an array containing general configuration data
     *
     * @param array $parameters the application parameters
     *
     * @return array $config the custom configuration
     */
    public function GetConfig(array $parameters) : array
    {
      	/** The required application configuration */
    	$config                               = array();

        /** The name of the application */
        $config['app_name']                   = "Islam Companion Api";
        /** The development mode is set */
        $config['dev_mode']                   = true;
        /** The option for enabling cross domain ajax calls */
        $config['enable_cors']                = true;
        /** Indicates that user access should be logged */
        $config['log_user_access']            = true;

        /** The names of the MySQL database tables to be used by the application */
		$config['mysql_table_names'] 		  = array(
												    "sura" => "ic_quranic_suras_meta",
													"author" => "ic_quranic_author_meta",
													"quranic_text" => "ic_quranic_text-quran-simple",
													"ayas" => "ic_quranic_text",
													"meta" => "ic_quranic_meta_data",
													"hadith_urdu" => "ic_hadith_urdu",
													"hadith_books_urdu" => "ic_hadith_books_urdu",
													"hadith_english" => "ic_hadith_english",
													"hadith_books_english" => "ic_hadith_books_english",
													"hadith_arabic" => "ic_hadith_arabic",
													"hadith_books_arabic" => "ic_hadith_books_arabic"
											    );
        
        /** If the application is in development mode */
        if ($config['dev_mode']) {
            /** The site url for islam companion api */
            $config['site_url']               = "http://dev.islamcompanion.pakjiddat.pk/";            
        }
        /** If the application is in production mode */
        else {
            /** The site url for islam companion api */
            $config['site_url']               = "https://islamcompanion.pakjiddat.pk/";
        }
        
        return $config;
    }

}
