<?php

declare(strict_types=1);

namespace IslamCompanionApi\Scripts;

use \Framework\DataAbstraction\MysqlDataObject as MysqlDataObject;

/**
 * This class implements the functionality of the Hadith Data Import
 *
 * It contains functions that are used to import hadith data from pdf to mysql database
 * It also contains function for downloading hadith data from hadithcollection.com website
 *
 * @category   IslamCompanionApi
 * @package    Scripts
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class ImportHadithData extends \Framework\Testing\Testing
{
    /**
     * Used to verify the hadith count for the ahadith downloaded from www.hadithcollection.com
     *
     * It checks the hadith count for each hadith source
     * It compares the hadith count with the count of hadith stored in database
     * It throws an exception if there the counts do not match
     */
    private function VerifyHadithCollectionData() : void
    {
        /** The list of hadith source urls */
        $hadith_source_urls = array(
            "Sahih Bukhari" => "http://www.hadithcollection.com/sahihbukhari.html",
            "Sahih Muslim" => "http://www.hadithcollection.com/sahihmuslim.html",
            "Maliks Muwatta" => "http://www.hadithcollection.com/maliksmuwatta.html",
            "Shamaa-il Tirmidhi" => "http://www.hadithcollection.com/shama-iltirmidhi.html",
            "Abu Dawud" => "http://www.hadithcollection.com/abudawud.html",
            "Hadith Qudsi" => "http://www.hadithcollection.com/hadith-qudsi.html",
            "An Nawawi's Fourty Hadiths" => "http://www.hadithcollection.com/an-nawawis-forty-hadith.html",
            "Authentic Supplications of the Prophet" => "http://www.hadithcollection.com/authentic-supplications-of-the-prophet.html"
        );
        /** Each source is checked */
        foreach ($hadith_source_urls as $hadith_source => $url) 
        {
            /** The application configuration is fetched */
            $configuration = $this->GetConfigObject();
            /** The configuration object is fetched */
            $parameters['configuration'] = $configuration;
            /** The mysql data object is created */
            $mysql_data_object = new MysqlDataObject($parameters);
            /** The mysql table name */
            $table_name = "ic_hadith_urdu";
            /** The table name is set */
            $mysql_data_object->SetTableName($table_name);
            /** The key field is set */
            $mysql_data_object->SetKeyField("id");
            /** The mysql data object is set to read/write */
            $mysql_data_object->SetReadOnly(true);
            /** The condition used to read the data from database */
            $condition = array(
                array(
                    "field" => "source",
                    "value" => "%" . $hadith_source . "%",
                    "operator" => "",
                    "operation" => "LIKE"
                )
            );
            /** The parameters used to read the data */
            $parameters = array(
                "fields" => "count(*) as total",
                "condition" => $condition,
                "read_all" => false
            );
            /** The Mysql data is read from database */
            $mysql_data_object->Read($parameters);
            /** The data is fetched */
            $data = $mysql_data_object->GetData(true);   
            /** The total number of ahadith in database */
            $total_ahadith_in_database = $data['total'];                     
            /** The file contents */
            $file_contents = $this->GetComponent("filesystem")->GetFileContent($url);  
            /** The file count is checked */
            preg_match_all('/<span class="badge badge-info tip hasTooltip" title="Hadith Count:"> (\d+) <\/span> <\/h3>/iU', $file_contents, $matches);
            /** The total number of ahadith */
            $total = 0;
            /** The total is calculated */
            for ($count = 0; $count < count($matches[1]); $count++)
            {
                $total += $matches[1][$count];
            }
            /** The total in database is compared with total on website */
            echo("The total number of ahadith for the source: ". $hadith_source . "\nAhadith in database: " . $total_ahadith_in_database . "\nAhadith on website: ". $total . "\n\n");            
        }
    }
    /** 
     * Used to import the Hadith data from text files to database
     *
     * It scans the ahadith folder recursively and reads each text file
     * For each text file, it imports the data to database
     */
    public function ImportTextFiles() : void
    {
        /** The txt folder path */
        $folder_path          = $this->GetConfig("path", "application_path") . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "ahadith" . DIRECTORY_SEPARATOR . "urdu" . DIRECTORY_SEPARATOR . "working";
        //$folder_path          = $this->GetConfig("path", "application_path") . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "ahadith" . DIRECTORY_SEPARATOR . "english";
        /** The ahadith folder is read */
        $file_list            = $this->GetComponent("filesystem")->GetFolderContents($folder_path, 5, ".txt", "pdf", true);
        /** Each text file is read */
        for ($count = 0; $count < count($file_list); $count++) 
        {
        	//if (strpos($file_list[$count],"An Nawawi's Forty Hadith.txt")===false)continue;
            /** The source file name */
            $source_file_name = $file_list[$count];
            /** Indicates the language of the file */
            $language         = (strpos($source_file_name, "urdu") !== false) ? "urdu_arabic" : "english";
            /** The file contents */
            $file_contents    = $this->GetComponent("filesystem")->GetFileContent($source_file_name);
            //if(strpos($source_file_name, "Imam_Nawawi_Hadith") === false) continue;
            /** If the language for the hadith is English */
            //if ($language == "english") $this->SaveHadithBookEnglish($file_contents, $source_file_name);
            /** If the language for the hadith is Urdu */
            if ($language == "urdu_arabic") $this->SaveHadithBookUrdu($file_contents, $source_file_name);            
        }
    }
    /**
     * Used to parse the ahadith urdu book data
     *
     * It parses the book data and returns the parsed data
     *
     * @param string $text the hadith text
     * @param string $file_name the name of the text file being parsed
     *
     * @return array $hadith_text_information the parsed ahadith book data
     */
    private function ParseAhadithUrduBookData(string $text, string $file_name) : array
    {
    	/** The file name is parsed */
    	$temp_arr                = explode("/", $file_name);
    	/** The short file name */
    	$file_name               = $temp_arr[count($temp_arr)-1];
        /** The data is split on newline */
        $data                    = mb_split("\n", $text);
        /** The book name in arabic */
        $book_name_arabic        = "";
        /** The book name in urdu */
        $book_name_urdu          = "";
        /** The hadith number */
        $hadith_number           = "";
        /** The topic name or hadith title in arabic */
        $title_arabic            = "";
        /** The topic name or hadith title in urdu */
        $title_urdu              = "";
        /** The ahadith source */
        $source                  = $data[0];
        /** The ahadith text in arabic */
        $ahadith_text_arabic     = array();
        /** The ahadith text in urdu */
        $ahadith_text_urdu       = array();        
        /** The hadith text information. It contains data for single hadith */
        $hadith_text_information = array();
        /** Each data item is checked */
        for ($count = 1; $count < count($data); $count++) 
        {        
        	/** A single line of text */
            $line_text            = $this->CleanSpaces($data[$count]);         
            /** If the line is empty, then the loop continues */
            if ($line_text == "") continue;
            /** If the line starts with 'كتاب' */
            if (mb_strpos($line_text, 'كتاب') !== false && mb_strpos($line_text, 'كتاب') < 10 ) {
                $book_name_arabic = $line_text;
                $book_name_urdu   = $data[$count+1];
            }
            /** If the line starts with 'بَابُ' */
            else if ((mb_strpos($line_text, 'بَابُ') !== false && mb_strpos($line_text, 'بَابُ') <=10) || (mb_strpos($line_text, 'باب') !== false && mb_strpos($line_text, 'باب') <=10) || (mb_strpos($line_text, 'بَابٌ') !== false && mb_strpos($line_text, 'بَابٌ') <=10) || (mb_strpos($line_text, 'بَاب') !== false && mb_strpos($line_text, 'بَاب') <=10)) {
                $title_arabic    = ($line_text == "" || $line_text == $title_arabic) ? $title_arabic : $line_text;
                $title_urdu      = ($data[$count+1] == "" || $data[$count+1] == $title_urdu) ? $title_urdu : $data[$count+1];
         		
         		/** The counter is increased by 1 */
         		$count++;
         		/** If the hadith title is not set */
         		if (!isset($hadith_text_information['arabic'][$source][$book_name_arabic][$title_arabic])) {
	        		/** The hadith title is set */
	                $hadith_text_information['arabic'][$source][$book_name_arabic][$title_arabic] = array();
                }
                /** If the hadith title is not set */
         		if (!isset($hadith_text_information['arabic'][$source][$book_name_arabic][$title_arabic])) {
	        		/** The hadith title is set */
	                $hadith_text_information['arabic'][$source][$book_name_arabic][$title_arabic]    = array();
                }                
                
                while (isset($data[$count+1]) && mb_strpos($data[$count+1], 'حدیث نمبر') === false && mb_strpos($data[$count+1], 'باب') === false && mb_strpos($data[$count+1], 'بَابُ') === false && mb_strpos($data[$count+1], 'بَابٌ') === false && mb_strpos($data[$count+1], 'بَاب') === false) $count++;
            }
            /** If the line starts with 'حدیث نمبر' */
            else if (mb_strpos($line_text, 'حدیث نمبر') !== false && mb_strpos($line_text, 'حدیث نمبر') < 20 && mb_strpos($line_text, 'وضاحت') === false) {
                $hadith_number        = mb_ereg_replace('حدیث نمبر', '', $line_text);
                $hadith_number        = (mb_ereg_replace(':', '', $hadith_number));
                $ahadith_text_arabic  = ($data[$count+1]);
                $ahadith_text_urdu    = ($data[$count+2]);
                /** The counter is increased by 2 */
                $count+=2;
                
                /** If the next line contains 'امام ترمذی کہتے ہیں' */
                if (($file_name == "Sun an Tirmizi Mukammal.txt" && isset($data[$count+1])) && (mb_strpos($data[$count+1], 'امام ترمذی کہتے ہیں') !== false && mb_strpos($data[$count+1], 'امام ترمذی کہتے ہیں') < 5 ) || (mb_strpos($data[$count+1], 'تخریج دارالدعو') !== false && mb_strpos($data[$count+1], 'تخریج دارالدعو') < 5 )) {
                
                	while (true) {                      		
                		                	               
                		if (!isset($data[$count+1])) break;
                		
                		$data[$count+1]             = $this->CleanSpaces($data[$count+1]);          	
                		
                		if (mb_strpos($data[$count+1], 'اس باب میں') === false && mb_strpos($data[$count+1], 'باب') !== false && mb_strpos($data[$count+1], 'باب') <= 5) break;
                		if (mb_strpos($data[$count+1], 'حدیث نمبر') !== false && mb_strpos($data[$count+1], 'حدیث نمبر') <= 5) break;
                		if (mb_strpos($data[$count+1], 'بَابُ') !== false && mb_strpos($data[$count+1], 'بَابٌ') !== false) break;
                		if (mb_strpos($data[$count+1], 'كتاب') !== false && mb_strpos($data[$count+1], 'كتاب') <= 5) break;
                		if (mb_strpos($data[$count+1], 'بَاب') !== false && mb_strpos($data[$count+1], 'بَاب') <= 5) break;
                		if (mb_strpos($data[$count+1], 'Narrated') !== false && mb_strpos($data[$count+1], 'بَاب') <= 5) break;
                		
                		/** The ahadith text is updated */
                		$ahadith_text_urdu    .= ("@" . ($data[$count+1]));
	                	/** The counter is increased by 1 */
	                	$count++;
                	}
                	/** The trailing '@' is removed */
                	$ahadith_text_urdu = trim($ahadith_text_urdu, "@");
                	/** If the file name is 'Sunan_Abu_Dawood_Arabic_Urdu_English_Translation_Complete.txt' */
                	if ($file_name == 'Sunan_Abu_Dawood_Arabic_Urdu_English_Translation_Complete.txt') {
                		/** The hadith text is split on '@' */
                		$temp_arr = mb_split("@", $ahadith_text_urdu);
                		/** The last line containing english is removed */
                		$ahadith_text_urdu = implode("@", array_slice($temp_arr, 0, count($temp_arr)-1));
                	}
                }
             
            	/** The hadith text is set */
                $hadith_text_information['arabic'][$source][$book_name_arabic][$title_arabic][] = array("hadith_number" => $hadith_number, "hadith_text" => $ahadith_text_arabic);
                $hadith_text_information['urdu'][$source][$book_name_urdu][$title_urdu][]     = array("hadith_number" => $hadith_number, "hadith_text" => $ahadith_text_urdu);                 
            }
        }

        return $hadith_text_information;
    }
    /**
     * Used to store the contents of the ahadith data to database
     *
     * It stores each hadith to database
     *
     * @param string $text the hadith text
     * @param string $file_name the hadith text file name
     */
    private function SaveHadithBookUrdu(string $text, string $file_name) 
    {
    	/** The file name is parsed */
    	//$temp_arr                = explode("/", $file_name);
       	/** The short file name */
    	//$file_name               = $temp_arr[count($temp_arr)-1];
    	//if ($file_name != "Sun an Tirmizi Mukammal.txt")return;
    	
    	/** The hadith book number */
    	$hadith_book_number                      = 0;
        /** The text is parsed line by line */
        $ahadith_book_data                       = $this->ParseAhadithUrduBookData($text, $file_name);
        /** Data for both arabic and urdu hadith is saved */
        foreach ($ahadith_book_data as $language => $hadith_sources) {
		    /** Data for each hadith source is saved */
		    foreach ($hadith_sources as $source_name => $hadith_books) {
			  	/** The hadith book number */
		    	$hadith_book_number              = 0;
		    	/** Data for each hadith book is saved */
			    foreach ($hadith_books as $book_name => $hadith_titles) {	
					/** The hadith book number */
					$hadith_book_number++;
			    	/** Data for each hadith title is saved */
				    foreach ($hadith_titles as $title => $hadith_data) {		  
						/** Data for each hadith title is saved */
						for ($count = 0; $count < count($hadith_data); $count++) {
							/** The hadith information */
							$hadith_information          = $hadith_data[$count];
							/** The application configuration is fetched */
							$configuration               = $this->GetConfigObject();
							/** The configuration object is fetched */
							$parameters['configuration'] = $configuration;
							/** The mysql data object is created */
							$mysql_data_object           = new MysqlDataObject($parameters);
							/** The mysql table name */
							$table_name                  = "ic_hadith_" . $language;
							/** The table name is set */
							$mysql_data_object->SetTableName($table_name);
							/** The key field is set */
							$mysql_data_object->SetKeyField("id");
							/** The mysql data object is set to read/write */
							$mysql_data_object->SetReadOnly(false);
							
							if (extension_loaded("intl")) $source_name        = \Normalizer::normalize($source_name, \Normalizer::FORM_C);       
							if (extension_loaded("intl")) $book_name          = \Normalizer::normalize($book_name, \Normalizer::FORM_C);       
							if (extension_loaded("intl")) $hadith_information['hadith_text']      = \Normalizer::normalize($hadith_information['hadith_text'], \Normalizer::FORM_C);       
							if (extension_loaded("intl")) $title              = \Normalizer::normalize($title, \Normalizer::FORM_C);     
							
							/** The extra white space is removed */
							$source_name                = $this->CleanSpaces($source_name);
							/** The extra white space is removed */
							$book_name                  = $this->CleanSpaces($book_name);
							/** The extra white space is removed */
							$hadith_book_number         = $this->CleanSpaces($hadith_book_number);
							/** The extra white space is removed */
							$hadith_number              = $this->CleanSpaces($hadith_information['hadith_number']);
							/** The hadith number should not contain spaces */
							$hadith_number              = str_replace(" ", "", $hadith_number);
							/** The extra white space is removed */
							$hadith_text                = $this->CleanSpaces($hadith_information['hadith_text']);
							/** The '@' is replaced with <br/><br/> */
							$hadith_text                = str_replace("@", "<br/>", $hadith_text);
							
							/** The hadith data */
							$data          				= array(
																"source" => $source_name,
																"book" => $book_name,
																"book_number" => $hadith_book_number,
																"title" => $title,
																"hadith_number" => $hadith_number,
																"hadith_text" => $hadith_text
															);
															
							/** The hadith data is set to the MysqlDataObject */
							$mysql_data_object->SetData($data);
							/** The mysql data is saved to database */
							$mysql_data_object->Save();
							/** The user is informed that data is saved to database */
							echo "Saved Hadith : " . $hadith_book_number . ":" . $hadith_number . " to database\n";
							flush();
						}
					}
				}				    
		    }
        }
    }
    /**
     * Used to remove extra spaces
     *
     * It removes the extra spaces from the given text
     * It also removes new lines
     *
     * @param string $text the text to be formatted
     *
     * @return string $formatted_text the formatted text
     */
    private function CleanSpaces(string $text) : string
    {
    	/** The extra white space is removed */
    	$formatted_text              = mb_ereg_replace("/\s\s+/", " ", $text);
    	/** The leading and trailing white spaces are removed */
    	$formatted_text              = mb_ereg_replace("/\s?(.+)\s?/", "$1", $text);
    	/** The new lines are removed */
    	$formatted_text              = str_replace("\n", "", trim($formatted_text));
    	
    	return $formatted_text;
    }
    /**
     * Used to parse the ahadith english book data
     *
     * It parses the book data and returns the parsed data
     *
     * @param $text the hadith text
     *
     * @return array $ahadith_book_data the parsed ahadith book data
     */
    private function ParseAhadithEnglishBookData(string $text) : array
    {
        /** The data is split on newline */
        $data = explode("\n", $text);
        /** Indicates that text has started */
        $text_started = false;
        /** The book name */
        $book_name = "";
        /** The ahadith source */
        $source = "";
        /** The ahadith text data. It contains data for all hadith */
        $ahadith_text_data = array();
        /** The hadith text information. It contains data for single hadith */
        $hadith_text_information = array();
        /** Each data item is checked */
        for ($count = 0;$count < count($data);$count++) 
        {
            /** A single line of text */
            $line_text = trim($data[$count]);
            /** The 'ã' is replaced with 'a' */
            $line_text = str_replace("ã", "a", $line_text);
            $line_text = str_replace("", "", $line_text);
            /** If the line contains non printable characters */
            if (!ctype_print($line_text)) continue;
            /** If the line contains text */
            if ($line_text != "" && !$text_started) 
            {
                $text_started = true;
                /** The source is set */
                $source = $line_text;
                /** The counter is increased */
                $count++;
                /** The next line is read */
                $line_text = $data[$count];
                /** If the book name is given in the next line */
                if (strpos($line_text, "Book") !== false || strpos($line_text, "Chapter") !== false) 
                {
                    $book_name = $line_text;
                }
                /** If the book name is not given in the next line */
                else 
                {
                    $book_name = $source;
                    $count--;
                }
            }
            /** If the book name and book source have been parsed */
            else 
            {
                /** The line is checked for narrator */
                preg_match("/([a-zA-Z0-9]{2,5}) : ([a-zA-Z0-9]{2,5}) : (.+)/i", $line_text, $matches);
                /** If the narrator was found */
                if (isset($matches[3])) 
                {
                    /** If the hadith text data is set */
                    if (isset($hadith_text_information['hadith_text'])) 
                    {
                        $ahadith_text_data[] = $hadith_text_information;
                        /** The hadith text information. It contains data for single hadith */
                        $hadith_text_information = array();
                    }
                    $hadith_text_information['book_number'] = $matches[1];
                    $hadith_text_information['hadith_number'] = $matches[2];
                    $hadith_text_information['title'] = $matches[3];
                }
                else
                {
                    /** 
                     * If the line does not contain page number and it does not contain url www.hadithcollection.com
                     * and the length of the string is greater than 2, then it is added to the hadith text list
                     */
                    if (strpos($line_text, "Page") === false && strpos($line_text, "www.hadithcollection.com") === false && ctype_print($line_text)) 
                    {
                        if (!isset($hadith_text_information['hadith_text'])) $hadith_text_information['hadith_text'] = array(
                            $line_text
                        );
                        else $hadith_text_information['hadith_text'][] = $line_text;
                    }
                }
            }
        }
        /** The last ahadith in the file is added */
        $ahadith_text_data[] = $hadith_text_information;
        /** The ahadith data is set */
        $ahadith_book_data = array(
            "source" => $source,
            "book_name" => $book_name,
            "text" => $ahadith_text_data
        );
        return $ahadith_book_data;
    }
    /**
     * Used to store the contents of the ahadith data to database
     *
     * It stores each hadith to database
     *
     * @param string $text the hadith text
     * @param string $file_name the hadith text file name
     */
    private function SaveHadithBookEnglish(string $text, string $file_name) 
    {
        /** The text is parsed line by line */
        $ahadith_book_data = $this->ParseAhadithEnglishBookData($text);
        /** Each hadith data is saved */
        for ($count = 0;$count < count($ahadith_book_data['text']);$count++) 
        {
            /** The application configuration is fetched */
            $configuration = $this->GetConfigObject();
            /** The configuration object is fetched */
            $parameters['configuration'] = $configuration;
            /** The mysql data object is created */
            $mysql_data_object = new MysqlDataObject($parameters);
            /** The mysql table name */
            $table_name = $this->GetConfig("general", "mysql_table_names", "hadith_english");
            /** The table name is set */
            $mysql_data_object->SetTableName($table_name);
            /** The key field is set */
            $mysql_data_object->SetKeyField("id");
            /** The mysql data object is set to read/write */
            $mysql_data_object->SetReadOnly(false);
            /** If the hadith data is missing then the file name is displayed */
            if (!isset($ahadith_book_data['text'][$count]['book_number'])) 
            {
                echo $file_name . "\n";
                print_R($ahadith_book_data);
                exit;
            }
            //continue;
            
            /** The hadith data */
            $hadith_data = array(
                "source" => trim($ahadith_book_data['source'], '.'),
                "book" => trim($ahadith_book_data['book_name'], '.'),
                "book_number" => $ahadith_book_data['text'][$count]['book_number'],
                "title" => $ahadith_book_data['text'][$count]['title'],
                "hadith_number" => $ahadith_book_data['text'][$count]['hadith_number'],
                "hadith_text" => implode(" ", $ahadith_book_data['text'][$count]['hadith_text'])
            );
            /** The hadith data is set to the MysqlDataObject */
            $mysql_data_object->SetData($hadith_data);
            /** The mysql data is saved to database */
            $mysql_data_object->Save();
            /** The user is informed that data is saved to database */
            echo "Saved Hadith : " . $ahadith_book_data['text'][$count]['book_number'] . ":" . $ahadith_book_data['text'][$count]['hadith_number'] . " to database\n";
        }
    }
    /**
     * Used to convert Hadith pdf files to text files
     *
     * It uses pdftotext application for converting pdf file to text file
     * The text file is saved in same folder as the pdf file
     */
    public function ConvertPdfFiles() : void
    {
        /** The pdf folder path */
        $folder_path = $this->GetConfig("path", "application_path") . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "ahadith" . DIRECTORY_SEPARATOR . "urdu";
        /** The ahadith folder is read */
        $file_list = $this->GetComponent("filesystem")->GetFolderContents($folder_path, true, ".pdf", ".txt");
        /** Each pdf file is converted to text file */
        for ($count = 0;$count < count($file_list);$count++) 
        {
            /** The source file name */
            $source_file_name = $file_list[$count];
            /** The destination file name */
            $destination_file_name = str_replace(".pdf", ".txt", $source_file_name);
            /** The current file name */
            echo "Converting file: " . $source_file_name . "\n";
            /** The pdf file is converted to txt file using pdftotext tool */
            exec('pdftotext "' . $source_file_name . '" "' . $destination_file_name . '"');
        }
    }
    /**
     * Used to download Hadith data
     *
     * It downloads hadith data from hadithcollection.com
     */
    public function DownloadHadithData() : void
    {
        $hadith_list = array(
            "Abu_Dawud" => "http://www.hadithcollection.com/download-abu-dawud.html~36",
            "Authentic_Supplications" => "http://www.hadithcollection.com/download-authentic-supplications-of-rasulullah.html~1",
            "Hadith_Qudsi" => "http://www.hadithcollection.com/download-hadith-qudsi.html~1",
            "Imam_Nawawi_Hadith" => "http://www.hadithcollection.com/download-an-nawawis-40-hadith.html~1",
            "Maliks_Muwatta" => "http://www.hadithcollection.com/download-maliks-muwatta.html~61",
            "Sahih_Bukhari" => "http://www.hadithcollection.com/download-sahih-bukhari.html~93",
            "Sahih_Muslim" => "http://www.hadithcollection.com/download-sahih-muslim.html~43",
            "Tirmidhi" => "http://www.hadithcollection.com/download-shama-il-tirmidhi.html~55"
        );
        /** Each hadith collection is downloaded to separate folder */
        foreach ($hadith_list as $folder_name => $download_details) 
        {
            /** The hadith collection base url and number of files to download */
            list($url, $file_count) = explode("~", $download_details);
            /** All the pages are downloaded */
            for ($count = 0;$count < ceil($file_count / 15);$count++) 
            {
                /** The page download url */
                $download_url = $url . "?start=" . ($count * 15);
                /** The page is downloaded */
                $page_contents = file_get_contents($download_url);
                /** The pdf links in the page are extracted */
                preg_match_all('/<a class="" href="(.+)">(.+pdf)<\/a>/iU', $page_contents, $matches);
                /** Each extracted link is downloaded */
                for ($count1 = 0;$count1 < count($matches[0]);$count1++) 
                {
                    /** The name of the pdf file */
                    $file_name = $matches[2][$count1];
                    /** The download url of the pdf file */
                    $pdf_file_url = "http://hadithcollection.com" . $matches[1][$count1];
                    /** The pdf file is downloaded and its contents are saved to the folder */
                    $file_contents = file_get_contents($pdf_file_url);
                    /** The pdf folder path */
                    $folder_path = $this->GetConfig("path", "application_path") . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "ahadith" . DIRECTORY_SEPARATOR . "english" . DIRECTORY_SEPARATOR . $folder_name;
                    /** The absolute file path */
                    $file_path = $folder_path . DIRECTORY_SEPARATOR . $file_name;
                    /** The file is saved */
                    $fh = fopen($file_path, "w");
                    fwrite($fh, $file_contents);
                    fclose($fh);
                    echo "Downloaded file: " . $file_name . "\n";
                    flush();
                    sleep(2);
                }
            }
        }
    }
}


