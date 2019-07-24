<?php

declare(strict_types=1);

namespace IslamCompanionApi\Lib\HolyQuran;

use \Framework\Config\Config as Config;

/**
 * It provides functions for fetching Holy Quran meta data
 * The meta data includes author, language ayat and sura meta data
 *
 * @category   Lib
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class MetaData
{
	/** @var array $max_division_count The maximum number of divisions for each division type */
    private $max_division_count  = array(
                                       "hizb" => 240, 
    									"juz" => 30, 
    									"manzil" => 7, 
    									"page" => 604, 
    									"ruku" => 556, 
    									"sura" => 114, 
    									"ayas" => 6236, 
    									"author" => 111
    							   );
    									
	/**
     * It checks if the given division name is valid
	 * 
	 * @param string $div_num the required division number
	 * @param string $division the required division name
	 * @throws object \Error an exception is thrown if the given division number is not valid
	 * 
	 * @return boolean $is_valid returns true if the division name is valid for the given division
     */
    public function IsValidDivision(string $div_num, string $division) : bool
	{
		/** Used to indicate if division number is valid */
		$is_valid           = false;
		/** The maximum division number for the division */
		$max_division_count = $this->max_division_count[$division];		
		/** If the required sura number is less than 1 or greater than the maximum sura count */ 
		if ($div_num < 1 || $div_num > $max_division_count)
		    /** An exception is thrown */
		    throw new \Error("Invalid division number: " . $div_num . " of division: " . $division);
		else {
		    /** The division number is marked as valid */
    		$is_valid       = true;
	    }
	    	
		return $is_valid;
	}
	
	/** 
     * It returns the max_division_count property
	 * 
	 * @param string $division the required division name
	 * 
	 * @return int $max_division_count the maximum number of divisions for the given division
     */
    public function GetMaxDivisionCount(string $division) : int
	{
		$max_division_count = $this->max_division_count[$division];
		
		return $max_division_count;
	}
	
    /**
     * It gets the names of all the supported languages     
     *
     * @return array $languages the list of all supported languages
     */
    public function GetLanguages() : array
    {    	
        /** The database object and table name are fetched */
        $dbinfo         = Config::GetComponent("holyquranapi")->GetDbInfo("author");
        
        /** The SQL query */
        $sql            = "SELECT distinct language FROM `" . $dbinfo['table_name'] . "` ORDER BY language ASC";
        /** All rows are fetched */
        $languages      = $dbinfo['dbobj']->AllRows($sql, null, null, \PDO::FETCH_COLUMN, 0);

        return $languages;
    }
    
    /**
     * It gets the names of all the supported translators for the given language     
     *
	 * @param string $language optional the required division name
	 *     
     * @return array $translators the list of all supported translators
     */
    public function GetTranslators(?string $language = null) : array
    {
        /** The database object and table name are fetched */
        $dbinfo         = Config::GetComponent("holyquranapi")->GetDbInfo("author");
        
        /** The SQL query */
        $sql            = "SELECT distinct translator FROM `" . $dbinfo['table_name'] . "`";
        
        /** If the language is given */
        if ($language != null) {
            /** The SQL query parameters */
            $params         = array($language);
            /** The where clause is appended to the SQL query */
            $sql           .= " WHERE language=? ORDER BY translator ASC";            
        }
        /** If the language is not given */
        else {
            /** The SQL query parameters */
            $params         = null;
            /** The SQL query is updated */
            $sql           .= " ORDER BY translator ASC";                 
        }            
        /** All rows are fetched */
        $translators    = $dbinfo['dbobj']->AllRows($sql, $params, null, \PDO::FETCH_COLUMN, 0);
        
        return $translators;
    }
    
	/**
     * It checks if the value of the given translator is valid
     *
     * @param string $translator the given translator
     *
     * @return boolean $is_valid used to indicate if the given translator is valid
     */
    public function IsTranslatorValid(string $translator) : bool
    {
        /** Indicates if the given translator is valid */
        $is_valid            = false;
        /** The list of all supported translators is fetched */
        $translator_list     = $this->GetTranslators();
        
        /** If the translator exists */
        $is_valid            = (in_array($translator, $translator_list)) ? true : false;
        
        return $is_valid;
    }
    /**
     * It checks if the value of the given language is valid
     *
     * @param string $language the given language
     *
     * @return boolean $is_valid used to indicate if the given language is valid
     */
    public function IsLanguageValid(string $language) : bool
    {
        /** Indicates if the given language is valid */
        $is_valid            = false;
        /** The list of all supported languages is fetched */
        $language_list       = $this->GetLanguages();
        
        /** If the language exists */
        $is_valid            = (in_array($language, $language_list)) ? true : false;
       
        return $is_valid;
    }
}
