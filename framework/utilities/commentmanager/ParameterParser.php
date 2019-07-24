<?php

declare(strict_types=1);

namespace Framework\Utilities\CommentManager;

/**
 * Provides functions for extracting method parameter information from Doc Block comments
 *
 * @category   Parser
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General private License, version 2
 */
final class ParameterParser
{	
    /**
     * Used to extract the parameters from the Doc Block comments
     * 
     * It uses regular expressions to extract param tags from Doc Block comments
	 * 	 
	 * @param string $tag_name the name of the tag that is to be extracted
     * @param string $comments the method comments string
	 * 
	 * @return array $parsed_parameters the parsed parameter tags
     */
    public function ExtractParameters(string $tag_name, string $comments) : array
    {
    	/** The parsed parameters */
    	$parsed_parameters                 = array();
    	
    	/** The supported variable types */
    	$types                             = "array|json|string|int|bool|object|mixed|callable";
    	/** The supported validation rules */
    	$rules                             = "list|range|custom|email";
		/** The param tags are extracted using regular expression */
		preg_match_all(
		    '/@' . $tag_name . '\s+(' . $types . ')\s([$a-z_0-9]+)\s(' . $rules . ')?\s?(\[.+\])?\s?(.+)/i',
		    $comments,
		    $matches
		);
		/** The details of each parameter is checked */
		for ($count = 0; $count < count($matches[0]); $count++) {
			/** The parameter string */
			$parameter_string              = $matches[0][$count];
			/** The parameter type */
			$parameters['type']            = $matches[1][$count];
			/** The variable name */
			$parameters['variable_name']   = str_replace("$", "", $matches[2][$count]);
			/** The parameter validation rule */
			$parameters['rule']            = $matches[3][$count];
			/** The parameter description */
			$parameters['description']     = $matches[5][$count];
			/** The rule validation data */
			$parameters['rule_data']       = str_replace(array("]", "["), "", $matches[4][$count]);
			/** If the parameter type is array or json */
			if ($parameters['type'] == 'array' || $parameters['type'] == 'json') {
			    /** The values of the array are fetched */
			    $array_values              = $this->ParseArrayParameterTags($parameter_string, $comments, 1); 
				/** If the array values are empty then the values are not set */
				if (count($array_values) > 0)
				    $parameters['values']  = $array_values;		
			}
			/** The parameters are added to the parsed parameters array */
			$parsed_parameters[]           = $parameters;
		}
	
		return $parsed_parameters;
    }
    
    /**
     * Used to parse the given array parameter line using regular expression
	 * 
     * @param string $line a line in the array parameter
	 * @param int $level the array nesting level
	 * @param string $comments the method Doc Block comments
	 * 
	 * @return array $parsed_line the parsed array parameter line
     */
    private function ParseArrayParameterLine(string $line, string $comments, int $level) : array
    {
        /** The parsed parameters */
		$parsed_line                      = array();
      	/** The supported variable types */
    	$types                            = "array|json|string|int|bool|object|mixed|callable";
    	/** The supported validation rules */
    	$rules                            = "list|range|custom|email";
		/** The param tags are extracted using regular expression */
		preg_match_all(
		    '/@\s{' . ($level * 4) . '}([^\s]+) => (' . $types . ')\s(' . $rules . ')?\s?(\[.+\])?\s?(.+)/i',
		    "@". $line,
		    $matches
	    );
		/** If the regular expression did not find any matches then empty array is returned */
		if (!isset($matches[0][0])) return $parsed_line;
		
		/** The parameter string for the sub array */
		$sub_array_param_text             = $matches[0][0];
		/** The variable name */
		$parsed_line['variable_name']     = $matches[1][0];
		/** The parameter type */
		$parsed_line['type']              = $matches[2][0];
		/** The parameter validation rule */
    	$parsed_line['rule']              = $matches[3][0];
		/** The rule validation data */
		$parsed_line['rule_data']         = str_replace(array("]", "["), "", $matches[4][0]);
		/** The parameter description */
		$parsed_line['description']       = $matches[5][0];
		/** If the parameter type is an array or json */
		if ($parsed_line['type'] == 'array' || $parsed_line['type'] == 'json') {
			/** The values of the array are fetched */
		    $array_values                 = $this->ParseArrayParameterTags(
		                                        $sub_array_param_text,
		                                        $comments,
		                                        ($level+1)
		                                    );
		    /** If the array values are empty then the values are not set */
			if (count($array_values) > 0)
			    $parsed_line['values']    = $array_values;				   				
	    }
	    
	    return $parsed_line;
    }
    
	/**
     * Used to extract the array values for the given array parameter
     * 
     * It uses regular expressions to extract the information about an array's elements
	 * 
     * @param string $array_param_text the parameter string for the array
	 * @param string $comments the parameter's Doc Block comment string
	 * @param int $level the array nesting level
	 * 
	 * @return array $parsed_array_values the parsed array values	 
     */
    private function ParseArrayParameterTags(string $array_param_text, string $comments, int $level) : array
    {
    	/** The array values */
    	$parsed_array_values                 = array();
		/** The comments are split on '*' */
		$line_arr                            = explode("*", $comments);
		/** The start line number */
		$start_index                         = -1;
		/** Each line is checked */
		for ($count = 0; $count < count($line_arr); $count++) {
			/** '/', '\r', '\n' are trimmed from the line */
			$line_arr[$count]                = trim($line_arr[$count], "/\r\n");
			/** If the line is equal to the array parameter text line */
			if (trim($line_arr[$count]) == trim($array_param_text)) {
			    /** The start index is set */
			    $start_index                 = $count;
			    /** The loop continues */
				continue;
			}			
			/** If the line is empty and start index has been set */
			else if (
			    (trim($line_arr[$count]) == "" || 
			    strpos(trim($line_arr[$count]), "@") !== false) && 
			    $start_index != -1
			) {
			    /** All array parameters have been parsed and the loop ends */
			    break;
			}
			
    		/** If the array parameter line has not been found, then the loop continues */
			if ($start_index == -1) continue;

			/** The array parameter line is parsed */
			$parsed_line                     = $this->ParseArrayParameterLine($line_arr[$count], $comments, $level);

			/** If the line was parsed */
			if (isset($parsed_line["variable_name"])) {
				/** The parsed array values */
				$parsed_array_values[]       = $parsed_line;			
			}
			/** If the line was not parsed, then the loop ends */
			else break;
		}
	    return $parsed_array_values;
    }
}

