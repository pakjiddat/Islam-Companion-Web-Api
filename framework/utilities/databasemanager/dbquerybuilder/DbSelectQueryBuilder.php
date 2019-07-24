<?php

declare(strict_types=1);

namespace Framework\Utilities\DatabaseManager\Classes;

use Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

/**
 * This class allows generating select sql query strings from query parameters
 * It generates query strings for prepared and normal queries
 *
 * @category   UtilityClass
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
final class DbSelectQueryBuilder
{
    /** The QueryBuilderUtils trait is used */
    use QueryBuilderUtils;
    
    /**
     * It builds the select query using the given parameters
     *
     * @param string $query_format [prepared,normal] the query format
     * @param array $field_list the field list
     * @param array $where_clause the where clause data
     * @param array $table_list the table list
     * @param array $extra_params the extra query parameters
     *    sort_by => string the sort by clause
     *    order_by => string the order by clause
     *    group_by => string the group by clause
     *    start => int the start value of the limit clause
     *    end => int the end value of the limit clause
     *
     * @return mixed $query the prepared query with parameters or the normal sql query
     */
    private function BuildSelectQuery(
        string $query_format,
        array $field_list,
        array $where_clause,
        array $table_list,
        array $extra_params
    ) {
        /** The where clause string */
        $where_clause    = $this->BuildWhereClause($query_format, $where_clause);
        /** The field list */
        $field_list      = implode(",", $field_list);
        /** The table list */
        $table_list      = implode(",", $table_list);

        /** The order by clause */
        $order_by        = "";
        /** The group by clause */
        $group_by        = "";
        /** The limit clause */
        $limit           = "";
        
        /** If the sort clause is given */
        if ($extra_params['sort_by'] != "") {
            /** The template parameters for rendering the where clause */
            $template_parameters  = array(
                                        "sort_field" => $extra_params['sort_by'],
                                        "direction" => $extra_params['order_by']
                                    );
            /** The path to the template file */
            $template_file        = $this->folder_path . DIRECTORY_SEPARATOR . "sort.html";
            /** The template file is rendered with given parameters */
            $order_by             = UtilitiesFramework::Factory("templateutils")->GenerateTemplateFile(
                                        $template_file,
                                        $template_parameters
                                    );
            /** The order by clause is trimmed */
            $order_by             = trim($order_by, "\n");
        }
        /** If the group by clause is given */
        if ($extra_params['group_by'] != "") {
            /** The template parameters for rendering the where clause */
            $template_parameters = array("field_name" => $extra_params['group_by']);
            /** The path to the template file */
            $template_file        = $this->folder_path . DIRECTORY_SEPARATOR . "group_by.html";
            /** The template file is rendered with given parameters */
            $group_by             = UtilitiesFramework::Factory("templateutils")->GenerateTemplateFile(
                                        $template_file,
                                        $template_parameters
                                    );
            /** The group by clause is trimmed */
            $group_by             = trim($group_by, "\n");
        }
        /** If the limit clause is given */
        if ($extra_params['start'] >=0 && $extra_params['end'] > 0) {
            /** The template parameters for rendering the where clause */
            $template_parameters = array("start" => $extra_params['start'], "end" => $extra_params['end']);
            /** The path to the template file */
            $template_file       = $this->folder_path . DIRECTORY_SEPARATOR . "limit.html";
            /** The template file is rendered with given parameters */
            $limit               = UtilitiesFramework::Factory("templateutils")->GenerateTemplateFile(
                                        $template_file,
                                        $template_parameters
                                   );
        }        
                                               
        /** The template parameters for rendering the where clause */
        $template_parameters   = array(
                                     "field_list" => $field_list,
                                     "table_list" => $table_list,
                                     "where_clause" => $where_clause,
                                     "group_by" => $group_by,
                                     "order_by" => $order_by,
                                     "limit" => $limit
                                 );
        /** The path to the select query template file */
        $template_file         = $this->folder_path . DIRECTORY_SEPARATOR . "select.html";
        /** The template file is rendered with given parameters */
        $query                 = UtilitiesFramework::Factory("templateutils")->GenerateTemplateFile(
                                    $template_file,
                                    $template_parameters
                                 );
        /** If the prepared query needs to be generated */
        if ($query_format == "prepared") {
            /** The sql string */
            $query           = array(
                                   "params" => $param_list,
                                   "sql" => $query
                               );
        }
        
        return $query;                        
    }
}

