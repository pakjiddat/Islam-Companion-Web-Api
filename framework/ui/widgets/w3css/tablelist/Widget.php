<?php

declare(strict_types=1);

namespace Framework\Ui\Widgets\W3css\TableList;

use \Framework\Config\Config as Config;

/**
 * Provides function for generating table widget
 *
 * @category   Widgets
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
class Widget
{
  	/**
     * It generates a table widget
     *
     * @param array $parameters the parameters used to generate the widget
     *
     * @return string $widget_html the html for the widget
     */
    public function Generate(array $parameters) : string
    {
        /** The table list template parameters are generated */
        $params      = $this->GetParams($parameters);
        /** The widget html */
  	    $widget_html = Config::GetComponent("templateengine")->Generate("table_list", $params, "w3css");

        return $widget_html;
    }
    
    /**
     * It generates the header columns for the table list
     *
     * @param array $parameters the table list widget parameters
     *
     * @return string $header_html the generated header columns
     */
    private function GetHeaderCols(array $parameters) : string {
    
        /** The required header html */
        $header_html  = "";
        /** The header column details are json decoded */
        $header_cols  = json_decode($parameters['table_cols'], true);            
        /** Each header column is generated and appended to the header column string */
        for ($count = 0; $count < count($header_cols); $count++) {
            /** The parameters used to generate the header */
            $params       = array("text" => $header_cols[$count]['title'], "color" => $parameters['header_color']);
            /** The header html is generated */
            $header_html .= Config::GetComponent("templateengine")->Generate("header_col", $params, "w3css");
        }
        
        return $header_html;
    }
    
    /**
     * It generates the parameters for rendering the table list template
     *
     * @param array $parameters the table list widget parameters
     *
     * @return array $widget_params the parameters for table list template
     */
    private function GetParams(array $parameters) : array
    {
        /** The required widget parameters */
        $widget_params = array(
                             "id" => "",
                             "title" => "",
                             "header_columns" => "",
                             "data" => "",
                             "bottom_buttons" => ""
                         );
                         
        /** The widget id */
        $widget_params['id']             = $parameters['id'];
        /** The table list title */
        $widget_params['title']          = $parameters['title'];
        /** The header cols are generated */
        $widget_params['header_columns'] = $this->GetHeaderCols($parameters);
        /** The bottom buttons are generated */
        $widget_params['bottom_buttons'] = $this->GetBottomButtons($parameters);
        /** The data columns are generated */
        $widget_params['data']           = $this->GetDataHtml($parameters);
                                         
        return $widget_params;
    }
    
    /**
     * It adds the new item and all item urls to the url routing information
     */
    public function UpdateUrlRouting() : void
    {
        
    }
    
    /**
     * It generates the data columns
     *
     * @param array $parameters the table list widget parameters
     *
     * @return string $data_html the data html
     */
    private function GetDataHtml(array $parameters) : string
    {
        /** The data html for the table list */
        $data_html       = "";
        /** The table column details are json decoded */
        $col_details     = json_decode($parameters['table_cols'], true);                    
        /** The data is fetched from database */
        $rows            = $this->GetDataFromDb($parameters);        
        /** The row html is generated for each row */
        for ($count1 = 0; $count1 < count($rows); $count1++) {
            /** The table row */
            $row          = $rows[$count1];
            /** The col html for the row */
            $row_col_html = "";
            /** The column html is generated for each column */
            for ($count2 = 0; $count2 < count($col_details); $count2++) {
                /** The column template name */
                $template          = "data_col";
                /** The table col */
                $col = $col_details[$count2];
                /** If the col type is db */
                if ($col["type"] == "db") {
                    /** The parameters used to generate the col html */
                    $params        = array("content" => $row[$col["extra"]]);
                }
                /** If the col type is autogen */
                else if ($col["type"] == "autogen") {
                    /** The parameters used to generate the col html */
                    $params        = array("content" => ($count1+1));
                }
                /** If the col type is callback */
                else if ($col["type"] == "callback") {
                    /** The callback details are json decoded */
                    $callback      = json_decode(str_replace("'", '"', $col['extra']), true);
                    /** The callback obj */
                    $callback_obj  = Config::GetComponent($callback["object"]);
                    /** The callback params */
                    $callback_params = array($row[$callback['params']]);
                    /** The callback */
                    $callback      = array($callback_obj, $callback["function"]);
                    /** The callback is run */
                    $result        = call_user_func_array($callback, $callback_params);
                    /** The parameters used to generate the col html */
                    $params        = array("content" => $result);
                }
                /** If the col type is icon-col */
                else if ($col["type"] == "icon-col") {
                    /** The col extra details are json decoded */
                    $extra_details = json_decode(str_replace("'", '"', $col['extra']), true);
                    /** The link url */
                    $link_url      = str_replace("{id}", $row['id'], $extra_details['link']);
                    /** The parameters used to generate the icon col */
                    $params        = array(
                                         "link" => $link_url,
                                         "title" => $extra_details['title'],
                                         "onclick" => $extra_details['onclick'],
                                         "icon" => $extra_details['icon']
                                     );
                    /** The column template name */
                    $template      = "icon_col";                                        
                }
                /** If the col type is edit */
                else if ($col["type"] == "edit") {
                    /** The col extra details are json decoded */
                    $extra_details = json_decode(str_replace("'", '"', $col['extra']), true);
                    /** The link url */
                    $link_url      = str_replace("{id}", $row['id'], $extra_details['link']);
                    /** The parameters used to generate the icon col */
                    $params        = array(
                                         "edit_link" => $link_url,
                                         "title" => $extra_details['title'],
                                         "table_id" => $parameters['id']
                                     );
                    /** The column template name */
                    $template      = "edit_col";
                }
                /** If the col type is delete */
                else if ($col["type"] == "delete") {
                    /** The col extra details are json decoded */
                    $extra_details = json_decode(str_replace("'", '"', $col['extra']), true);
                    /** The link url */
                    $link_url      = str_replace("{id}", $row['id'], $extra_details['link']);
                    /** The parameters used to generate the icon col */
                    $params        = array(
                                         "delete_link" => $link_url,
                                         "title" => $extra_details['title'],
                                         "table_id" => $parameters['id']
                                     );
                    /** The column template name */
                    $template      = "delete_col";
                }
                /** The col html is generated */
                $row_col_html .= Config::GetComponent("templateengine")->Generate($template, $params, "w3css");
            }
            /** The parameters used to generate the row */
            $params    = array("data_columns" => $row_col_html);
            /** The data row is generated */
            $row_html  = Config::GetComponent("templateengine")->Generate("row", $params, "w3css");
            
            /** The data html is updated */
            $data_html.= $row_html;
        }
        
        return $data_html;
    }
    
    /**
     * It fetches the table list data from database
     *
     * @param array $parameters the table list widget parameters
     *
     * @return array $data the table list data
     */
    private function GetDataFromDb(array $parameters) : array
    {
        /** The dbinit object is fetched */
        $dbinit          = Config::GetComponent("dbinit");
        /** The Database class object is fetched */
        $database        = $dbinit->GetDbManagerClassObj("Database");
		/** The table name for website content */
        $table_name      = Config::$config['general']['mysql_table_names'][$parameters['database_table']];
        /** The SQL query for fetching the website content */
        $sql             = "SELECT * FROM `" . $table_name . "` " . $parameters['sql_query'];
        $sql             .= " LIMIT 0," . $parameters['row_count'];
        /** All rows are fetched */
        $data            = $database->AllRows($sql);
        
        return $data;
    }
    
    /**
     * It generates the bottom button colors
     *
     * @param array $parameters the table list widget parameters
     *
     * @return string $button_html the html for the bottom buttons
     */
    private function GetBottomButtons(array $parameters) : string
    {
        /** The required bottom button html */
        $button_html  = "";
        
        /** The parameters used to generate the bottom buttons */
        $params       = array(
                            "color" => $parameters['bottom_btn_color'],
                            "all_items_url" => $parameters['all_items_url'],
                            "new_item_url" => $parameters['new_item_url'],
                            "table_id" => $parameters['id'],
                            "all_items_text" => $parameters["all_items_text"],
                            "new_item_text" => $parameters["new_item_text"],
                        );
        /** The button html is generated */
        $button_html  = Config::GetComponent("templateengine")->Generate("bottom_buttons", $params, "w3css");

        return $button_html;
    }    
}
