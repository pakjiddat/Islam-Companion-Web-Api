<?php

declare(strict_types=1);

namespace Framework\Ui\Widgets\W3css\Login;

use \Framework\Config\Config as Config;

/**
 * Provides function for generating login widget
 *
 * @category   Widgets
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
class Widget
{
  	/**
     * It generates a login widget
     *
     * @param array $parameters the parameters used to generate the widget
     *
     * @return string $widget_html the html for the widget
     */
    public function Generate(array $parameters) : string
    {
        /** The custom js file */
        $custom_file     = array("url" => "{fw_ui}/login/js/main.js", "type" => "module");
        /** The custom file is appended to the app config */
  	    Config::GetComponent("widgetmanager")->AppendFile($custom_file, "js", "w3css");   	

        /** The alert box html is returned */
  	    $alert_html      = Config::GetComponent("widgetmanager")->Generate("alert", null, "w3css");
  	    
     	/** The parameters for generating the login widget */
        $params          = $parameters;
        /** The alert html is added to the login page */
        $params['alert'] = $alert_html;  	    
    	/** The widget html */
  	    $widget_html = Config::GetComponent("templateengine")->Generate("login", $params, "w3css");

        return $widget_html;
    }
}
