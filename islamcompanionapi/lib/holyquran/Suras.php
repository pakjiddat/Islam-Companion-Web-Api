<?php

declare(strict_types=1);

namespace IslamCompanionApi\Lib\HolyQuran;

use \Framework\Config\Config as Config;

/**
 * It provides functions for retreiving sura data
 *
 * @category   Lib
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class Suras
{
    /**
     * It fetches list of all the suras
     *
     * @return array $sura_data the required sura data
     *    sindex => int the sura index
     *    tname => string the translated sura name
     *    ename => string the sura name in english
     */
    public function GetSuraData() : array
    {
        /** The database object and table name are fetched */
        $dbinfo       = Config::GetComponent("holyquranapi")->GetDbInfo("sura");
        
        /** The SQL query */
        $sql          = "SELECT sindex, tname, ename FROM `" . $dbinfo['table_name'] . "` ORDER BY id ASC";
        /** All rows are fetched */
        $sura_list    = $dbinfo['dbobj']->AllRows($sql, null, null);
        
        return $sura_list;
    }
    
    /**
     * It fetches list of all the suras in the given division
     *
     * @param string $division [juz,hizb,page,manzil] the name of the division
     * @param int $div_num [1-604] the division number
     *
     * @return array $sura_data the required sura data
     *    sindex => int the sura index
     *    tname => string the translated sura name
     *    ename => string the sura name in english
     */
    public function GetSuraDataForDivision(string $division, int $div_num) : array
    {
        /** The database object and table name are fetched */
        $dbinfo       = Config::GetComponent("holyquranapi")->GetDbInfo("meta");
        
        /** The SQL query */
        $sql          = "SELECT MIN(sura) as start_sura, MAX(sura) as end_sura FROM `" . $dbinfo['table_name'] . "`";
        $sql         .= " WHERE $division=?";
        /** The query parameters */
        $query_params = array($div_num);
        /** The first row is  fetched */
        $row          = $dbinfo['dbobj']->FirstRow($sql, $query_params, null);
        
        /** The start sura */
        $start_sura   = $row['start_sura'];
        /** The end sura */
        $end_sura     = $row['end_sura'];
 
        /** The database object and table name are fetched */
        $dbinfo       = Config::GetComponent("holyquranapi")->GetDbInfo("sura");
        
        /** The SQL query */
        $sql          = "SELECT sindex, tname, ename FROM `" . $dbinfo['table_name'] . "` WHERE sindex>=?";
        $sql         .= " AND sindex<=?";
        /** The query parameters */
        $query_params = array($start_sura, $end_sura);
        /** All rows are fetched */
        $sura_list    = $dbinfo['dbobj']->AllRows($sql, $query_params, null);
        
        return $sura_list;
    }
    
    /**
     * It fetches details for a sura
     *
     * @param int $sura [1-114] the sura id
     *
     * @return array $sura_data the required sura data
     */
    public function GetSuraDetails(int $sura) : array
    {
        /** The database object and table name are fetched */
        $dbinfo       = Config::GetComponent("holyquranapi")->GetDbInfo("sura");
        
        /** The SQL query */
        $sql          = "SELECT * FROM `" . $dbinfo['table_name'] . "` WHERE sindex=?";
        /** The query parameters */
        $query_params = array($sura);
        /** The first row is fetched */
        $sura_data    = $dbinfo['dbobj']->FirstRow($sql, $query_params);
        
        return $sura_data;
    }
}
