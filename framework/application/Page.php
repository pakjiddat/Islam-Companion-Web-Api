<?php

declare(strict_types=1);

namespace Framework\Application;

use \Framework\Application\Web as WebApplication;
use \Framework\Config\Config as Config;

/**
 * This class provides the base class for developing browser based applications
 *
 * It extends the WebApplication class 
 * It adds functions that allow parsing template files
 *
 * @category   Application
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
abstract class Page extends WebApplication
{    
    /**
     * It generates the html for the home page
     *
     * @return string $page_html the html for the page
     */
    public function Generate() : string
    {        
		/** The header contents are fetched */
		$header_html = parent::GetHeader();
		/** The footer contents are fetched */
		$footer_html = parent::GetFooter();		
		/** The contents for the scripts is fetched */
		$script_tags = parent::GetScripts();
		/** The html for the page body */
        $body_html   = Config::GetComponent("templateengine")->Generate("body", array());
        /** The template parameters for the table template */
        $tag_values  = array(
                           "title" => $script_tags['title'],
						   "css_tags" => $script_tags['css'],
						   "js_tags" => $script_tags['js'],
						   "font_tags" => $script_tags['fonts'],
						   "body" => $body_html,
						   "header" => $header_html,
						   "footer" => $footer_html
					   );
							    
   		/** The html for the page */
        $page_html   = Config::GetComponent("templateengine")->Generate("page", $tag_values);
		
		return $page_html;
    }
    /**
     * Used to return the page title
     * 
     * @return string $title the page title
     */
    protected function GetTitle() : string
    {
        /** The page title */
        $title                      = "";
        
        return $title;
    }
   
    /**
     * Used to get the absolute urls to the javascript, css and font files
     * The files are defined in application config
     *
     * @return array $data the required absolute urls
     */
    private function GetFileUrls() : array
    {
        /** The custom css files */
        $custom_css               = Config::$config["general"]["custom_css_files"];
        /** The custom javascript files */
        $custom_javascript        = Config::$config["general"]["custom_js_files"];
        /** The custom font files */
        $custom_fonts             = Config::$config["general"]["custom_font_files"];

        /** The library folder url */
        $fw_vendors_url           = Config::$config["path"]["fw_vendors_url"];
        /** The ui framework url */
        $fw_ui_url                = Config::$config["path"]["fw_ui_url"];
        /** The application ui folder url */
        $app_ui_url               = Config::$config["path"]["app_ui_url"];
        /** The custom css and javascript files */
        $file_list                = array(
                                        "css_files" => $custom_css,
                                        "javascript_files" => $custom_javascript,
                                        "font_files" => $custom_fonts
                                    );
        /** All custom files are converted to absolute urls */
        foreach ($file_list as $type => $file_names) {
            /** The application folder path is appended to each css and javascript file */
            for ($count = 0; $count < count($file_names); $count++) {
                /**  If the file name is not an absolute url */
                if (strpos($file_names[$count]["url"], "http://") === false &&
                    strpos($file_names[$count]["url"], "https://") === false
                ) {
                    /** If the url points to ui folder */
                    if (strpos($file_names[$count]["url"], "{app_ui}") !== false) {
                        /** The base url is set to user interface folder url */
                        $base_url        =  $app_ui_url;
                    }
                    /** If the url points to framework ui folder */
                    else if (strpos($file_names[$count]["url"], "{fw_ui}") !== false) {
                        /** The base url is set to user interface folder url */
                        $base_url        =  $fw_ui_url;
                    }
                    /** If the url does not point to ui folder */
                    else if (strpos($file_names[$count]["url"], "{fw_vendors}") !== false) {
                        /** The base url is set to framework library folder url */
                        $base_url        =  $fw_vendors_url;
                    }
                    
                    /** The prefix are removed from the custom url */
                    $file_names[$count]["url"] = str_replace("{fw_ui}", "", $file_names[$count]["url"]);
                    /** The prefix are removed from the custom url */
                    $file_names[$count]["url"] = str_replace("{app_ui}", "", $file_names[$count]["url"]);
                    /** The prefix are removed from the custom url */
                    $file_names[$count]["url"] = str_replace("{fw_vendors}", "", $file_names[$count]["url"]);
                    
                    /** The base url is appended to the custom file name */
                    $file_names[$count]["url"] = $base_url . $file_names[$count]["url"];
                }
            }
            /** The file paths are updated */
            $file_list[$type]                  = $file_names;
        }

        /** The files are placed in an array */
        $data                                  = array(
                                                     "javascript" => $file_list['javascript_files'],
                                                     "css" => $file_list['css_files'],
                                                     "fonts" => $file_list['font_files']
                                                 );

        return $data;
    }
    
    /**
     * Used to return the list of css, javascript and font tags
     * 
     * @return array $header_tags the list of css, javascript and font tags
     */
    protected function GetHeaderTags() : array
    {       
        /** The javascript, css and font tags are generated */
        $urls         = $this->GetFileUrls();
        /** The css, javascript and font tags */
        $header_tags  = Config::GetComponent("widgetmanager")->Generate("headertags", $urls);
		/** The css and javascript tags are json decoded */
        $header_tags  = json_decode($header_tags, true);
        
        return $header_tags;
    }

    /**
     * It provides the html for the page body
     *
     * @param array $params the parameters for generating the body
     *
     * @return string $body_html the html for the body
     */
    protected function GetBody(?array $params = null) : string
    {
    	$body_html = "";
    	
    	return $body_html;
    }
    
    /**
     * It provides the html for the page header
     *
     * @param array $params the parameters for generating the header
     *
     * @return string $header_html the html for the header
     */
    protected function GetHeader(?array $params = null) : string
    {
    	$header_html = "";
    	
    	return $header_html;
    }
    
    /**
     * It sets the custom JavaScript, CSS and Font files to application configuration depending on the current page
     */
    protected function UpdateScripts() : void
    {
        
    }
    
    /**
     * It provides the html for the page footer
     *
     * @param array $params optional the parameters for generating the footer
     *
     * @return string $footer_html the html for the page footer
     */
    protected function GetFooter(?array $params = null) : string
    {
    	$footer_html = "";
    	
    	return $footer_html;
    }
 
     /**
     * It provides the code for the page scripts
     *
     * @param array $params the parameters for generating the scripts
     *
     * @return array $tag_values the script code
     *    title => string the page title
     *    css => string the css tags
     *    js => string the js tags
     *    fonts => string the font tags
     */
    protected function GetScripts(?array $params = null) : array
    {
        /** The JavaScript, CSS and Font tags are set for the current page */
        $this->UpdateScripts();
        /** The JavaScript, CSS and Font tags are generated */
        $header_tags          = $this->GetHeaderTags();       
        /** The template parameters for the table template */
        $tag_values           = array(
                                    "title" => "",
								    "css" => $header_tags['css'],
									"js" => $header_tags['javascript'],
									"fonts" => $header_tags['fonts']
							    );

        return $tag_values;  
    }   
    /**
     * Used to handle requests for which no matching url routing rules were found
     * It returns http status code of 404
     * It displays a custom 404 error page
     * This function may be overriden by child classes
     */
    public function HandlePageNotFound() : void
    {
        /** The http status header is sent with code 404 */
        http_response_code(404);
        /** The page title */
        $title         = Config::$config["general"]["app_name"] . " - Page not found";
        /** The contents of 404 page is fetched */
        $page_contents = Config::GetComponent("widgetmanager")->Generate("404", array("title" => $title));
        /** The page contents are displayed */
        $this->DisplayOutput($page_contents);
        /** The script ends */
        die();
    }
    
    /**
     * It redirects the user by sending http location header
     *
     * @param string $url the redirect url
     * @throws \Error an exception is thrown if http headers were already sent
     */
    final public function Redirect(string $url) : void 
    {
        /** If the http headers were not sent, then the user is redirected to the given url */
        if (!headers_sent($filename, $linenum)) {
            header("Location: " . $url);
        }
        /** An exception is thrown if http headers were already sent */
        else {
            throw new \Error("Headers already sent in " . $filename . " on line " . $linenum . "\n");
        }
    }  
    
    /**
     * Used to initialize the application
     *
     * It reads the translation information in config file
     * It generates parameters for the application from the url and from the data submitted by the user
     * It generates url routing information that determines which method should handle the current request
     * It optionally enables Php sessions and redirects the user to the login page if the user is not already logged in
     *
     * @param array $parameters the application parameters     
     */
    public function InitializeApplication($parameters) : void 
    {              
        /** If the user has enabled text translation */
        if (Config::$config["general"]["translate_text"]) {
            /** The translation text is read */
            Config::GetComponent("translation")->ReadTranslationText();
        }       
        
        /** If the user has created a Widgets.txt file */
        if (file_exists(Config::$config["path"]["widget_config_file"])) {
            /** The widget configuration is read */
            Config::GetComponent("widgetmanager")->ReadWidgetConfig();
            /** The url routing is updated in app config */
            Config::GetComponent("widgetmanager")->UpdateUrlRouting();
        }
        
        /** The application parameters are generated */
        $this->GenerateParameters();
		/** The url routing information is generated */
        Config::GetComponent("urlrouting")->GetCallback();
        
        /** If the sessions have been enabled in application config and the script is not being run from the command line */
        if (Config::$config["general"]["enable_sessions"] && php_sapi_name() != "cli") {
            /** Php sessions are enabled */
            Config::GetComponent("sessionhandling")->EnableSessions();
        }
        
        /** If session authentication has been enabled */
        if (Config::$config["general"]["enable_session_auth"]) {
            /** The user is redirected if not logged in */
            Config::GetComponent("sessionhandling")->RedirectIfNotLoggedIn();
        }
    }
}
