<?php

declare(strict_types=1);

namespace Framework\Ui\Widgets\Bootstrap\Four0Four;

use \Framework\Config\Config as Config;

/**
 * Provides functions for generating 404 error page
 *
 * @category   Widgets
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
class Widget
{
  	/**
     * It generates 404 error page
     *
     * @param array $parameters the parameters used to generate the 404 page
     *
     * @return string $widget_html the html for the 404 page
     */
    public function Generate(array $parameters) : string
    {
        /** The parameters for generating the 404 page */
        $params      = array("title" => "Page not found !");
    	/** The 404 template is returned */
  	    $widget_html = Config::GetComponent("templateengine")->Generate("404", $params);
        
        return $widget_html;
    }
}
