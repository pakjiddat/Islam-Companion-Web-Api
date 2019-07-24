<?php

declare(strict_types=1);

namespace IslamCompanionApi\Lib\HolyQuran;

use \Framework\Config\Config as Config;

/**
 * This trait implements functions for fetching information about Holy Quran ayas
 *
 * It provides functions for retreiving Holy Quran ayas
 *
 * @category   Lib
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class Ayas
{
    /**
     * It fetches the list of ayas for the given sura, start aya and end aya
     *
     * @param int $sura the sura
     * @param int $start_ayat optional the start ayat. if it is less than or equal to 0 then it is not considered
     * @param int $end_ayat optional the end ayat. if it is less than or equal to 0 then it is not considered
     *
     * @return array $ayat_list the list of ayas in the given sura starting from start aya and ending at end aya
     *    sura => string the sura number
     *    ayat_id => int the ayat number
     *    sura_ayat_id => int the sura ayat number
     *    sura_name => string the sura name
     *    translated_text => stringthe ayat translation
     *    arabic_text => the ayat text in arabic
     */
    public function GetAyasInSura(int $sura, int $start_ayat, int $end_ayat) : array
    {
        /** The database object and table name are fetched */
        $dbinfo         = Config::GetComponent("holyquranapi")->GetDbInfo("quranic_text");
        /** The sura table name is fetched */
        $sura_tn        = $table_name = Config::$config['general']['mysql_table_names']['sura'];
        /** The ayat table name is fetched */
        $aya_tn         = Config::GetComponent("holyquranapi")->GetAyatTableName();
        /** The Arabic table name */
        $arabic_tn      = $dbinfo['table_name'];
        
        /** The list of fields to fetch */
        $field_list     = array(
                              "t1.sura",
                              "t1.id as ayat_id",
                              "t1.sura_ayat_id",
                              "t2.arabic_text",
                              "t3.tname as sura_name"
                          );

        /** If the translation table is same as original arabic table */
        if ($aya_tn == $dbinfo['table_name'])              
            /** The arabic_text field is appended */
            $field_list []= "t2.arabic_text as translated_text"; 
        /** If the translation table is not same as original arabic table */
        else if ($aya_tn != $dbinfo['table_name'])              
            /** The arabic_text field is appended */
            $field_list []= "t1.translated_text";
                                                  
        /** The field list is formatted */
        $field_list     = implode(",", $field_list);                          
        /** The SQL query for fetching the file name */
        $sql            = "SELECT " . $field_list . " FROM `" . $aya_tn . "` t1, `". $dbinfo['table_name'] . "` t2,";
        $sql           .= "`" . $sura_tn . "` t3 ";
        $sql           .= " WHERE t1.sura=? AND t1.sura_ayat_id >=? AND t1.sura_ayat_id <=? AND t1.id=t2.id";
        $sql           .= " AND t3.sindex=t1.sura";
        $sql           .= " ORDER BY t1.sura_ayat_id ASC";
        /** The query parameters */
        $query_params   = array($sura, $start_ayat, $end_ayat);
        /** All rows are fetched */
        $ayat_list      = $dbinfo['dbobj']->AllRows($sql, $query_params);

        return $ayat_list;
    }
    /**
     * It fetches meta data for a random ruku
     *
     * @return array $meta_data the ruku meta data
     *    sura => string the sura name
     *    sura_id => int [1-114] the sura number
     *    start_ayat => int [1-286] the start sura ayat
     *    end_ayat => int [1-286] the end sura ayat
     */
    public function GetRandomRuku() : array
    {
        /** The database object and table name are fetched */
        $dbinfo         = Config::GetComponent("holyquranapi")->GetDbInfo("sura");
        /** The total number of suras */
        $sura_count     = Config::GetComponent("holyquranmetadata")->GetMaxDivisionCount("sura");
        /** A random sura number is generated */
        $sura_id        = rand(1, $sura_count);
                        
        /** The SQL query for fetching the file name */
        $sql             = "SELECT CONCAT(tname, ' (', ename, ')') as sura, sindex as sura_id, rukus FROM ";
        $sql            .= "`" .$dbinfo['table_name'] . "` WHERE sindex=?";
        /** The query parameters */
        $query_params   = array($sura_id);
        /** The first row is fetched */
        $sura_data      = $dbinfo['dbobj']->FirstRow($sql, $query_params);
        /** The total number of rukus in the sura */
        $rukus          = (int) $sura_data['rukus'];
        /** A random ruku number is generated */
        $sura_ruku      = rand(1, $rukus);

        /** The meta data table name is fetched */
        $table_name     = Config::$config['general']['mysql_table_names']["meta"];
        /** The SQL query for fetching the file name */
        $sql            = "SELECT MIN(sura_ayat_id) as start_ayat, MAX(sura_ayat_id) as end_ayat FROM ";
        $sql           .= "`" . $table_name . "` WHERE sura=? AND sura_ruku=? ORDER BY id ASC";
        /** The query parameters */
        $query_params   = array($sura_id, $sura_ruku);
        /** The first row is fetched */
        $meta_data      = $dbinfo['dbobj']->FirstRow($sql, $query_params);
        
        /** The sura data is merged with the meta data */
        $meta_data      = array_merge($meta_data, $sura_data);
               
        return $meta_data;
    }
    /**
     * It searches the ayat table for the given text
     *
     * @param string $narrator custom the translator name
     * @param string $search_text the search text
     * @param string $is_random list [yes,no] indicates if random search results should be fetched
     * @param int $page_number range [1-20] the search results page number
     * @param int $results_per_page range [1-20] the number of results per page     
     *
     * @return array $data contains the search results and total result count
     *    search_results => array the search results that contain the given text
     *        translation => string the translated text
     *        meta_data => array the ruku meta data
     *            sura => string the sura name
     *            sura_id => int range [1-114] the sura id
     *            ayat_id => int range [1-286] the ayat number
     *    result_count => int the total number of results
     */
    public function SearchAyat(
        string $search_text,
        string $is_random,
        int $page_number,
        int $results_per_page
    ) : array {

        /** The database object and table name are fetched */
        $dbinfo         = Config::GetComponent("holyquranapi")->GetDbInfo("ayas");
        /** The meta data table name is fetched */
        $table_name     = Config::$config['general']['mysql_table_names']["meta"];                        
        /** The SQL query for fetching the matching ayas */
        $sql             = "SELECT translated_text, sura, sura_ayat_id FROM ";
        $sql            .= "`" .$dbinfo['table_name'] . "` WHERE translated_text LIKE ?";
        /** If the search results should be randomized */
        if ($is_random == "yes")
            /** The sql query is updated */
            $sql.= " ORDER BY RAND()";

        /** The offset value is calculated */
        $offset          = ($page_number - 1) * $results_per_page;
        /** The limit values are added */
        $sql            .= " LIMIT " . $offset . ", " . $results_per_page;
                    
        /** The query parameters */
        $query_params   = array("%" . $search_text . "%");
        /** All rows are fetched */
        $rows           = $dbinfo['dbobj']->AllRows($sql, $query_params);
        /** The required ayat data */
        $ayat_data      = array();
        /** Each row is checked */
        for ($count = 0; is_array($rows) && $count < count($rows); $count++) {
            /** The translated text */
            $translated_text = $rows[$count]['translated_text'];
            /** The sura ayat id */
            $ayat_id         = $rows[$count]['sura_ayat_id'];
            /** The sura id */
            $sura_id         = (int) $rows[$count]['sura'];
            /** The sura details are fetched */
            $sura_details    = Config::GetComponent("suras")->GetSuraDetails($sura_id);
            /** The ayat meta data */
            $meta_data       = array(
                                   "sura" => $sura_details['tname'],
                                   "sura_id" => $sura_details['sindex'],
                                   "ayat_id" => $ayat_id
                               );
            $ayat_data[]     = array("translation" => $translated_text, "meta_data" => $meta_data);
        }
        
        /** The SQL query for fetching the total number of matching ayas */
        $sql             = "SELECT count(*) as total FROM ";
        $sql            .= "`" .$dbinfo['table_name'] . "` WHERE translated_text LIKE ?";                            
        /** The query parameters */
        $query_params   = array("%" . $search_text . "%");
        /** The first row is fetched */
        $row            = $dbinfo['dbobj']->FirstRow($sql, $query_params);
        /** The total row count */
        $row_count      = $row['total'];
        /** The required data */
        $data           = array("search_results" => $ayat_data, "result_count" => $row_count); 
        
        return $data;
    }
}
