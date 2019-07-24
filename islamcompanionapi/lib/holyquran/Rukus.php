<?php

declare(strict_types=1);

namespace IslamCompanionApi\Lib\HolyQuran;

use \Framework\Config\Config as Config;

/**
 * It provides functions for fetching information about Holy Quran Rukus
 * 
 * @category   Lib
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class Rukus
{
	/**
     * It fetches the list of rukus for the given sura and division number
	 * If the division is ruku then all rukus are fetched in the sura
	 * 
	 * @param int $sura the sura
	 * @param string $division the division name	
	 * @param int $div_num the division number		
	 * 
	 * @return array $ruku_data the list of rukus for given sura and division number
	 *    id => int [1-556] ruku id
	 *    sura_ruku => int [1-40] the sura ruku id
     */
    public function GetRukusInDivision(int $sura, string $division, int $div_num) : array
	{
        /** The database object and table name are fetched */
        $dbinfo                            = Config::GetComponent("holyquranapi")->GetDbInfo("meta");	
		/** If the division is not ruku then all rukus in given sura and division are fetched */
		if ($division != "ruku") {
            /** The SQL query */
            $sql                           = "SELECT DISTINCT(ruku) as id, sura_ruku FROM ";
            $sql                          .= "`" . $dbinfo['table_name'] . "`";
            $sql                          .= " WHERE sura=? AND " . $division . "=? ORDER BY sura_ruku ASC";
            /** The query parameters */
            $query_params                  = array($sura, $div_num);
		}
		/** If the division is ruku then all rukus in given sura are fetched */
		else {
		    /** The SQL query */
            $sql                           = "SELECT DISTINCT(ruku) as id, sura_ruku FROM ";
            $sql                          .= "`" . $dbinfo['table_name'] . "`";            
            $sql                          .= " WHERE sura=? ORDER BY sura_ruku ASC";
            /** The query parameters */
            $query_params                  = array($sura);        
		}
        
        /** All rows are fetched */
        $ruku_data                         = $dbinfo['dbobj']->AllRows($sql, $query_params, null);

		return $ruku_data;
	}
	
    /**
     * It fetches the start and end ayat values of the current ruku
     * 
     * @param int $sura the sura     
     * @param int $sura_ruku the sura ruku
     * 
     * @return array $ayat_data it contains the start and end ayat values
     *    start_ayat => int the start ruku ayat
     *    end_ayat => int the end ruku ayat
    */
    public function GetStartAndEndAyatOfRuku(int $sura, int $sura_ruku) : array
	{
        /** The database object and table name are fetched */
        $dbinfo                     = Config::GetComponent("holyquranapi")->GetDbInfo("meta");	

        /** The SQL query */
        $sql                        = "SELECT MIN(sura_ayat_id) as start_ayat, MAX(sura_ayat_id) as end_ayat FROM `";
        $sql                       .= $dbinfo['table_name'] . "` WHERE sura=? AND sura_ruku=? ORDER BY id ASC";
        /** The query parameters */
        $query_params               = array($sura, $sura_ruku);
        /** The first row is fetched */
        $ayat_data                  = $dbinfo['dbobj']->FirstRow($sql, $query_params, null);
    														
		return $ayat_data;
	}

	/**
     * It fetches the ruku id of the ruku for the given sura and sura ruku
	 * 
	 * @param int $sura the sura id
	 * @param int $sura_ruku_id the sura ruku id
	 * 
	 * @return int $ruku_id the ruku id
	 */
    public function GetRukuId(int $sura, int $sura_ruku_id) : int
	{
		/** The database object and table name are fetched */
        $dbinfo         = Config::GetComponent("holyquranapi")->GetDbInfo("meta");
        
        /** The SQL query */
        $sql          = "SELECT ruku FROM `" . $dbinfo['table_name'] . "` WHERE sura=? AND sura_ruku=? ORDER BY id ASC";
        /** The query parameters */
        $query_params = array($sura, $sura_ruku_id);
        /** The first row is fetched */
        $row          = $dbinfo['dbobj']->FirstRow($sql, $query_params, null);
        /** The ruku id */
        $ruku_id      = (int) $row['ruku'];
        
        return $ruku_id;
	}
}
