<?php

declare(strict_types=1);

namespace IslamCompanionApi\Lib\Hadith;

use \Framework\Config\Config as Config;

/**
 * It provides functions for fetching Hadith book data
 *
 * @category   Lib
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class Books
{
    /**
     * It fetches the list of book titles for the given book id
     *
     * @param string $language custom the Hadith language     
     * @param int $book_id the hadith book id
     *
     * @return array $titles the list of hadith titles
     *    id => int the book title id
     *    title => string the book title
     */
    public function GetBookTitles(string $language, int $book_id) : array
    {
        /** The database object and table name are fetched */
        $dbinfo         = Config::GetComponent("hadithapi")->GetDbInfo("text");
        
        /** The SQL query */
        $sql            = "SELECT id, title FROM `" . $dbinfo['table_name'] . "` WHERE book_id=?";
        $sql            .= " GROUP BY title ORDER BY id ASC";
        /** The query parameters */
        $query_params   = array($book_id);
        /** All rows are fetched */
        $titles         = $dbinfo['dbobj']->AllRows($sql, $query_params);
        
        return $titles;
    }
    
    /**
     * It fetches the list of all books for given hadith source
     *
     * @param string $source the hadith source
     *
     * @return array $books the list of books
     *    id => int the book id
     *    book => string the book name
     */
    public function GetBooks(string $source) : array
    {
        /** The database object and table name are fetched */
        $dbinfo       = Config::GetComponent("hadithapi")->GetDbInfo("books");
        
        /** The SQL query */
        $sql          = "SELECT id, book FROM `" . $dbinfo['table_name'] . "`";
        $sql          .= " WHERE source=? ORDER BY book_number ASC";
        /** The query parameters */
        $query_params = array($source);
        /** All rows are fetched */
        $books        = $dbinfo['dbobj']->AllRows($sql, $query_params);
       
        return $books;
    }
    /**
     * It fetches the book id of the first book of the given Hadith source
     *
     * @param string $source the Hadith source
     *
     * @return int $book_id the id of the first Hadith book
     */
    public function GetFirstBookIdOfSource(string $source) : int
    {
        /** The database object and table name are fetched */
        $dbinfo         = Config::GetComponent("hadithapi")->GetDbInfo("books");
        
        /** The SQL query */
        $sql            = "SELECT id FROM `" . $dbinfo['table_name'] . "` WHERE source=? ORDER BY book_number ASC";
        /** The query parameters */
        $query_params   = array($source);
        /** The first row is fetched */
        $row            = $dbinfo['dbobj']->FirstRow($sql, $query_params);
        /** The Hadith book id */
        $book_id        = (int) $row["id"];

        return $book_id;
    }
    
    /**
     * Used to get the Hadith source for the given Hadith book id
     *
     * @param int $book_id the Hadith book id
     *
     * @return string $source the required Hadith source
     */
    public function GetHadithBookSource(int $book_id) : string
    {
        /** The database object and table name are fetched */
        $dbinfo         = Config::GetComponent("hadithapi")->GetDbInfo("books");
        
        /** The SQL query */
        $sql            = "SELECT source FROM `" . $dbinfo['table_name'] . "` WHERE id=?";
        /** The query parameters */
        $query_params   = array($book_id);
        /** The first row is fetched */
        $row            = $dbinfo['dbobj']->FirstRow($sql, $query_params);
        /** The Hadith source */
        $source         = $row["source"];

        return $source;
    }
    
    /**
     * Used to get the Hadith book id for the given title id
     *
     * @param string $title_id the Hadith title id
     *
     * @return int $book_id the Hadith book id
     */
    public function GetHadithBookId(int $title_id) : int
    {
        /** The database object and table name are fetched */
        $dbinfo         = Config::GetComponent("hadithapi")->GetDbInfo("text");
        
        /** The SQL query */
        $sql            = "SELECT book_id FROM `" . $dbinfo['table_name'] . "` WHERE id=?";
        /** The query parameters */
        $query_params   = array($title_id);
        /** The first row is fetched */
        $row            = $dbinfo['dbobj']->FirstRow($sql, $query_params);
        
        /** The Hadith book id */
        $book_id        = (int) $row["book_id"];

        return $book_id;
    }
}

