<?php

declare(strict_types=1);

namespace IslamCompanionApi\Lib;

use \Framework\Config\Config as Config;

/**
 * It provides functions for fetching Hadith data
 *
 * @category   Lib
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class HadithApi extends IslamCompanionApi
{
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
        /** The table name */
        $table_name     = $this->GetHadithTableName($short_name);
        /** The dbinit object is fetched */
        $dbinit         = Config::GetComponent("dbinit");
        /** The Database class object is fetched */
        $database       = $dbinit->GetDbManagerClassObj("Database");
        
        /** The database information object is set */
        $dbinfo         = array("table_name" => $table_name, "dbobj" => $database);
        
        return $dbinfo;
    }
    
    /**
     * It returns the name of the required hadith table
     * It uses the narrator name and language given in application request
     * If the application is in test mode, then the test configuration information is used
     *
     * @param string $short_name list [text,books] the short table name
     *
     * @return string $table_name the name of the required table
     */
    public function GetHadithTableName(string $short_name) : string 
    {
        /** The configured language */
        $language = "";
        /** If the application is in test mode, then the information in test config is used */
        if (Config::$config['test']['test_mode']) {
            /** The language is set */
            $language    = Config::$config['test']['language'];
        }
        /** If the application is not in test mode, then the information in general config is used */
        else {
            /** The language is set */
            $language    = Config::$config['general']['parameters']['language'];
        }
        /** The language is converted to lower case */
        $language        = strtolower($language);
        
        /** If the short name is text */
        if ($short_name == 'text') {        
            /** The table name for hadith text */
            $table_name  = Config::$config['general']['mysql_table_names']['hadith_' . $language];
        }
        /** If the short name is books */
        else if ($short_name == 'books') {        
            /** The table name for hadith books */
            $table_name  = Config::$config['general']['mysql_table_names']['hadith_books_' . $language];
        }
        
        return $table_name;
    }
    
    /**
     * It returns the list of hadith books for the given Hadith source
     *
     * @param string $language custom the hadith language
     * @param string $source custom the hadith source for which the books need to be fetched
     *
     * @return json $hadith_books the list of hadith books
     *    id => int range [1-291] the hadith book id
     *    book => string the hadith book
     */
    public function HandleGetHadithBooks(string $language, string $source) : string
    {
        /** The hadith source */
        $source       = html_entity_decode($source);
        /** The list of hadith books */
        $hadith_books = Config::GetComponent("hadithbooks")->GetBooks($source);

        /** The data is json encoded */
        $hadith_books = json_encode($hadith_books);
        
        return $hadith_books;
    }
    
    /**
     * It fetches list of Hadith book titles for the given hadith book and source
     * Only distinct titles are fetched
     *
     * @param int $book_id range [1-291] the hadith book id
     * @param string $language custom the hadith language
     *
     * @return json $titles the list of hadith book titles
     *    id => int range [1-26824] the hadith title id
     *    title => string the hadith title
     */
    public function HandleGetHadithTitles(int $book_id, string $language) : string
    {
        /** The list of hadith book titles */
        $titles = Config::GetComponent("hadithbooks")->GetBookTitles($language, $book_id);
        /** The data is json encoded */
        $titles = json_encode($titles);
        
        return $titles;
    }
    
    /**
     * It fetches list of hadith text for the given hadith title and book id
     * All hadith under the given title are fetched
     *
     * @param string $language custom the hadith language     
     * @param int $title_id range [1-26824] the hadith title id
     *
     * @return json $hadith_data the list of hadith
     *    text => string the hadith text
     *    title => string the hadith title
     *    title_id => int the hadith title id
     *    book_id => int the hadith book id
     *    book => string the hadith book name
     *    number => string the hadith number
     *    source => string the hadith source
     */
    public function HandleGetHadithText(string $language, int $title_id) : string
    {     
        /** The required hadith data */
        $hadith_data   = array();
        /** All hadith title ids with same titles as the given title are fetched */
        $title_ids     = Config::GetComponent("hadithtext")->GetHadithTitleIds($title_id);
        /** The data for each title id is fetched */
        for ($count = 0; $count < count($title_ids); $count++) {
            /** The hadith id */
            $id            = (int) $title_ids[$count]["id"];
            /** The hadith text */
            $hadith_text   = Config::GetComponent("hadithtext")->GetHadithText($id);
            /** The hadith title */
            $hadith_title  = Config::GetComponent("hadithtext")->GetHadithTitle($id);
            /** The hadith meta data */
            $meta_data     = Config::GetComponent("hadithmetadata")->GetHadithMeta(false, $id);
            /** The hadith data is enclosed in array */
            $data          = array("text" => $hadith_text, "title" => $hadith_title);
            /** The hadith meta data is merged with the hadith data */
            $data          = array_merge($data, $meta_data);
            /** The hadith data is enclosed in an array */
            $hadith_data   []= $data;
        }
        
        /** The data is json encoded */
        $hadith_data   = json_encode($hadith_data);

        return $hadith_data;
    }
    
    /**
     * It fetches list of random hadith text
     *
     * @param int $hadith_count range [1-10] the number of hadith to fetch
     * @param string $language custom the hadith language      
     *
     * @return json $hadith_data the random hadith text
     *    text => string the hadith text
     *    source => string custom the hadith source
     *    book => string custom the hadith book name
     *    number => string int [1-2000] the hadith number
     */
    public function HandleGetRandomHadithText(int $hadith_count, string $language) : string
    {      
        /** The hadith data */
        $hadith_data = Config::GetComponent("hadithtext")->GetRandomHadithText($hadith_count);        
        /** The data is json encoded */
        $hadith_data = json_encode($hadith_data);

        return $hadith_data;
    }
    
    /**
     * It fetches list of hadith sources for the given language
     *
     * @param string $language custom the hadith language      
     *
     * @return json $hadith_sources the list of hadith sources
     */
    public function HandleGetHadithSources(string $language) : string
    {      
        /** The hadith source */
        $hadith_sources = Config::GetComponent("hadithmetadata")->GetHadithSources($language);
        /** The data is json encoded */
        $hadith_sources = json_encode($hadith_sources);

        return $hadith_sources;
    }
    
    /**
     * It returns the navigator configuration for given navigator action
     *
     * @param string $action list [language_box,source_box,book_box,title_box,next,prev,random] the action taken by the user
     * @param int $book_id range [1-291] the hadith book id
     * @param string $language custom the hadith language     
     * @param string $source custom the hadith source
     * @param int $title_id range [1-26824] the hadith book title id
     *
     * @return json $updated_data the updated Navigator configuration data
     *    source => string custom the new hadith source
     *    book_id => int range [1-291] the new hadith book id
     *    title_id => int range [1-26824] the new hadith book title id
     */
    public function HandleGetHadithNavConfig(
        string $action,
        int $book_id,    
        string $language,       
        string $source,
        int $title_id
    ) : string {
        /** The updated navigator configuration */
        $updated_data       = Config::GetComponent("hadithnavigator")->GetNavigatorConfig(
                                  $source,
                                  $book_id,
                                  $title_id,
                                  $language,
                                  $action
                              );                               

        /** The data is json encoded */
        $updated_data       = json_encode($updated_data);

        return $updated_data;
    }
    
    /**
     * It returns list of hadith that contain the given text
     *
     * @param string $is_random list [yes,no] indicates if random search results should be fetched
     * @param string $language custom the language for the hadith text
     * @param int $page_number range [1-500] the search results page number
     * @param int $results_per_page range [1-20] the number of results per page
     * @param string $search_text the search text
     *
     * @return json $data contains the search results and total result count
     *    search_results => array the search results
     *        text => string the hadith text
     *        title => string the hadith title
     *        source => string custom the new hadith source
     *        book => string the book name
     *        number => string the hadith number
     *    result_count => int the total number of results
     */
    public function HandleSearchHadith(
        string $is_random,    
        string $language,
        int $page_number,
        int $results_per_page,
        string $search_text        
    ) : string {
    
        /** The hadith table is searched for the given text */
        $data = Config::GetComponent("hadithtext")->SearchHadith(
                    $is_random,
                    (int) $page_number,
                    (int) $results_per_page,
                    $search_text
                );
                             
        /** The data is json encoded */
        $data = json_encode($data);  

        return $data;
    }
}
