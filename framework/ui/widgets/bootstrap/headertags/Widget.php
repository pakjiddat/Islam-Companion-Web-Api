<?php

declare(strict_types=1);

namespace Framework\Ui\Widgets\W3css\HeaderTags;

use \Framework\Config\Config as Config;

/**
 * Provides functions for generating JavaScript, CSS and Font tags
 *
 * @category   Widgets
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
class Widget
{
  	/**
     * It generates JavaScript, CSS and Font tags
     *
     * @param array $parameters the parameters used to generate the tags
     *    javascript => array the javascript template parameters
     *    css => array the css template parameters
     *    fonts => array the font template parameters
     *
     * @return string $header_tags the list of CSS, JavaScript and Font tags in json encoded format
     */
    public function Generate(array $parameters) : string
    {
    	/** If the CSS template parameters are given */
    	if (count($parameters['css']) > 0) {
	        /** The CSS template file is rendered */
    	    $css_tags         = Config::GetComponent("templateengine")->Generate(
    	                            "css_tags.html",
    	                            $parameters['css']
    	                        );
		}
		/** If the CSS tags are not given */
		else $css_tags        = "";
		
		/** If the JavaScript template parameters are given */
    	if (count($parameters['javascript']) > 0) {    	    
        	/** The JavaScript template file is rendered */
	        $javascript_tags  = Config::GetComponent("templateengine")->Generate(
	                                "js_tags.html",
	                                $parameters['javascript']
	                            );
		}
		/** If the JavaScript template parameters are not given */
		else $javascript_tags = "";
		
		/** If the Fonts template parameters are given */
    	if (count($parameters['fonts']) > 0) {
        	/** The fonts template file is rendered */
	        $font_tags        = Config::GetComponent("templateengine")->Generate(
	                                "font_tags.html",
	                                $parameters['fonts']
	                            );
		}
		/** If the JavaScript template parameters are not given */
		else $font_tags       = "";
			        
        /** The required script tags */
        $header_tags          = array("javascript" => $javascript_tags, "css" => $css_tags, "fonts" => $font_tags);

        /** The header tags are json encoded */
        $header_tags          = json_encode($header_tags);
        
        return $header_tags;
    }
}
