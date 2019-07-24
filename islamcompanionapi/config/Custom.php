<?php

declare(strict_types=1);

namespace IslamCompanionApi\Config;

/**
 * This class provides custom application configuration
 *
 * @category   Config
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class Custom
{
    /**
     * It returns an array containing custom configuration data
     *
     * @param array $parameters the application parameters
     *
     * @return array $config the custom configuration
     */
    public function GetConfig(array $parameters) : array
    {
      	/** The required application configuration */
    	$config                               = array();
    	
    	/** The list of supported hadith languages */
    	$config['hadith_languages']           = array("English", "Urdu", "Arabic");
        
        return $config;
    }

}
