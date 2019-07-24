<?php

declare(strict_types=1);

namespace Framework\Ui\Widgets\W3css\ScrollTop;

use \Framework\Config\Config as Config;

/**
 * Provides function for generating scroll widget
 *
 * @category   Widgets
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
class Widget
{
  	/**
     * It generates a scroll widget
     *
     * @param array $parameters the parameters used to generate the widget
     *
     * @return string $widget_html the html for the widget
     */
    public function Generate(?array $parameters) : string
    {
        /** The custom css file */
        $custom_file     = array("url" => "{fw_ui}/scrolltop/css/scroll-top.css");
        /** The custom file is appended to the app config */
  	    Config::GetComponent("widgetmanager")->AppendFile($custom_file, "css", "w3css");
        /** The custom js file */
        $custom_file     = array("url" => "{fw_ui}/scrolltop/js/main.js", "type" => "module");
        /** The custom file is appended to the app config */
  	    Config::GetComponent("widgetmanager")->AppendFile($custom_file, "js", "w3css");
  	    
     	/** The parameters for generating the login widget */
        $params          = $parameters;
    	/** The widget html */
  	    $widget_html     = Config::GetComponent("templateengine")->Generate("scroll-top", $params, "w3css");

        return $widget_html;
    }
}
