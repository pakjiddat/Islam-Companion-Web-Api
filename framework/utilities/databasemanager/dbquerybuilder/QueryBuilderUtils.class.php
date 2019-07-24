<?php

declare(strict_types=1);

namespace Framework\Utilities\DatabaseManager\Classes;

use Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

/**
 * This class allows generating update sql query strings from query parameters
 * It generates query strings for prepared and normal queries
 *
 * @category   UtilityClass
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
trait QueryBuilderUtils
{
    /** @var array the path to the sql template folder */
    public $template_folder = "";
    
    /**
     * Class constructor
     * Sets the template folder path
     * 
     * @param string $template_folder the template folder path
     */
    public function __construct(string $template_folder) 
    {
        /** The template folder path is set */
        $this->template_folder    = realpath(dirname(__FILE__) . "sql");        
    }
    
    /**
     * Used to build the where clause
     *
     * @param string $query_format [prepared,normal] the query format
     * @param array $where_clause the data for the where clause
     *    field_name => string the field name
     *    field_value => string the field value
     *    operation => string [AND,OR,NOT] the operation
     *    operator => string [<,=,>,>=,>=] the operator
     *
     * @return array $where_data the required where data
     *    sql => string the where clause text
     *    value_list => array the list of values extracted from where clause
     */
    private function BuildWhereClause(string $query_format, array $where_clause) : array
    {
        /** The required where clause */
        $where_data        = array("sql" => "", "value_list" => array());
        /** If the where clause is given */
        if (count($where_clause) > 0) {
            /** The path to the select query template file */
            $template_file        = $this->folder_path . DIRECTORY_SEPARATOR . "where.html";
            /** Each where clause is rendered */
            for ($count = 0; $count < count($where_clause); $count++) {
                /** If the normal query is required */
                if ($query_format == "normal") {
                    /** If the field value is a string */
                    if (is_string($where_clause[$count]['value'])) {
                        /** Slashes are added to the field value */
                        $field_value = addslashes($where_clause[$count]['value']);
                    }
                    else {
                        /** The field value */
                        $field_value = $where_clause[$count]['value'];
                    }
                    /** The field value is enclosed in quotes */
                    $field_value = "'" . $field_value . "'";
                }
                /** If the prepared query is required */
                if ($query_format == "prepared") {
                     /** The field value is added to the value list */
                    $where_data['value_list'][] = $field_value;
                    /** The field value is set to "?" */
                    $field_value                = "?";
                }               
                
                /** The template parameters for rendering the where clause */
				$template_parameters  = array(
										    "field_name" => $where_clause[$count]['field'],
										    "operation" => $where_clause[$count]['operation'],
										    "field_value" => $field_value,
										    "operator" => $where_clause[$count]['operator']
									    );
									    

                /** The template file is rendered with given parameters */
                $query_part           = UtilitiesFramework::Factory("templateutils")->GenerateTemplateFile(
                                            $template_file,
                                            $template_parameters
                                        );
                /** The query is trimmed */
                $where_data["sql"]   .= trim($query_part, "\n");
            }
        }
        
        return $where_data;
    }
}