/** sql queries for importing the book data 

insert into ic_hadith_books_arabic (book, book_number, source) (select book, book_number, source from ic_hadith_arabic where source='صحیح بخاری' group by book);
insert into ic_hadith_books_arabic (book, book_number, source) (select book, book_number, source from ic_hadith_arabic where source='سنن ترمذی' group by book);
insert into ic_hadith_books_arabic (book, book_number, source) (select book, book_number, source from ic_hadith_arabic where source='سنن ابن ماجہ' group by book);
insert into ic_hadith_books_arabic (book, book_number, source) (select book, book_number, source from ic_hadith_arabic where source='سنن أبي داود' group by book);
insert into ic_hadith_books_arabic (book, book_number, source) (select book, book_number, source from ic_hadith_arabic where source='سنن نسائی' group by book);

insert into ic_hadith_books_urdu (book, book_number, source) (select book, book_number, source from ic_hadith_urdu where source='صحیح بخاری' group by book);
insert into ic_hadith_books_urdu (book, book_number, source) (select book, book_number, source from ic_hadith_urdu where source='سنن ترمذی' group by book);
insert into ic_hadith_books_urdu (book, book_number, source) (select book, book_number, source from ic_hadith_urdu where source='سنن ابن ماجہ' group by book);
insert into ic_hadith_books_urdu (book, book_number, source) (select book, book_number, source from ic_hadith_urdu where source='سنن أبي داود' group by book);
insert into ic_hadith_books_urdu (book, book_number, source) (select book, book_number, source from ic_hadith_urdu where source='سنن نسائی' group by book);


insert into ic_hadith_books_english (book, book_number, source) (select book, book_number, source from ic_hadith_english where source='Abu Dawud' group by book);
insert into ic_hadith_books_english (book, book_number, source) (select book, book_number, source from ic_hadith_english where source='Authentic Supplications of the Prophet' group by book);
insert into ic_hadith_books_english (book, book_number, source) (select book, book_number, source from ic_hadith_english where source='Hadith Qudsi' group by book);
insert into ic_hadith_books_english (book, book_number, source) (select book, book_number, source from ic_hadith_english where source="An Nawawi's Fourty Hadiths" group by book);
insert into ic_hadith_books_english (book, book_number, source) (select book, book_number, source from ic_hadith_english where source='Maliks Muwatta' group by book);
insert into ic_hadith_books_english (book, book_number, source) (select book, book_number, source from ic_hadith_english where source='Sahih Bukhari' group by book);
insert into ic_hadith_books_english (book, book_number, source) (select book, book_number, source from ic_hadith_english where source='Sahih Muslim' group by book);
insert into ic_hadith_books_english (book, book_number, source) (select book, book_number, source from ic_hadith_english where source='Shamaa-il Tirmidhi' group by book);
*/
