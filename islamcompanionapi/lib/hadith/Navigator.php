<?php

declare(strict_types=1);

namespace IslamCompanionApi\Lib\hadith;

use \Framework\Config\Config as Config;

/**
 * This class implements the Navigator class
 *
 * It provides functions for managing hadith Navigator
 *
 * @category   Lib
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class Navigator
{   
    /**
     * It generates configuration data for the navigator after an action such as selection of hadith source box
     *
     * @param string $source custom the hadith source
     * @param int $book_id range [1-291] the hadith book id
     * @param int $title_id range [1-26824] the hadith title id
     * @param string $language custom the hadith language
     * @param string $action list [source_box,book_box,title_box,next,prev,random] the action taken by the user
     *
     * @return array $updated_data the updated Navigator configuration data
     *    source => string the new hadith source
     *    book_id => int [1-291] the new hadith book id
     *    title_id => int [1-26824] the new hadith book title id 
     */
    public function GetNavigatorConfig(
        string $source,
        int $book_id,
        int $title_id,
        string $language,
        string $action
    ) : array {
        /** The updated data */
        $updated_data                    = array();
        /** If a language was selected from the hadith language dropdown */
        if ($action == "language_box") {
            /** The list of all hadith sources for the selected language are fetched */
            $source_list                 = Config::GetComponent("hadithmetadata")->GetHadithSources(
                                               $language
                                           );
            /** The hadith source is set */
            $source                      = $source_list[0];            
            /** The book id of the first book in the hadith source is fetched */
            $updated_data["book_id"]     = Config::GetComponent("hadithbooks")->GetFirstBookIdOfSource(
                                               $source
                                           );
            /** The title id of the first title in the hadith book is fetched */
            $updated_data["title_id"]    = Config::GetComponent("hadithtext")->GetFirstTitleIdOfBook(
                                               $updated_data["book_id"]
                                           );
            /** The hadith source is set */
            $updated_data["source"]      = $source;                                           
        }
        /** If a source was selected from the hadith source dropdown */
        else if ($action == "source_box") {
            /** The book id of the first book in the hadith source is fetched */
            $updated_data["book_id"]     = Config::GetComponent("hadithbooks")->GetFirstBookIdOfSource(
                                               $source
                                           );
            /** The title id of the first title in the hadith book is fetched */
            $updated_data["title_id"]    = Config::GetComponent("hadithtext")->GetFirstTitleIdOfBook(
                                               $updated_data["book_id"]
                                           );
            /** The hadith source is set */
            $updated_data["source"]      = $source;                                                   
        }
        /** If a book was selected from the hadith book dropdown */
        else if ($action == "book_box") {
            /** The title id of the first title in the hadith book is fetched */
            $updated_data["title_id"]    = Config::GetComponent("hadithtext")->GetFirstTitleIdOfBook(
                                               $book_id
                                           );
            /** The hadith source is set */
            $updated_data["source"]      = $source;
            /** The hadith book id is set */
            $updated_data["book_id"]     = $book_id;                                                    
        }
        /** If a title was selected from the hadith title dropdown */
        else if ($action == "title_box") {
            /** The hadith source is set */
            $updated_data["source"]      = $source;
            /** The hadith book id is set */
            $updated_data["book_id"]     = $book_id;
            /** The hadith title id is set */
            $updated_data["title_id"]    = $title_id;            
        }
        /** If the random button was clicked */
        else if ($action == "random") {
            /** The random hadith meta data is fetched */
            $hadith_meta              = Config::GetComponent("hadithmetadata")->GetHadithMeta(true);
            /** The hadith book id is fetched */
            $updated_data["book_id"]  = $hadith_meta["book_id"];
            /** The hadith title id is fetched */
            $updated_data["title_id"] = $hadith_meta["title_id"];
            /** The hadith source is fetched */
            $updated_data["source"]   = $hadith_meta["source"];            
        }
        /** If the next or previous button was clicked */
        else if ($action == "next" || $action == "prev") {            
            /** The hadith title id is set to the id of the next/prev hadith */
            $updated_data["title_id"] = Config::GetComponent("hadithmetadata")->GetNextPrevHadithId(
                                            $title_id,
                                            $action
                                        );
                                        
            /** The hadith book id is fetched */
            $updated_data["book_id"]  = Config::GetComponent("hadithbooks")->GethadithBookId(
                                            $updated_data["title_id"]
                                        );
            /** The hadith source is fetched for the given hadith book */
            $updated_data["source"]   = Config::GetComponent("hadithbooks")->GethadithBookSource(
                                            $updated_data["book_id"]
                                        );
        }
       
        return $updated_data;
    }
}
