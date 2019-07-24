<?php

declare(strict_types=1);

namespace Framework\Utilities\CommentManager;

/**
 * Provides functions for extracting method Doc Block comments
 *
 * @category   UtilityClass
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General private License, version 2
 */
class Parser
{
    /**
     * Used to parse the Doc Block comments of a method
     * 
     * It first extracts the Doc Block comments of the given object's method
	 * It then parses the comments into an array containing the sections inside the comments
	 * 
     * @param string $class_name the class name
	 * @param string $function_name the function name
	 * 
	 * @return array $parsed_comments the parsed doc block comments
     */
    public function ParseMethodDocBlockComments(string $class_name, string $function_name) : array
    {
    	/** The parsed doc block comments */
    	$parsed_comments                       = array();
    	
    	/** The DescriptionParser class object is created */
    	$description_parser                    = new DescriptionParser();
    	/** The ParameterParser class object is created */
    	$parameter_parser                      = new ParameterParser();
    	    	
		/** The reflection object for the class name */
        $reflector                             = new \ReflectionClass($class_name);    
        /** The function doc block comments */
        $comments                              = $reflector->getMethod($function_name)->getDocComment();
	    /** The description text. It is extracted using regular expression */
	    $parsed_comments['description']        = $description_parser->ExtractDescriptionText($comments);
		/** The method version. It includes since and version tags. It is extracted using regular expression */
	    $parsed_comments['version']            = $description_parser->ExtractVersion($comments);
		/** The internal tags. They are extracted using regular expressions */
	    $parsed_comments['internal']           = $description_parser->ExtractInternal($comments);
		/** The method parameters */
	    $parsed_comments['parameters']         = $parameter_parser->ExtractParameters("param", $comments);
		/** The method return value */
	    $parsed_comments['return_value']       = $parameter_parser->ExtractParameters("return", $comments);
	    /** The return value is set */
		$parsed_comments['return_value']       = $parsed_comments['return_value'][0] ?? '';
		/** The function name is set */
		$parsed_comments['function_name']      = $function_name;
		
		return $parsed_comments;
    }	
}
