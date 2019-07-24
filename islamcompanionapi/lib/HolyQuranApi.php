<?php

declare(strict_types=1);

namespace IslamCompanionApi\Lib;

use \Framework\Config\Config as Config;

/**
 * It provides functions for fetching Holy Quran data
 *
 * @category   Lib
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class HolyQuranApi extends IslamCompanionApi
{
    /**
     * It returns the name of the required ayat table
     * It uses the narrator name and language given in application request
     * If the application is in test mode, then the test configuration information is used
     *
     * @return string $table_name the name of the required table
     */
    public function GetAyatTableName() : string 
    {
        /** The configured language */
        $language        = "";
        /** The configured narrator */
        $narrator        = "";
        
        /** If the application is in test mode, then the information in test config is used */
        if (Config::$config['test']['test_mode']) {
            /** The narrator is set */
            $narrator    = Config::$config['test']['narrator'];
            /** The language is set */
            $language    = Config::$config['test']['language'];            
        }
        /** If the application is not in test mode, then the information in general config is used */
        else {
            /** The narrator is set */
            $narrator    = Config::$config['general']['parameters']['narrator'];
            /** The language is set */
            $language    = Config::$config['general']['parameters']['language'];            
        }
        /** The dbinit object is fetched */
        $dbinit          = Config::GetComponent("dbinit");
        /** The Database class object is fetched */
        $database        = $dbinit->GetDbManagerClassObj("Database");
          
        /** The SQL query for fetching the file name */
        $sql             = "SELECT file_name FROM ic_quranic_author_meta WHERE translator=? AND language=?";
        /** The query parameters */
        $query_params    = array($narrator, $language);
        /** The first row is fetched */
        $row             = $database->FirstRow($sql, $query_params);
        /** The text to append to table name */
        $table_suffix    = str_replace(".txt", "", $row['file_name']);
        /** The table name for ayas */
        $table_name      = Config::$config['general']['mysql_table_names']['ayas'] . "-" . $table_suffix;
        
        return $table_name;
    }
    
    /**
     * It returns the table name and database object
     *
     * @param string $short_name the short table name
     *
     * @return array $dbinfo the database object and table name
     *    table_name => string the table name
     *    dbobj => the database object
     */
    public function GetDbInfo(string $short_name) : array 
    {                
        /** If the short table name is not ayas */
        if ($short_name != "ayas") {
            /** The table name for ayas */
            $table_name = Config::$config['general']['mysql_table_names'][$short_name];
        }
        /** If the short table name is ayas */
        else {
            /** The ayat table name is fetched */
            $table_name = $this->GetAyatTableName();
        }
        
        /** The dbinit object is fetched */
        $dbinit         = Config::GetComponent("dbinit");
        /** The Database class object is fetched */
        $database       = $dbinit->GetDbManagerClassObj("Database");
        
        /** The database information object is set */
        $dbinfo         = array("table_name" => $table_name, "dbobj" => $database);
        
        return $dbinfo;
    }
    
    /**
     * It provides list of all suras that are part of the given division
     *
     * @param int $div_num range [1-604] the division number     
     * @param string $division list [hizb,juz,page,manzil,ruku] the division name
     *
     * @return json $sura_list the names of all the suras that are in the given division
     *    ename => string brief decriptive name of the sura in English
     *    tname => string the sura name in English
     *    sindex => int range [1-114] the sura id
     */
    public function HandleGetSurasInDivision(int $div_num, string $division) : string
    {    
        /** If the division is ruku */
        if ($division == "ruku") {
            /** All sura data is fetched */
            $sura_list = Config::GetComponent("suras")->GetSuraData();
        }
        /** If the division is not ruku */
        else {
            /** Sura data for the given division and division number is fetched */
            $sura_list = Config::GetComponent("suras")->GetSuraDataForDivision($division, $div_num);
        }
        
        /** The data is json encoded */
        $sura_list     = json_encode($sura_list);
        
        return $sura_list;
    }
    /**
     * It provides the start and end ruku numbers for the given division, division number and sura
     *
     * @param int $div_num [1-604] the division number     
     * @param string $division list [hizb,juz,page,manzil,ruku] the division name
     * @param int $sura range [1-114] the sura number
     *
     * @return json $ruku_data the start and end ruku numbers
     *    start_ruku => int range [1-40] the start sura ruku number
     *    end_ruku => int range [1-40] the end sura ruku number
     */
    public function HandleGetRukuList(int $div_num, string $division, int $sura) : string
    {                
        /** The list of rukus is fetched */
        $ruku_data   = Config::GetComponent("rukus")->GetRukusInDivision(
                          $sura,
                          $division,
                          $div_num
                       );
        
        $ruku_data   = array(
                           "start_ruku" => $ruku_data[0]['sura_ruku'],
                           "end_ruku" => $ruku_data[count($ruku_data)-1]['sura_ruku']
                       );
                                  
        /** The data is json encoded */
        $ruku_data   = json_encode($ruku_data);
        
        return $ruku_data;
    }
    
    /**
     * It returns the arabic text and translation for the given verses
     *
     * @param int $end_ayat range [1-6236] the end ayat number
     * @param string $language custom the language for the verse text
     * @param string $narrator custom the translator name
     * @param int $start_ayat range [1-6236] the start ayat number
     * @param int $sura range [1-114] the sura number
     *
     * @return json $ayat_list the list of required ayas
     *    arabic_text => string the arabic text
     *    translation => string the translated text
     */
    public function HandleGetVerses(
        int $end_ayat,
        string $language,
        string $narrator,
        int $start_ayat,
        int $sura
    ) : string {
    
        /** The ayat information is fetched */
        $ayat_data    = Config::GetComponent("ayas")->GetAyasInSura(
                            $sura,
                            $start_ayat,
                            $end_ayat
                        );
        /** Each value is formatted */
        $ayat_list    = array_map(
                            function ($data) {
                                /** The required ayat data */
                                $ayat_data = array(
                                                 "arabic_text" => $data["arabic_text"],
                                                 "translation" => $data["translated_text"]
                                             );
                                return $ayat_data;
                            },
                            $ayat_data
                        );
        
        /** The data is json encoded */
        $ayat_list   = json_encode($ayat_list);  
        
        return $ayat_list;
    }
    
    /**
     * It returns the text for a random ruku along with meta data
     *
     * @param string $language custom the language for the verse text
     * @param string $narrator custom the translator name
     *
     * @return json $ayat_data the verse data
     *    arabic => array the verse text in arabic
     *    translation => array the translated text
     *    meta_data => array the ruku meta data
     *        sura => string the sura name
     *        sura_id => int range [1-114] the sura id
     *        start_ayat => int range [1-286] the start sura ayat
     *        end_ayat => int range [1-286] the end sura ayat     
     */
    public function HandleGetRandomVerses(string $language, string $narrator) : string
    {
        /** Random ayat data is fetched */
        $meta_data   = Config::GetComponent("ayas")->GetRandomRuku();
        /** The ayat text is fetched */
        $ayat_text   = Config::GetComponent("ayas")->GetAyasInSura(
                         (int) $meta_data['sura_id'],
                         (int) $meta_data['start_ayat'],
                         (int) $meta_data['end_ayat']
                       );

        /** The translation column is extracted */
        $translation = array_column($ayat_text, "translated_text");
        /** The arabic column is extracted */
        $arabic      = array_column($ayat_text, "arabic_text");
                
        /** The ayat data */
        $ayat_data   = array("arabic" => $arabic, "translation" => $translation, "meta_data" => $meta_data);
        /** The data is json encoded */
        $ayat_data   = json_encode($ayat_data);  
        
        return $ayat_data;
    }
    
    /**
     * It generates the navigator configuration data for the given action
     *
     * @param string $action list [sura_box,ruku_box,div_num_box,next,prev,random] the action taken by the user
     * @param int $div_num range [1-604] the current division number
     * @param string $division list [hizb,juz,page,manzil,ruku] the current division
     * @param int $sura range [1-114] the current sura
     * @param int $sura_ruku range [1-40] the current sura ruku
     *
     * @return json $navigator_data the updated Navigator configuration data
     *    sura => int range [1-114] the new sura
     *    sura_ruku => int range [1-40] the new ruku id
     *    start_ayat => int range [1-286] the new start ayat
     *    end_ayat => int range [1-286] the new end ayat
     *    div_num => int range [1-604] the new division number
     *    audiofile => string the base audio file name     
     */
    public function HandleGetQuranNavConfig(
        string $action,
        int $div_num,        
        string $division,    
        int $sura,
        int $sura_ruku
    ) : string {            

        /** The updated navigator configuration */
        $navigator_data     = Config::GetComponent("holyqurannavigator")->GetNavigatorConfig(
                                  $action,
                                  $sura,
                                  $sura_ruku,
                                  $division,
                                  $div_num
                              );

        /** The data is json encoded */
        $navigator_data     = json_encode($navigator_data);  
        
        return $navigator_data;
    }
    
    /**
     * It returns the list of all supported languages
     *
     * @return json $languages the list of all supported languages
     */
    public function HandleGetLanguages() : string
    {
        /** The list of all languages is fetched */
        $languages = Config::GetComponent("holyquranmetadata")->GetLanguages();
        /** The data is json encoded */
        $languages = json_encode($languages);  
        
        return $languages;
    }
    
    /**
     * It returns the list of all supported narrators for the given language
     *     
	 * @param string $language the language for which the narrators are required
	 *
     * @return json $narrators the list of all supported narrators
     */
    public function HandleGetNarrators(string $language) : string
    {
        /** The list of all narrators is fetched */
        $narrators = Config::GetComponent("holyquranmetadata")->GetTranslators($language);        
        /** The data is json encoded */
        $narrators = json_encode($narrators);  
        
        return $narrators;
    }
    
    /**
     * It returns list of ayas that contain the given text
     *
     * @param string $is_random list [yes,no] indicates if random search results should be fetched     
     * @param string $language custom the language for the verse text
     * @param string $narrator custom the translator name
     * @param int $page_number range [1-500] the search results page number
     * @param int $results_per_page range [1-20] the number of results per page     
     * @param string $search_text the search text     
     *
     * @return json $data contains the search results and total result count
     *    search_results => array the verse data that contains the given text
     *        translation => string the translated text
     *        meta_data => array the ruku meta data
     *            sura => string the sura name
     *            sura_id => int range [1-114] the sura id
     *            ayat_id => int range [1-286] the ayat number
     *    result_count => int the total number of results
     */
    public function HandleSearchAyat(
        string $is_random,    
        string $language,
        string $narrator,
        int $page_number,
        int $results_per_page,
        string $search_text        
    ) : string {
    
        /** The ayat table is searched for the given text */
        $data      = Config::GetComponent("ayas")->SearchAyat(
                         $search_text,
                         $is_random,
                         (int) $page_number,
                         (int) $results_per_page
                     );
        /** The data is json encoded */
        $data      = json_encode($data);  

        return $data;
    }
}
