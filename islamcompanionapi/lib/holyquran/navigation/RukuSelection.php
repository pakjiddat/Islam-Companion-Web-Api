<?php

declare(strict_types=1);

namespace IslamCompanionApi\Lib\HolyQuran\Navigation;

use \Framework\Config\Config as Config;

/**
 * This class implements the RukuSelection trait
 *
 * It provides navigator configuration information when a ruku is selected from the ruku dropdown
 *
 * @category   Lib
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
class RukuSelection
{
    /**
     * It fetches the start ayat when the given ruku is selected
     *
     * @param int $sura_ruku the sura ruku
     * @param int $sura the sura id
     * @param string $division the division     
     *
     * @return array $ayat_data the ayat data
     *    sura => int [1-114] the sura id
     *    sura_ruku => int [1-40] the sura ruku     
     *    start_ayat => int the ayat number
     */
    public function GetConfig(int $sura_ruku, int $sura, string $division) : array
    {
        /** The database object and table name are fetched */
        $dbinfo        = Config::GetComponent("holyquranapi")->GetDbInfo("meta");
        
        /** The SQL query parameters */
        $query_params  = array($sura, $sura_ruku);
        /** The SQL query */
        $sql           = "SELECT sura, sura_ruku, " . $division . " as div_num FROM";
        $sql          .= " `" . $dbinfo['table_name'] . "` WHERE sura=? AND sura_ruku=?";
        $sql          .= " ORDER BY sura_ayat_id ASC";
        /** The first row is fetched */
        $row           = $dbinfo['dbobj']->FirstRow($sql, $query_params);     
        
        /** The start and end ayas of the sura ruku */
        $ayat_data     = Config::GetComponent("rukus")->GetStartAndEndAyatOfRuku(
                                                       (int) $row['sura'],
                                                       (int) $row['sura_ruku']
                                                   );
                                                   
        /** The start and end ayat numbers are merged with the ayat data */
        $ayat_data    = array_merge($row, $ayat_data);
        
        return $ayat_data;
    }
}
