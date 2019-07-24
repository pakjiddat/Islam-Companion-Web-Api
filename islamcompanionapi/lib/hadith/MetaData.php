<?php

declare(strict_types=1);

namespace IslamCompanionApi\Lib\Hadith;

use \Framework\Config\Config as Config;

/**
 * This class provides functions for fetching Hadith meta data such as Hadith language, book and source information
 *
 * @category   Lib
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class MetaData
{								     								     
    /** @var array $hadith_details The total number of hadith for each hadith source grouped by language */
    private $hadith_details = array();
    
    /**
     * Class constructor
     * It reads hadith meta data from csv files     
     */
    public function __construct()
    {
        /** The path to the data folder */
        $path                 = Config::$config['path']['app_path'] . DIRECTORY_SEPARATOR . "data";
        /** The list of supported Hadith languages */
        $languages            = Config::$config['custom']['hadith_languages'];
        /** The meta data is read for each language */
        for ($count = 0; $count < count($languages); $count++) {
            /** The hadith language */
            $language         = $languages[$count];
            /** The hadith meta data file */
            $file             = $path . DIRECTORY_SEPARATOR . "hadith-" . lcfirst($languages[$count]) . ".csv";
            /** The file is opened */
            $fh               = fopen($file, "r");
            /** The header is read */
            $contents         = fgetcsv($fh);
            /** The file contents are read */
            while (($contents = fgetcsv($fh)) !== false) {
                /** The hadith source */
                $source       = html_entity_decode($contents[0]);
                /** The hadith count */
                $hadith_count = $contents[1];
                /** The hadith book count */
                $book_count   = $contents[2];
                /** The hadith details are updated */
                $this->hadith_details[$language][$source] = array("books" => $book_count, "hadith" => $hadith_count);
            }
        }
    }

    /**
     * It returns the total number of hadith in all the hadith sources
     *
     * @param string $language the language for the hadith
     *
     * @return int $total_hadith_count the total hadith count
     */
    public function GetTotalHadithCount(string $language) : int
    {
        /** The total hadith count */
        $total_hadith_count      = 0;
        /** The hadith sources */
        $sources                 = array_values($this->hadith_details[$language]);
        /** Each Hadith source is checked */
        for ($count = 0; $count < count($sources); $count++) {        	
       		/** The total hadith count is updated */
	       	$total_hadith_count += ($sources[$count]['hadith']);
       	}
       	
        return $total_hadith_count;
    }
    
    /**
     * Indicates if the given language is a valid hadith language
     *
     * @param string $language the language to check
     *
     * @return bool $is_valid indicates if language is valid
     */
    public function IsLanguageValid(string $language) : bool
    {
        /** The list of supported Hadith languages */
        $languages = Config::$config['custom']['hadith_languages'];
        /** If the given language is in the list of supported hadith languages */
        $is_valid  = (in_array($language, $languages)) ? true : false;
       	
        return $is_valid;
    }
    
    /**
     * Indicates if the given source is a valid hadith source
     *
     * @param string $source the source to check
     *
     * @return bool $is_valid indicates if source is valid
     */
    public function IsSourceValid(string $source) : bool
    {
        /** The source is html decoded */
        $source           = html_entity_decode($source);
        /** Indicates if the hadith source is valid */
        $is_valid         = false;
        /** The hadith source details */
        $source_list      = array_values($this->hadith_details);
        /** Each source is checked */
        for ($count = 0; $count < count($source_list); $count++) {
            /** The source names */
            $source_names = array_keys($source_list[$count]);
            /** If the hadith source exists */
            if (in_array($source, $source_names)) {
                /** The source is marked as valid */
                $is_valid = true;
                /** The loop ends */
                break;
            }            
        }

        return $is_valid;
    }
    
    /**
     * Returns the list of hadith sources for the given language
     *
     * @param string $language the hadith language
     *
     * @return array $source_list the list of hadith sources
     */
    public function GetHadithSources(string $language) : array
    {
        /** The hadith sources */
        $sources = array_keys($this->hadith_details[$language]);
        
        return $sources;
    }
    
    /**
     * It fetches the source name, book id and title id of a hadith
     * The data is fetched from database
     * Meta data may be fetched for random hadith or for a given hadith title
     *
     * @param bool $is_random indicates if meta data for random hadith should be fetched
     * @param int $title_id the id of the hadith
     *
     * @return array $hadith_meta contains hadith meta data information
     *    title_id => int the hadith title id
     *    book_id => int the hadith book id
     *    book => string the hadith book name
     *    number => string the hadith number
     *    source => string the hadith source
     */
    public function GetHadithMeta(bool $is_random, ?int $title_id = null) : array
    {
        /** The database object and table name are fetched */
        $dbinfo        = Config::GetComponent("hadithapi")->GetDbInfo("text");
        /** The SQL query */
        $sql           = "SELECT id, book_id, hadith_number FROM `" . $dbinfo["table_name"] . "`";
        /** If a random hadith needs to be fetched */
        if ($is_random) {
            /** A random hadith is fetched */
            $sql          .= " GROUP BY title ORDER BY RAND()";
            /** The query parameters */
            $query_params  = null;
        }
        /** If data for certain hadith is needed */
        if ($title_id != null) {
            /** A random hadith is fetched */
            $sql          .= " WHERE id=?";
            /** The query parameters */
            $query_params  = array($title_id);            
        }
        /** The first row is fetched */
        $meta_data1    = $dbinfo['dbobj']->FirstRow($sql, $query_params);

        /** The database object and table name are fetched */
        $table_name    = Config::GetComponent("hadithapi")->GetHadithTableName("books");
        /** The SQL query */
        $sql           = "SELECT source, book FROM `" . $table_name . "` WHERE id=?";
        /** The query parameters */
        $query_params  = array($meta_data1["book_id"]);
        /** The first row is fetched */
        $meta_data2    = $dbinfo['dbobj']->FirstRow($sql, $query_params);
        
        /** The hadith meta data */
        $hadith_meta   = array(
                             "title_id" => $meta_data1["id"],
                             "number" => $meta_data1["hadith_number"],                             
                             "book_id" => $meta_data1["book_id"],
                             "book" => $meta_data2["book"],
                             "source" => $meta_data2["source"]
                         );
                         
        return $hadith_meta;                         
    }
    
    /**
     * It fetches the title for the given hadith
     *
     * @param int $title_id range [1-26824] the hadith title id
     *
     * @return string $title the hadith title
     */
    public function GetHadithTitle(int $title_id)
    {
        /** The database object and table name are fetched */
        $dbinfo        = Config::GetComponent("hadithapi")->GetDbInfo("text");
        /** The SQL query */
        $sql           = "SELECT title FROM `" . $dbinfo['table_name'] . "` WHERE id=?";
        /** The query parameters */
        $query_params  = array($title_id);
        /** The first row is fetched */
        $row           = $dbinfo['dbobj']->FirstRow($sql, $query_params);
        /** The hadith title id */
        $title_id      = $row['title'];
        
        return $title_id;
    }
    
    /**
     * It fetches the id of the last hadith
     *
     * @return int $title_id the id of the last hadith
     */
    public function GetLastHadithId() : int
    {
        /** The database object and table name are fetched */
        $dbinfo        = Config::GetComponent("hadithapi")->GetDbInfo("text");
        /** The SQL query */
        $sql           = "SELECT id FROM `" . $dbinfo['table_name'] . "` GROUP BY title ORDER BY id DESC LIMIT 0,1";
        /** The first row is fetched */
        $row           = $dbinfo['dbobj']->FirstRow($sql);
        /** The hadith title id */
        $title_id      = (int) $row['id'];
        
        return $title_id;
    }
    
    /**
     * It fetches the id of the next/previous hadith that has a different title than the given hadith
     *
     * @param int $title_id range [1-26824] the hadith title id
     * @param string $type list [next,prev] indicates if next or previous hadith should be fetched
     *
     * @return int $title_id the hadith title id
     */
    public function GetNextPrevHadithId(int $title_id, string $type) : int
    {
        /** The hadith title is fetched */
        $title         = $this->GetHadithTitle($title_id);
        /** The database object and table name are fetched */
        $dbinfo        = Config::GetComponent("hadithapi")->GetDbInfo("text");
        /** The SQL query */
        $sql           = "SELECT id FROM `" . $dbinfo['table_name'] . "` WHERE ";
        /** If the next hadith needs to be fetched */
        if ($type == "next") {
            /** The SQL query is updated */
            $sql       .= "id > ?";
            $sql       .= " AND title !=?";
            $sql       .= " GROUP BY title ORDER BY id ASC";
        }            
        /** If the prev hadith needs to be fetched */
        else if ($type == "prev") {
            /** The SQL query is updated */
            $sql       .= "id < ?";
            $sql       .= " AND title !=?";
            $sql       .= " GROUP BY title ORDER BY id DESC";
        }
        /** The SQL query is updated */
        $sql           .= " LIMIT 0,1";
        
        /** The query parameters */
        $query_params  = array($title_id, $title);
        /** The first row is fetched */
        $row           = $dbinfo['dbobj']->FirstRow($sql, $query_params);
        /** If no data was found and next hadith is required, then title id is set to 1 */
        if ($row == null && $type == "next")
            $title_id  = 1;
        /** If no data was found and prev hadith is required, then title id is set to the id of last hadith */
        else if ($row == null && $type == "prev")
            $title_id  = Config::GetComponent("hadithmetadata")->GetLastHadithId();            
        /** If data was found */
        else
            $title_id  = (int) $row['id'];
        
        return $title_id;
    }
}
