<?php

declare(strict_types=1);

namespace IslamCompanionApi\Config;

/**
 * This class provides test application configuration
 *
 * @category   Config
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class Test
{
    /**
     * Used to return the configuration
     *
     * It returns an array containing testing configuration data
     *
     * @param array $parameters the application parameters
     *
     * @return array $config the custom configuration
     */
    public function GetConfig(array $parameters) : array
    {
      	/** The required application configuration */
    	$config                               = array();
    	/** The test mode is set */
    	$config['test_mode']                  = false;
    	/** The test type is set */
       	$config['test_type']                  = "blackbox";

       	/** The parameters used for testing */
       	$config['language']                   = "Urdu";
       	$config['narrator']                   = "Abul A'ala Maududi";
       	$config['hadith_language']            = "Urdu";       	       	
       	
	    /** The function to unit test during black box testing. No other method is tested */
    	/**$config['only_test']                  = array(
    	                                              "object" => "hadithapi",
    	                                              "method" => "HandleGetHadithSources"
    	                                        );*/
    	                                        
        /** The files to include during testing */
		$config['include_files'] 	          = array("pear" => array("Mail/mime.php", "Mail.php"));
        /** The list of objects to test */    	        
		$config['testobjectlist']             = array(		                                          
		                                            "rukus",
		                                            "ayas",
		                                            "suras",
		                                            "holyquranmetadata",
		                                            "holyqurannavigator",
		                                            "hadithbooks",
  		                                            "hadithmetadata",
		                                            "hadithtext",
		                                            "hadithnavigator",
		                                            "holyquranapi",
		                                            "hadithapi"
		                                      );
    	        
        return $config;
    }
}
