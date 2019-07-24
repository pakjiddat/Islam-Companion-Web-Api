<?php

declare(strict_types=1);

namespace Framework\Utilities\DatabaseManager\Classes;

use Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

/**
 * This class allows generating delete sql query strings from query parameters
 * It generates query strings for prepared and normal queries
 *
 * @category   UtilityClass
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
final class DbDeleteQueryBuilder
{
    /** The QueryBuilderUtils trait is used */
    use QueryBuilderUtils;

    /**
     * Used to get the delete query
     *
     * @param string $query_format [prepared,normal] the query format
     * @param array $where_clause the data for the where clause
     *    field_name => string the field name
     *    field_value => string the field value
     *    table_name => string the table name
     *    operation => string [AND,OR,NOT] the operation
     *    operator => string [<,=,>,>=,>=] the operator
     * @param string $table_name the table name
     *
     * @return mixed $query the prepared query with parameters or the normal sql query 
     */
    private function BuildDeleteQuery(string $query_format, array $where_clause, string $table_name)
    {
        /** The where clause */
        $where_clause          = $this->BuildWhereClause($where_clause);
        /** The template parameters for the delete statement */
        $template_parameters   = array(
                                     "table_name" => $table_name,
                                     "where_clause" => $where_clause['sql']
                                 );
        /** The path to the select query template file */
        $template_file         = $this->folder_path . DIRECTORY_SEPARATOR . "delete.html";
        /** The template file is rendered with given parameters */
        $query                 = UtilitiesFramework::Factory("templateutils")->GenerateTemplateFile(
                                     $template_file,
                                     $template_parameters
                                 );
                                             
        /** If the prepared query needs to be generated */
        if ($query_format == "prepared") {
            /** The sql string */
            $query           = array(
                                   "params" => $where_clause['value_list'],
                                   "sql" => $query
                               );
        }
        
        return $query;
    }
}

