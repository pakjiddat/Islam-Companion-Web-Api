<?php

declare(strict_types=1);

namespace IslamCompanionApi\Lib\HolyQuran;

use \Framework\Config\Config as Config;

/**
 * This class provides functions for managing HolyQuran Navigator
 *
 * @category   Lib
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class Navigator
{
    /**
     * It generates configuration data for the navigator after an action such as sura box select has occurred
     *
     * @param string $action list [sura_box,ruku_box,div_num_box,next,prev,random] the action taken by the user
     * @param int $sura range [1-114] the current sura
     * @param int $sura_ruku range [1-40] the current sura ruku
     * @param string $division list [hizb,juz,page,manzil,ruku] the current division
     * @param int $div_num range [1-604] the current division number
     *
     * @return array $updated_data the updated Navigator configuration data
     *    sura => int [1-114] the new sura
     *    sura_ruku => int [1-40] the new ruku id
     *    start_ayat => int [1-6236] the new start ayat
     *    end_ayat => int [1-6236] the new end ayat
     *    div_num => int [1-604] the new division number
     *    audiofile => string the base audio file name     
     */
    public function GetNavigatorConfig(
        string $action,
        int $sura,
        int $sura_ruku,
        string $division,
        int $div_num
    ) : array {
    
        /** The updated data */
        $updated_data                             = array();
        /** If a sura was selected from the sura dropdown */
        if ($action == "sura_box") {
            /** The updated data containing the new sura and ruku */
            $updated_data                         = Config::GetComponent("surasel")->GetConfig(
                                                        $sura,
                                                        $div_num,
                                                        $division
                                                    );
        }
        /** If a ruku was selected from the ruku dropdown */
        else if ($action == "ruku_box") {
            /** The updated data containing the new ayat */
            $updated_data                         = Config::GetComponent("rukusel")->GetConfig(
                                                        $sura_ruku,
                                                        $sura,
                                                        $division
                                                    );
        }
        /** If a division number was selected from the division number dropdown */
        else if ($action == "div_num_box") {
            /** The updated data containing the sura, ruku and ayat */
            $updated_data                         = Config::GetComponent("divnumsel")->GetConfig(
                                                        $div_num,
                                                        $division
                                                    );
        }
        /** If the next, previous or random button was clicked */
        else if ($action == "next" || $action == "prev" || $action == "random") {
            /** The ruku id is fetched for the given sura and sura ruku */
            $ruku_id                              = Config::GetComponent("rukus")->GetRukuId(
                                                        $sura,
                                                        $sura_ruku
                                                    );
            /** The updated data containing the sura, ruku and ayat */
            $updated_data                         = Config::GetComponent("btnsel")->GetConfig(
                                                       $ruku_id,
                                                       $division,
                                                       $action
                                                    );
        }
        /** The start and end ayat of the ruku are fetched */
        $ayat_data                                = Config::GetComponent("rukus")->GetStartAndEndAyatOfRuku(
                                                       (int) $updated_data["sura"],
                                                       (int) $updated_data["sura_ruku"]
                                                   );
        /** The sura details are fetched */
        $sura_details                             = Config::GetComponent("suras")->GetSuraDetails(
                                                        (int) $updated_data["sura"]
                                                    );
                                                   
        /** The start ayat id is set */
        $updated_data["start_ayat"]               = $ayat_data["start_ayat"];
        /** The end ayat id is set */
        $updated_data["end_ayat"]                 = $ayat_data["end_ayat"];
        /** The base audio file name for the sura */
        $updated_data["audiofile"]                = $sura_details['audiofile'];
        
        return $updated_data;
    }
}
