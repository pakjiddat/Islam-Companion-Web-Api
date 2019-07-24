<?php

declare(strict_types=1);

namespace IslamCompanionApi\Lib\Hadith;

use \Framework\Config\Config as Config;

/**
 * This class provides functions that for fetching Hadith text from database using different conditions
 *
 * @category   Lib
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class Text
{
    /**
     * It fetches the ids of all hadith titles that have title same as the given title
     *
     * @param int $title_id the hadith title id
     *
     * @return array $title_ids the list of hadith title ids
     */
    public function GetHadithTitleIds(int $title_id) : array
    {                
        /** The database object and table name are fetched */
        $dbinfo         = Config::GetComponent("hadithapi")->GetDbInfo("text");
        
        /** The SQL query */
        $sql            = "SELECT id FROM `" . $dbinfo['table_name'] . "` WHERE title=";
        $sql            .= " (SELECT title from `" . $dbinfo['table_name'] . "` WHERE id=?)";
        /** The query parameters */
        $query_params   = array($title_id);
        /** All rows are fetched */
        $title_ids      = $dbinfo['dbobj']->AllRows($sql, $query_params);

        return $title_ids;
    }
    
    /**
     * It fetches the hadith text for the given hadith title id
     *
     * @param int $title_id the hadith title id
     *
     * @return string $text the hadith text
     */
    public function GetHadithText(int $title_id) : string
    {                
        /** The database object and table name are fetched */
        $dbinfo         = Config::GetComponent("hadithapi")->GetDbInfo("text");
        
        /** The SQL query */
        $sql            = "SELECT hadith_text FROM `" . $dbinfo['table_name'] . "` WHERE id=?";
        /** The query parameters */
        $query_params   = array($title_id);
        /** The first row is fetched */
        $row            = $dbinfo['dbobj']->FirstRow($sql, $query_params);        
        /** The Hadith book text */
        $text           = $row["hadith_text"];
        
        return $text;
    }
    
    /**
     * It return a list of random hadith text
     *
     * @param int $count the number of hadith to fetch
     *
     * @return array $hadith_data the hadith data
     *    text => string the hadith text
     *    source => string the hadith source
     *    book => string the hadith book name
     *    number => string the hadith number     
     */
    public function GetRandomHadithText(int $count) : array
    {                
        /** The database object and table name are fetched */
        $dbinfo      = Config::GetComponent("hadithapi")->GetDbInfo("text");
        /** The hadith books table name */
        $books_table = Config::GetComponent("hadithapi")->GetHadithTableName("books");
        
        /** The SQL query */
        $sql         = "SELECT t1.hadith_number as number, t1.hadith_text as text, t2.book, t2.source FROM ";
        $sql        .= " `" . $dbinfo['table_name'] . "` t1, `" . $books_table . "` t2";
        $sql        .= " WHERE t1.book_id=t2.id ORDER BY RAND() LIMIT 0, " . $count;
        /** All rows are fetched */
        $hadith_data = $dbinfo['dbobj']->AllRows($sql);
        
        return $hadith_data;
    }
    
    /**
     * It fetches the title of the given Hadith
     *
     * @param int $title_id the id of the Hadith
     *
     * @return string $title the Hadith title
     */
    public function GetHadithTitle(int $title_id) : string
    {       
        /** The database object and table name are fetched */
        $dbinfo         = Config::GetComponent("hadithapi")->GetDbInfo("text");
        
        /** The SQL query */
        $sql            = "SELECT title FROM `" . $dbinfo['table_name'] . "` WHERE id=?";
        /** The query parameters */
        $query_params   = array($title_id);
        /** The first row is fetched */
        $row            = $dbinfo['dbobj']->FirstRow($sql, $query_params, null);        
        /** The Hadith book title */
        $title          = $row["title"];
    
        return $title;
    }
    /**
     * It fetches the id of the first title for the given Hadith book
     *
     * @param int $book_id the id of the Hadith book
     *
     * @return int $title_id the id of the first title
     */
    public function GetFirstTitleIdOfBook(int $book_id) : int
    {       
        /** The database object and table name are fetched */
        $dbinfo         = Config::GetComponent("hadithapi")->GetDbInfo("text");
        
        /** The SQL query */
        $sql            = "SELECT id FROM `" . $dbinfo['table_name'] . "` WHERE book_id=? ORDER BY id ASC";
        /** The query parameters */
        $query_params   = array($book_id);
        /** The first row is fetched */
        $row            = $dbinfo['dbobj']->FirstRow($sql, $query_params, null);        
        /** The Hadith book title id */
        $title_id       = (int) $row["id"];

        return $title_id;
    }
    
    /**
     * It returns list of hadith that match the given search text
     *
     * @param string $is_random list [yes,no] indicates if random search results should be fetched
     * @param int $page_number range [1-20] the search results page number
     * @param int $results_per_page range [1-20] the number of results per page
     * @param string $search_text the search text     
     *
     * @return array $data the hadith data including the result count
     *    search_results => array the hadith data
     *        text => string the hadith text
     *        title => string the hadith title
     *        source => string custom the new hadith source
     *        book => string the book name
     *        number => string the hadith number
     *    result_count => int the total number of results     
     */
    public function SearchHadith(
        string $is_random,
        int $page_number,
        int $results_per_page,
        string $search_text
    ) : array {
    
        /** The database object and table name are fetched */
        $dbinfo      = Config::GetComponent("hadithapi")->GetDbInfo("text");
        /** The hadith books table name */
        $books_table = Config::GetComponent("hadithapi")->GetHadithTableName("books");
        
        /** The SQL query */
        $sql         = "SELECT t1.title, t1.hadith_number as number, t1.hadith_text as text, t2.book, t2.source FROM ";
        $sql        .= " `" . $dbinfo['table_name'] . "` t1, `" . $books_table . "` t2";
        $sql        .= " WHERE t1.book_id=t2.id AND t1.hadith_text LIKE ?";
        /** If random results are needed */
        if ($is_random == "yes") {
            /** The SQL query is updated */
            $sql    .= " ORDER BY RAND()";
        }
        /** The offset value is calculated */
        $offset      = ($page_number - 1) * $results_per_page;
        /** The number of results are limited */
        $sql        .= " LIMIT " . $offset . ", " . $results_per_page;

        /** The sql query parameters */
        $query_params = array("%" . $search_text . "%");
        /** All rows are fetched */
        $hadith_data  = $dbinfo['dbobj']->AllRows($sql, $query_params);
        
        /** The SQL query for fetching the total number of matching hadith */
        $sql          = "SELECT count(*) as total FROM ";
        $sql          .= " `" . $dbinfo['table_name'] . "` t1, `" . $books_table . "` t2";
        $sql          .= " WHERE t1.book_id=t2.id AND t1.hadith_text LIKE ?";                      
        /** The query parameters */
        $query_params = array("%" . $search_text . "%");
        /** The first row is fetched */
        $row          = $dbinfo['dbobj']->FirstRow($sql, $query_params);
        /** The total row count */
        $row_count    = $row['total'];
        /** The required data */
        $data         = array("search_results" => $hadith_data, "result_count" => $row_count);
        
        return $data;
    }
}
