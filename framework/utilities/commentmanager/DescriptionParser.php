<?php

declare(strict_types=1);

namespace Framework\Utilities\CommentManager;

/**
 * Provides functions for extracting method description information from Doc Block comments
 *
 * @category   Parser
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General private License, version 2
 */
final class DescriptionParser
{	
    /**
     * Used to extract the short and long description text from Doc Block comments
	 * 
     * @param string $comments the method comments string	 
	 * 
	 * @return array $parsed_text the parsed method description
	 *    short => string the short description of the method
	 *    long => string the long description of the method	                       
     */
    public function ExtractDescriptionText(string $comments) : array
    {
    	/** The parsed description and context */
    	$parsed_text                    = array("short" => array(), "long" => array());
    	/** The start and end lines are removed */
    	$comments                       = str_replace("/**", "", $comments);
    	$comments                       = str_replace("*/", "", $comments);    	
    	$comments                       = trim($comments);    	    	
		/** The comments are split on '*' */
		$line_arr                       = explode("*", $comments);
		/** The type of comment to parse */
		$comment_type                   = "short";
		/** Each line is checked */
		for ($count = 1; $count < count($line_arr); $count++) {
			/** The comment line. Carriage return and line feed are removed from the line */
			$line_arr[$count]           = str_replace(array("\r", "\n"), "", trim($line_arr[$count]));
			/** If the line is empty */
			if ($line_arr[$count] == "") {
			    /** Comment type is set to long */
			    $comment_type           = "long";
			    /** The loop continues */
			    continue;
			}
			/** If the line contains the text "@param" or "@internal" then the loop ends */
			if (strpos($line_arr[$count], "@param") !== false || strpos($line_arr[$count], "@internal") !== false)
			    break;
			
		    /** The line is added to the description */
    		$parsed_text[$comment_type][] = $line_arr[$count];
		}

        /** The short description is formatted */
		$parsed_text['short']           = implode(". ", $parsed_text['short']);

		/** The long description is formatted */
	    $parsed_text['long']            = implode(". ", $parsed_text['long']);			

		return $parsed_text;
    }
    
    /**
     * Used to extract the version tags from the Doc Block comments
     * 
     * It uses regular expressions to extract version and since tags from Doc Block comments	 
	 * 
     * @param string $comments the method comments string	 
	 * 
	 * @return array $parsed_version the parsed version tags
	 *    since => string the since tag
	 *    version => string the version tag
     */
    public function ExtractVersion($comments) : array
    {
    	/** The parsed version */
    	$parsed_version                = array();
		/** The since tag is extracted using regular expression */
		preg_match_all("/@since\s+([\d\.]+)/i", $comments, $matches);
		/** The since version number */
		$parsed_version['since']       = $matches[1][0] ?? '';
		/** The since tag is extracted using regular expression */
		preg_match_all("/@version\s+([\d\.]+)/i", $comments, $matches);
		/** The version number */
		$parsed_version['version']     = $matches[1][0] ?? '';
		
		return $parsed_version;
    }
	
	/**
     * Used to extract the internal tags from the Doc Block comments
     * 
     * It uses regular expressions to extract internal tags from Doc Block comments
	 * 
     * @param string $comments the comments string for the method	 
	 * 
	 * @return array $internal_tag_list the parsed internal tags
     */
    public function ExtractInternal(string $comments) : array
    {
    	/** The list of internal tags */
    	$internal_tag_list                               = array();
		/** The internal tags are extracted using regular expression */
		preg_match_all("/\{@internal\s+([^\s]+)\s+(.+)\}/i", $comments, $matches);	
		/** All the internal tags are extracted */
		for ($count = 0 ; $count < count($matches[0]); $count++) {
		    /** The internal tag name */
		    $internal_tag_name                           = $matches[1][$count] ?? '';		    
		    /** The internal tag description */
		    $internal_tag_list[$internal_tag_name]       = $matches[2][$count] ?? '';
		}		
		
		
		return $internal_tag_list;
    }
}

