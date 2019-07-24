<?php

declare(strict_types=1);

namespace Framework\Utilities\DatabaseManager\Classes;

use Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

/**
 * This class allows generating insert sql query strings from query parameters
 * It generates query strings for prepared and normal queries
 *
 * @category   UtilityClass
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
final class DbInsertQueryBuilder
{
    /** The QueryBuilderUtils trait is used */
    use QueryBuilderUtils;
    
    /**
     * It builds the insert query using the given parameters
     *
     * @param string $query_format [prepared,normal] the query format
     * @param array $query_data the data for the insert query in field => value format
     * @param array $table_name the table name
     *
     * @return mixed $query the prepared query with parameters or the normal sql query
     */
    private function BuildInsertQuery(string $query_format, array $query_data, string $table_name)
    {
        /** The field list */
        $field_list            = implode(",", array_keys($query_data));
        /** The value list */
        $value_list            = implode(",", array_values($query_data));
                
        /** If the normal query needs to be generated */
        if ($query_format == "normal") {
            /** The value list */
            $param_list = implode(",", array_fill(0, count($value_list), "?"));
        }
        /** If the prepared query needs to be generated */
        else {
            /** The value list */
            $param_list = implode(",", $value_list);
        }            
        /** The template parameters for the normal query */
        $template_params = array(
                               "table_name" => $table_name,
                               "field_list" => $field_list,
                               "value_list" => $param_list
                           );        
        
        /** The path to the insert query template file */
        $template_file = $this->template_folder . DIRECTORY_SEPARATOR . "insert.html";
        /** The template file is rendered with given parameters */
        $query         = UtilitiesFramework::Factory("templateutils")->GenerateTemplateFile(
                             $template_file,
                             $template_parameters
                         );

        /** If the prepared query needs to be generated */
        if ($query_format == "prepared") {
            /** The parameters for the prepared query */
            $value_list      = array_values($query_data);
            /** The sql string */
            $query           = array(
                                   "params" => $value_list,
                                   "sql" => $query
                               );
        }
        
        return $query;
    }
}

