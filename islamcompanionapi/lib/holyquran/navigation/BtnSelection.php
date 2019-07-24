<?php

declare(strict_types=1);

namespace IslamCompanionApi\Lib\HolyQuran\Navigation;

use \Framework\Config\Config as Config;

/**
 * This class provides navigator configuration information when the next, prev or random button is clicked
 *
 * @category   Lib
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
class BtnSelection
{
    /**
     * It fetches the division number, sura, ruku and ayat when the next, previous or random ruku is selected
     *
     * @param int $ruku the ruku id
     * @param string $division [hizb,juz,page,manzil,ruku] the division name
     * @param string $action [next,prev,random] the action
     *
     * @return array $meta_data the meta data containing ruku information
     *    div_num => int the next/previous/random division number
     *    sura => int [1-114] the next/previous/random sura
     *    sura_ruku => int [1-40] the next/previous/random sura ruku
     *    start_ayat => int [1-286] the next/previous/random ayat
     */
    public function GetConfig(int $ruku, string $division, string $action) : array
    {
        /** The database object and table name are fetched */
        $dbinfo           = Config::GetComponent("holyquranapi")->GetDbInfo("meta");
        
        /** The total number of rukus in Holy Quran */
        $total_rc         = Config::GetComponent("holyquranmetadata")->GetMaxDivisionCount("ruku");
        /** If the next button was clicked then ruku count is increased by 1 */
        if ($action == "next") {
            /** If the current ruku is equal to the last ruku then it is set to 1. Otherwise it is increased by 1 */
            if ($ruku == $total_rc)
                $ruku = 1;
            /** Otherwise the ruku count is set to the last ruku */
            else 
                $ruku = $ruku + 1;
        }
        /** If the previous button was clicked then ruku count is decreased by 1 */
        else if ($action == "prev") {
            /** If the current ruku is greater than 1 then ruku count is decreased by 1 */
            if ($ruku > 1) 
                $ruku = $ruku - 1;
            /** Otherwise the ruku count is set to the last ruku */
            else 
                $ruku = $total_rc;
        }

        /** The SQL query */
        $sql           = "SELECT " . $division . " as div_num, sura, sura_ruku, sura_ayat_id as start_ayat FROM ";
        $sql          .= "`" . $dbinfo['table_name'] . "`";
        /** If a random ruku is required */
        if ($action == "random") {
            /** The sql is updated */
            $sql          .= " ORDER BY RAND()";
            /** The query parameters */
            $query_params  = array();
        }                
        /** If next or previous ruku is required */
        else {
            /** The sql is updated */
            $sql           .= " WHERE ruku=? ORDER BY id ASC";
            /** The query parameters */
            $query_params  = array($ruku);            
        }
        
        /** The first row is fetched */
        $meta_data     = $dbinfo['dbobj']->FirstRow($sql, $query_params);

        return $meta_data;
    }
}
