<?php

declare(strict_types=1);

namespace Framework\Ui\Widgets;

use \Framework\Config\Config as Config;
use \Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

/**
 * This class provides functions for generating widgets
 *
 * @category   Widgets
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class Manager
{				
    /**
     * Used to generate html for the required widget
     *
     * @param string $name the widget name. it may be the id of a widget given in Widgets.txt config file
     * @param array $parameters the widget parameters     
     * @param string $ui_framework the name of the user interface framework to use
     *
     * @return string $widget_html the html string for the widget
     */
    public function Generate(
        string $name,
        ?array $parameters = null,
        ?string $ui_framework = "w3css"
    ) : string {
    
        /** If the widget has been specified in Widgets.txt config file */
        if (isset(Config::$config['general']["widget_config"][$name])) {
            /** The widget parameters are set */
            $parameters  = Config::$config['general']["widget_config"][$name];
            /** The widget type */
            $name        = Config::$config['general']["widget_config"][$name]["type"];
        }
        /** The widget object name */
        $widget_obj_name = $ui_framework . "_" . $name;
        /** The widget is generated */
        $widget_html     = Config::GetComponent($widget_obj_name)->Generate($parameters);
        
        return $widget_html;
    }
    
    /**
     * Used to append a file such as js, css or font file to the custom file configuration
     *
     * @param array $file the custom file details
     * @param string $type [css,js,font] the type of file
     */
    public function AppendFile(array $file, string $type, string $ui_framework) : void
    {    
        /** The configuration name */
        $app_config  = "";
        /** If the file type is javascript */
        if ($type == "js") {
            /** The configuration name is set */
            $app_config = "custom_js_files";
        }
        /** If the file type is css */
        else if ($type == "css") {
            /** The configuration name is set */
            $app_config = "custom_css_files";
        }
        /** If the file type is font */
        if ($type == "font") {
            /** The configuration name is set */
            $app_config = "custom_font_files";
        }
        /** The ui framework url */
        $fw_ui_url   = Config::$config["path"]["fw_ui_url"];
        /** The user interface framework is added to the ui framework url */
        $fw_ui_url   = str_replace("{ui_framework}", $ui_framework, $fw_ui_url);
        /** The file url is updated */
        $file["url"] = str_replace("{fw_ui}", $fw_ui_url, $file["url"]);
        
        /** The list of custom files */
        $file_list   = Config::$config['general'][$app_config];
        /** The give file is appended to the file list */
        $file_list   = array_merge($file_list, array($file));
        /** The list of custom files is updated */
        Config::$config['general'][$app_config] = $file_list;
    }

    /**
     * Used to read the Widget config file
     * The contents of the file are copied to app config
     */
    public function ReadWidgetConfig() : void
    {
        /** The widget config file */
        $widget_config_file = Config::$config["path"]["widget_config_file"];
        /** The widget config file is read */
        $file_contents      = UtilitiesFramework::Factory("urlmanager")->GetFileContent($widget_config_file);
        /** The text is converted to array */
        $config_arr         = explode("\n", $file_contents);
        /** The widget configuration */
        $widget_config      = array();
        /** The widget id */
        $widget_id          = "";
        /** Each line in the config array is checked */
        for ($count = 0; $count < count($config_arr); $count++) {
            /** If the line is empty */
            if ($config_arr[$count] == "") continue;
            /** The widget config line is split into key value pairs */
            preg_match("/([a-z_]+): (.+)/i", $config_arr[$count], $matches);
            /** If the key is id */
            if ($matches[1] == "id") {
                /** The widget id is set */
                $widget_id                 = $matches[2];
                /** The widget config is initialized */
                $widget_config[$widget_id] = array();
            }
            /** If the widget is empty, then loop continues */
            if ($widget_id == "") continue;
            
            /** The widget config is updated */
            $widget_config[$widget_id][$matches[1]] = $matches[2];
        }
        /** The widget config is set to application config */
        Config::$config['general']["widget_config"] = $widget_config;
    }
    
    /**
     * Used to update the url routing information in application config
     * It calls the UpdateUrlRouting function of each widget defined in app config
     */
    public function UpdateUrlRouting() : void
    {
        /** The widget configuration */
        $widget_config = Config::$config['general']["widget_config"];
        /** Each widget configuration is checked */
        foreach ($widget_config as $widget_id => $config) {
            /** The widget object name */
            $widget_obj_name = $config["ui_framework"] . "_" . $config["type"];
            /** The url routing information is updated */
            Config::GetComponent($widget_obj_name)->UpdateUrlRouting();            
        }
    }
}
