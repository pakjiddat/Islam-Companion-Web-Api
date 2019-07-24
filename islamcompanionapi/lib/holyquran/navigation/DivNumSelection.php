<?php

declare(strict_types=1);

namespace IslamCompanionApi\Lib\HolyQuran\Navigation;

use \Framework\Config\Config as Config;

/**
 * This class provides navigator configuration information for selection of division number dropdown
 *
 * @category   Lib
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
class DivNumSelection
{
    /**
     * It fetches the start ayat, sura ruku and sura when the given division number is selected
     *
     * @param int $div_num the division number
     * @param string $division the division
     *
     * @return array $ayat_data the ayat data
     *    sura => int [1-114] the sura number of the first sura in the division
     *    start_ayat => int [1-286] the first ayat that is in the given division number and sura
     *    sura_ruku => int [1-40] the first sura ruku in the given division number and sura
     *    div_num => int range [1-604] the division number
     */
    public function GetConfig(int $div_num, string $division) : array
    {
        /** The Database object is fetched */
        $database              = Config::GetComponent("dbinit")->GetDbManagerClassObj("Database");        
        /** The database table name */
        $table_name            = Config::$config['general']['mysql_table_names']['meta'];
        /** The sql query for fetching the data */
        $sql                   = "SELECT sura, sura_ruku FROM " . $table_name;
        $sql                   .= " WHERE " . $division . "=? ORDER BY sura_ayat_id ASC";
        /** The query parameters */
        $query_parameters      = array($div_num);
        /** The first row is fetched */
        $row                   = $database->FirstRow($sql, $query_parameters);
        
        /** The start and end ayas of the sura ruku */
        $ayat_data             = Config::GetComponent("rukus")->GetStartAndEndAyatOfRuku(
                                     (int) $row['sura'],
                                     (int) $row['sura_ruku']
                                 );
                                                   
        /** The start and end ayat numbers are merged with the ayat data */
        $ayat_data            = array_merge($row, $ayat_data);
        /** The division number is set */
        $ayat_data['div_num'] = $div_num;

        return $ayat_data;
    }
}
