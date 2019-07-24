<?php

declare(strict_types=1);

namespace IslamCompanionApi\Lib\HolyQuran\Navigation;

use \Framework\Config\Config as Config;

/**
 * This class provides navigator configuration information for selecting an item from the sura dropdown
 *
 * @category   Lib
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
class SuraSelection
{
    /**
     * It fetches the sura ruku number and start ayat when the given sura is selected 
     * For the given division and division number
     *
     * @param int $sura the sura id
     * @param int $div_num the division number
     * @param string $division the division     
     *
     * @return array $ayat_data the ayat data
     *    sura => int [1-114] the sura id
     *    sura_ruku => int [1-40] the sura ruku
     *    start_ayat => int [1-286] the start ayat number of the sura ruku
     *    end_ayat => int [1-286] the end ayat number of the sura ruku
     *    div_num => int [1-604] the division number
     */
    public function GetConfig(int $sura, int $div_num, string $division) : array
    {   
        /** If the division is not ruku */
        if ($division != "ruku") {
            /** The extra where clause */
        	$extra         = "AND " . $division . "=?";
            /** The SQL query parameters */
            $query_params  = array($sura, $div_num);
        }
        else {
            /** The extra where clause is set to empty */
        	$extra         = "";
            /** The SQL query parameters */
            $query_params  = array($sura);
        }
                
        /** The database object and table name are fetched */
        $dbinfo        = Config::GetComponent("holyquranapi")->GetDbInfo("meta");
        

        /** The SQL query */
        $sql           = "SELECT sura, sura_ruku, " . $division . " as div_num FROM ";
        $sql          .= " `" . $dbinfo['table_name'] . "` WHERE sura=? " . $extra;
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
