<?php

declare(strict_types=1);

namespace IslamCompanionApi\Scripts;

use \Framework\Testing\Testing as Testing;
use \IslamCompanionApi\DataObjects\HolyQuran as HolyQuran;
use \Framework\Object\MysqlDataObject as MysqlDataObject;

/**
 * This class implements the import quranic verses script
 * 
 * It contains functions used to import quranic data from text files to MySQL database
 * 
 * @category   IslamCompanionApi
 * @package    Scripts
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class ImportQuranicVerses extends Testing
{
	/**
     * Used to drop quranic verse table fields
     * 
     * It drops the file_name field in all verse tables	 
     */
    public function DropFileNameColumnInVerseTables() : void
	{
		/** The database object is fetched */
		$database_obj = $this->GetComponent("database");
		/** The list of all table names is fetched */
		$table_list   = $database_obj->df_get_table_list();
		/** Each table is checked */
		for ($count = 0; $count < count($table_list); $count++) {
			/** The table name */
			$table_name = $table_list[$count]['Tables_in_ic_holy_quran_data'];
			/** If the table is not a verse table, then the loop continues */
			if (strpos($table_name, "ic_quranic_text") === false) continue;
			/** If the table is a verse table then the file_name column is dropped */
			else {
				$database_obj->df_drop_column($table_name, "file_name");
			}
		}
	}
	
	/**
     * Used to rename the surah field in all the verse tables
     * 
     * It fetches the list of all verse tables in the quranic database
	 * For each table in the database, it renames the surah column to sura	 
     */
    public function RenameVerseTables() : void
	{
		/** The database object is fetched */
		$database_obj = $this->GetComponent("database");
		/** The list of all table names is fetched */
		$table_list   = $database_obj->df_get_table_list();
		/** Each table is checked */
		for ($count = 0; $count < count($table_list); $count++) {
			/** The table name */
			$table_name = $table_list[$count]['Tables_in_ic_holy_quran_data'];
			/** If the table is not a verse table, then the loop continues */
			if (strpos($table_name, "ic_quranic_text") === false) continue;
			/** If the table is a verse table then the surah column is renamed to sura */
			else {
				$database_obj->df_rename_column($table_name, "ayat", "sura_ayat_id", "int (11) NOT NULL");
			}
		}
	}
	
	/**
     * Used to return the number of rows in the given verse table
     * 
     * It returns the number of rows in the given verse table
     *
	 * @param string $table_name the name of the verse table
	 * @param string $file_name the name of the verse text file
	 * @param MysqlDataObject $mysql_data_object the mysql data object
	 * 
	 * @return int $row_count the number of rows in the verse table
     */
    private function GetTableRowCount(string $table_name, string $file_name, MysqlDataObject $mysql_data_object) : int
	{
		/** The table name is set */
		$mysql_data_object->SetTableName($table_name);
		/** The key field is set */
		$mysql_data_object->SetKeyField("file_name");
		/** The parameters used to read the data from database */
		$parameters                                    = array("fields" => "count(*) as total",
		                                                       "condition" => $file_name,
		                                                       "read_all" => false
		                                                  );
		/** The mysql data object is loaded with data from database */
		$mysql_data_object->Read($parameters);
		/** The mysql data is fetched */
		$data                                          = $mysql_data_object->GetData();				
		/** The total number of verses */
		$row_count                                     = $data['total'];
		
		return $row_count;
	}
	
	/**
     * Used to import Quranic data from text files to MySQL database
     * 
     * It reads Holy Quran data from text files and imports the data to MySQL database  
	 * 
	 * @return string $verse_text the Holy Quran verse text
     */
    public function ImportTextFiles() : string
	{
		/** The required translation language */
		$required_language                                      = "All";
		/** The full path to the data directory */
		$data_directory                                         = $this->GetConfig("path","application_path"). DIRECTORY_SEPARATOR. "data". DIRECTORY_SEPARATOR. "original_files";
		/** The list of files in the original_text folder */
		$file_list                                              = scandir($data_directory);		
		/** The mysql table name */
		$table_name                                             = $this->GetConfig("general","mysql_table_names","aya");				
		/** The application configuration is fetched */
		$configuration                                          = $this->GetConfigObject();
		/** The application configuration */
		$meta_information['configuration']                      = $configuration;					
		/** The mysql table key field */
		$meta_information['key_field']                          = "id";
		/** It indicates that checksum needs to be calculated on the data */
		$meta_information['validate_checksum']                  = false;
		/** The database object to use */
		$meta_information['database_object']                    = $this->GetComponent("ic_database");
		/** The short name for the mysql table */
		$meta_information['data_type']                          = "aya";
		/* Data is imported from each text file */													
		for($count1 = 0 , $counter = 0; $count1 < count($file_list); $count1++)
			{
				$file_name                                      = $file_list[$count1];
				/** If the file name does not have .txt extension or the file name is "." or ".." then the loop continues */
				if (strpos($file_name, ".txt") === false || $file_name == "." || $file_name == "..") continue;
				/** 
				 * The string that will be added to the mysql table name
				 * The file extension is removed and the resulting string is appended to the table name
				 */
				$table_name_extension                           = str_replace(".txt", "", $file_name);   
				/** The mysql table name to which the data will be imported */
				$translation_table_name                         = $table_name."-".$table_name_extension;
				/** The mysql data object is created */
				$mysql_data_object                              = new MysqlDataObject($meta_information);
				/** The total number of rows in the given table */
				$total                                          = $this->GetTableRowCount($translation_table_name, $file_name, $mysql_data_object);
				/** If the total number of rows in the table is equal to the total number of verses then the next file is imported */
				if ($total == HolyQuran::GetMaxDivisionCount("ayas")) continue;			
				/** The table name is set */
				$mysql_data_object->SetTableName($translation_table_name);							
				/** The number of lines in the footer */
				$footer_line_count                             = ($file_name == "quran-simple.txt")?30:13;
				/** The text file is read. Each line is saved as array element */
				$line_arr                                      = file($data_directory . DIRECTORY_SEPARATOR . $file_name);												
				/** The mysql data object is set to read/write */
				$mysql_data_object->SetReadonly(false);
				/** The key field is set */
				$mysql_data_object->SetKeyField("id");
				/** The data is added to the authors table */
				/*list($temp_str,$name)=explode(":",$line_arr[count($line_arr)-8]);
				list($temp_str,$translator)=explode(":",$line_arr[count($line_arr)-7]);
				list($temp_str,$language)=explode(":",$line_arr[count($line_arr)-6]);
				list($temp_str,$id)=explode(":",$line_arr[count($line_arr)-5]);
				list($temp_str,$last_update)=explode(":",$line_arr[count($line_arr)-4]);
				list($temp_str,$source)=explode(":",$line_arr[count($line_arr)-3]);
				
				if($required_language!=$language&&$required_language!="All")continue;
				
				$insert_str="INSERT INTO ic_quranic_author_meta(file_name,name,translator,language,file_id,last_update,source,created_on) VALUES('".mysql_escape_string($file_name)."','".mysql_escape_string($name)."','".mysql_escape_string($translator)."','".mysql_escape_string($language)."','".mysql_escape_string($id)."','".mysql_escape_string($last_update)."','".mysql_escape_string($source)."','".(time())."')";
				if(!mysql_query($insert_str))die("<b>Error in executing following mysql query:</b> ".$insert_str."<br/><b>Error:</b>".mysql_error());*/
		
				/** Each line in the text file is added to database */
				for($count2 = 0; $count2 < (count($line_arr)-$footer_line_count); $count2++)
					{
						/** The newline character is removed and the line is split on '|' character */
						list($surah,$ayat,$text)                = explode("|",trim($line_arr[$count2]));
						/** The checksum for the data fields */
						$checksum                               = md5(($count2+1).$surah.$ayat.$text);
						/** The formatted data */
						$formatted_data                         = array("sura"=>$surah,"ayat"=>$ayat,
						                                                "translated_text"=>$text,"file_name"=>$file_name,
						                                                "created_on"=>time(),"checksum"=>$checksum);						
						/** The data is set to the object */
						$mysql_data_object->SetData($formatted_data);																		
						/** The data is saved to database */
						$mysql_data_object->Save();
					}		
				/** The total number of rows in the given table */
				$total                                         = $this->GetTableRowCount($translation_table_name, $file_name, $mysql_data_object);
				/** Used to check if the total number of rows in the table is equal to the number of verse lines in the text file */
				$this->AssertEqual($total,(count($line_arr)-$footer_line_count));
				/** Checks if the total number of row in the table is equal to total number of verses */
				$this->AssertEqual($total,HolyQuran::GetMaxDivisionCount("ayas"));
				/** The table counter is increased by 1 */
				$counter++;
				
				echo $counter.") Data was successfully added to the table: ".$translation_table_name."\n";
				if ($counter == 5) break;
			}
	}
}
?>
