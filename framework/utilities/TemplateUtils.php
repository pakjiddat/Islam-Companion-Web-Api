<?php

declare(strict_types=1);

namespace Framework\Utilities;

/**
 * This class provides functions for parsing template files
 *
 * @category   UtilityClass
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class TemplateUtils
{
    /** @var Template $instance The single static instance */
    protected static $instance;
    
    /**
     * Class constructor
     * Used to prevent creating an object of this class outside of the class using new operator
     *
     * Used to implement Singleton class
     */
    protected function __construct() 
    {
    }
    /**
     * Used to return a single instance of the class
     *
     * Checks if instance already exists. If it does not exist then it is created. The instance is returned
     *
     * @return TemplateUtils static::$instance name the instance of the correct child class is returned
     */
    public static function GetInstance() : TemplateUtils
    {
        if (static ::$instance == null) {
            static ::$instance = new static();
        }
        return static ::$instance;
    }
    /**
     * It reads a template file and extracts all the tags in the template file
     *
     * @param string $template_path absolute path to the template html file
     *
     * @return array $template_info
     *    contents => string the template file contents
     *    tag_list => array the list of tags in the template file
     */
    public function ExtractTemplateTags(string $template_path) : array
    {
        /** The file contents are read */
        $template_contents              = UtilitiesFramework::Factory("filemanager")->ReadLocalFile($template_path);
        
        /** All template tags of the form {} are extracted from the template file */
        preg_match_all("/\{([a-zA-Z0-9\-_]+)\}/iU", $template_contents, $matches);
        /** The list of extracted tags */
        $template_tag_list              = $matches[1];
        /** The list of extracted tags is sorted */
        sort($template_tag_list);
        /** The required template information */
        $template_information           = array("contents" => $template_contents, "tag_list" => $template_tag_list);
        
        return $template_information;
    }
    /**
     * Used to replace the given tag name with a tag array
     *
     * It replaces the tag name with an array
     * Each element of the array can be a simple value
     * Or it can be an array which will contain a template name and template value pair
     *
     * @param string $tag_name the name of the tag to replace
     * @param array $tag_values the array values that will replace the tag name
     *
     * @return string $replacement_value the value to replace the tag name
     */
    function ReplaceTagWithArray(string $tag_name, array $tag_values) : string
    {
    	/** The value to replace the tag name */
        $replacement_value            = "";
        /** Each tag value is checked */
        for ($count = 0; $count < count($tag_values); $count++) {
        	/** The tag value */
            $item_value               = $tag_values[$count];

            /** The template path */
            $template_path            = $item_value['template_path'];
            /** The template values */
            $template_values          = $item_value['template_values'];
            /** 
             * If the template values is not an array then it is placed in an array
             * The template will be replaced the same number of times as the length of the
             * $template_values array
             */
            if (!is_array($template_values)) 
                $template_values      = array($template_values);
            /** The template is rendered */
            $item_value               = $this->GenerateTemplateFile($template_path);
            /** The tag value is updated */
            $replacement_value        .= $item_value;
        }
        return $replacement_value;
    }
    /**
     * Used to render the given template file with the given parameters
     *
     * It reads the given template file from the given template category
     * It parses all the tags from the template file
     * It then replaces each tag with the correct value
     * From the $tag_replacement_arr parameter
     *
     * @param string $template_path absolute path to the template html file
     * @param array $tag_replacement_arr tag replacement values
     * @param callable $callback optional the callback function for fetching missing template parameters
     *
     * @return string $generated_file the contents of the template file with all the tags replaced
     * The template file is replaced x number of times where x is the length of $tag_replacement_arr
     */
    public function GenerateTemplateFile(
        string $template_path,
        array $tag_replacement_arr,
        ?callable $callback = null
    ) : string {
    
        /** The final tag replacement value */
        $generated_file                                 = "";
        /** The tags in the template file are extracted */
        $template_info                                  = $this->ExtractTemplateTags($template_path);
        /** If the tag replacement array is an associative array then it is added to an array */
        if (!isset($tag_replacement_arr[0])) 
            $tag_replacement_arr                        = array($tag_replacement_arr);

        /** A template may be rendered multiple times. e.g table rows or table column templates */
        for ($count1 = 0; $count1 < count($tag_replacement_arr); $count1++) {
            /** The template file contents */
            $temp_template_contents                     = $template_info['contents'];
            /** The tag replacements values */
            $tag_replacement                            = $tag_replacement_arr[$count1];
            /** For each extracted template tag the value for that tag is fetched */
            for ($count2 = 0; $count2 < count($template_info['tag_list']); $count2++) {
                /** First the tag value is checked in the tag replacement array */
                $tag_name                               = $template_info['tag_list'][$count2];
                /** If the tag name contains a space, then it is assumed to contain user content, so the loop continues */
                if (strpos($tag_name, " ") !== false) continue;
                
                /** If the tag replacement value exists */
                if (array_key_exists($tag_name, $tag_replacement)) {
                    /** The array value is fetched */
                    $tag_value                          = $tag_replacement[$tag_name];
                    /** If the tag value is not an array */
                    if (!is_array($tag_value)) {
                        /** The tag value is converted to string */
                        $tag_value                      = (string) $tag_value;
                    }
                }
                else {
                    $tag_value                          = "!NOTSET!";
                }
                
                /** If the tag value is an array */
                if (is_array($tag_value)) {
                    /** The array values are resolved to a string */
                    $tag_value                          = $this->ReplaceTagWithArray($tag_name, $tag_value);
                }
                /** If the tag values were not found */
                else if ($tag_value == '!NOTSET!') {
                    /** If the callback was given */
                    if (is_callable($callback)) {
                        /** The parameter callback function is called */
                        $tag_value = call_user_func_array(
                                         $callback,
                                         array($tag_name)
                                     );
                    }
                    /** If the tag value is not found then an exception is thrown */
                    else {
                        /** The error message */
                        $msg  = "Tag replacement value was not given for the tag: " . $tag_name;
                        $msg .= " in the file: " . $template_path;
                        /** The exception is thrown */
                        throw new \Error($msg);
                    }
                }
                /** The tag name is replaced with the tag value */
                $temp_template_contents = str_replace("{" . $tag_name . "}", $tag_value, $temp_template_contents);              
            }
           /** The final template string is updated with the contents of the template */
           $generated_file .= $temp_template_contents;
        }
                
        return $generated_file;
    }
}
