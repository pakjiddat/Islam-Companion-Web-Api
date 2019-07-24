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
final class DbUpdateQueryBuilder
{
    /** The QueryBuilderUtils trait is used */
    use QueryBuilderUtils;
    
    /**
     * Used to get the update query
     *
     * @param string $query_format [prepared,normal] the query format
     * @param array $update_data the data to update. it should be in the field => value format
     * @param array $where_clause the where clause data
     * @param array $table_list the table list     
     *
     * @return mixed $query the prepared query with parameters or the normal sql query     
     */
    private function BuildUpdateQuery(string $query_format, array $update_data, array $where_clause, array $table_list)
    {
        /** The field list */
        $field_list      = implode(",", array_keys($update_data));
        /** The value list */
        $value_list      = implode(",", array_values($update_data));
        /** The placeholders for the table list */
        $table_list      = implode(",", $table_list);
        /** The sql query */
        $query           = "";
        /** The sql query part */
        $query_part      = array();
        /** The path to the select query template file */
        $template_file   = $this->folder_path . DIRECTORY_SEPARATOR . "update_list.html";
        /** Each where clause is rendered */
        for ($count = 0; $count < count($value_list); $count++) {
            /** The field value */
            $field_value     = $value_list[$count];
            /** If the prepared query is required */
            if ($query_format == "prepared") {
                $field_value = "?";
            }
            /** The template parameters for rendering the where clause */
            $template_parameters  = array(
                                        "field_name" => $field_list[$count],
                                        "field_value" => $field_value
                                    );                                    
            /** The template file is rendered with given parameters */
            $query_part           []= UtilitiesFramework::Factory("templateutils")->GenerateTemplateFile(
                                            $template_file,
                                            $template_parameters
                                     );
        }
        /** The where clause */
        $where_clause              = $this->BuildWhereClause($query_format, $where_clause);
        /** The update list is formatted */
        $update_list               = implode(",", $query_part);

        /** The template parameters for rendering the where clause */
        $template_parameters       = array(
		                                 "table_list" => $table_list,
		                                 "update_list" => $update_list,
		                                 "where_clause" => $where_clause['sql']
		                             );									    
        /** The path to the select query template file */
        $template_file             = $this->folder_path . DIRECTORY_SEPARATOR . "update.html";
        /** The template file is rendered with given parameters */
        $query                     = UtilitiesFramework::Factory("templateutils")->GenerateTemplateFile(
                                         $template_file,
                                         $template_parameters
                                     );
                                        		                             
        /** If the prepared query needs to be generated */
        if ($query_format == "prepared") {
            /** The list of values in update data */
            $value_list      = array_values($update_data)
            /** The parameters for the prepared sql query */
            $params          = array_merge($value_list, $where_clause['value_list']);
            /** The sql string */
            $query           = array(
                                   "params" => $params,
                                   "sql" => $query
                               );
        }
        
        return $query;
    }
}

