<?php

declare(strict_types=1);

namespace IslamCompanionApi\Scripts;

/**
 * This class implements the backup and restore class for the application
 * 
 * It contains functions used to backup and restore Holy Quran data
 * The data is backed up from a database and restore to a dedicated database
 * The large ayas table is split into smaller tables
 * 
 * @category   IslamCompanionApi
 * @package    Scripts
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class BackupAndRestoreQuranicData extends \Framework\Object\DataObjectBackupRestore
{
	/**
     * Used to restore the Holy Quran data from data files
     * It restores the Holy Quran data using the DataObjectBackupRestore class	
     */
    public function RestoreHolyQuranData() : void
	{
		/** The mysql tables are created in the new database */
		$this->CreateTables();	
		/** The absolute path to the backup folder */
		$backup_folder_path                                = $this->GetConfig("path","data_folder") . DIRECTORY_SEPARATOR . "backup";		
		/** The list of all MySQL tables */
		$mysql_table_list                                  = $this->GetConfig("general","mysql_table_names");
		/** The application configuration */
		$meta_information['configuration']                 = $this->GetConfigObject();					
		/** The mysql table key field */
		$meta_information['key_field']                     = "id";
		/** It indicates that checksum needs to be calculated on the data */
		$meta_information['validate_checksum']             = true;
		/** The database object to use */
		$meta_information['database_object']               = $this->GetComponent("ic_database");			
		/** Each mysql table is backed up */
		foreach ($mysql_table_list as $short_table_name => $mysql_table_name) {			
		    /** The short name for the mysql table */
		    $meta_information['data_type']                 = $short_table_name;			
			/** The object information */
			$object_information                            = array("type" => "\Framework\object\MysqlDataObject", "meta_information" => $meta_information, "parameters" => $parameters);
			/** The path to the backup file */
			$backup_file_path                              = $backup_folder_path . DIRECTORY_SEPARATOR . $mysql_table_name . ".tlv";
			/** The data is restored */
			$this->Restore($object_information, $backup_file_path);
		}		
	}
	
	/**
     * Used to create the ayas table
     * 
     * It creates the structure of the ayas table in the new database	
     */
    public function CreateAyaTable() : void
	{
		/** The absolute path to the original_files folder */
		$original_files_folder_path          = $this->GetConfig("path","data_folder") . DIRECTORY_SEPARATOR . "original_files";
		/** The list of all files in the original folder is fetched */
		$file_list                           = $this->GetComponent("filesystem")->GetFolderContents($original_files_folder_path);			
		/** The list of all MySQL tables given in application configuration */
		$mysql_table_list                    = $this->GetConfig("general","mysql_table_names");
		/** The database object is fetched */
		$database_obj                        = $this->GetComponent("ic_database");
		/** The authors table attributes */
		$authors_table                       = array();
		$authors_table['table_name']         = $mysql_table_list["aya"];
		$authors_table['field_list']         = array(
													array("name"=>"id","type"=>"int (11) NOT NULL"),
													array("name"=>"sura","type"=>"int (11) NOT NULL"),
													array("name"=>"ayat","type"=>"int (11) NOT NULL"),
													array("name"=>"translated_text","type"=>"longtext NOT NULL"),													
													array("name"=>"file_name","type"=>"varchar(20) NOT NULL"),
													array("name"=>"checksum","type"=>"text NOT NULL"),
													array("name"=>"created_on","type"=>"int(11) NOT NULL")													
												   );
		$authors_table['auto_increment']     = array("id int (11)");									  
		$authors_table['primary_key']        = "id";
		$authors_table['unique_indexes']     = array("name"=>"sura","field_list"=>array("sura","ayat","file_name"));
		$authors_table['comment']            = "used to store aya information of the Holy Quran";

		/** A new table is created for each file in the original_folders table */
		for ($count = 0; $count < count($file_list); $count++) {
			/** The name of a file containing quranic data */
            $file_name                       = $file_list[$count];
			/** If the file name does not have .txt extension then the loop continues */
			if (strpos($file_name, ".txt") === false) continue;
			/** The file extension is removed and the resulting string is appended to the table name */
			$file_name                       = str_replace(".txt", "", $file_name);   
		    $table_name                      = $authors_table['table_name']."-".$file_name;  
			
			$database_obj->df_create_table($table_name, $authors_table['field_list'], 
			                               $authors_table['primary_key'], $authors_table['auto_increment'],
			                               $authors_table['unique_indexes'], $authors_table['comment']);
		}										 
	}
	
	/**
     * Used to create the sura table
     * 
     * It creates the structure of the sura table in the new database	
     */
    public function CreateSuraTable() : void
	{
		/** The list of all MySQL tables given in application configuration */
		$mysql_table_list                    = $this->GetConfig("general","mysql_table_names");
		/** The database object is fetched */
		$database_obj                        = $this->GetComponent("ic_database");
		/** The authors table attributes */
		$authors_table                       = array();
		$authors_table['table_name']         = $mysql_table_list["sura"];
		$authors_table['field_list']         = array(
													array("name"=>"id","type"=>"int (11) NOT NULL"),
													array("name"=>"sindex","type"=>"int (11) NOT NULL"),
													array("name"=>"ayas","type"=>"int (11) NOT NULL"),
													array("name"=>"start","type"=>"int (11) NOT NULL"),
													array("name"=>"name","type"=>"varchar(100) CHARACTER SET utf8 NOT NULL"),
													array("name"=>"tname","type"=>"varchar(100) CHARACTER SET utf8 NOT NULL"),
													array("name"=>"ename","type"=>"varchar(100) CHARACTER SET utf8 NOT NULL"),													
													array("name"=>"type","type"=>"varchar(50) CHARACTER SET utf8 NOT NULL"),
													array("name"=>"sorder","type"=>"int(11) NOT NULL"),
													array("name"=>"rukus","type"=>"int(11) NOT NULL"),
													array("name"=>"audiofile","type"=>"varchar(255) NOT NULL"),
													array("name"=>"checksum","type"=>"text NOT NULL"),
													array("name"=>"created_on","type"=>"int(11) NOT NULL"),													
											   );
		$authors_table['auto_increment']     = array("id int (11)");						
		$authors_table['primary_key']        = "id";
		$authors_table['unique_indexes']     = array("name"=>"index","field_list"=>array("sindex"));
		$authors_table['comment']            = "used to store sura information of the Holy Quran";
		
		$database_obj->df_create_table($authors_table['table_name'], $authors_table['field_list'], 
		                               $authors_table['primary_key'], $authors_table['auto_increment'],
		                               $authors_table['unique_indexes'], $authors_table['comment']);		
	}

	/**
     * Used to create the meta table
     * 
     * It creates the structure of the meta table in the new database 	
     */
    public function CreateMetaTable() : void
	{
		/** The list of all MySQL tables given in application configuration */
		$mysql_table_list                    = $this->GetConfig("general","mysql_table_names");
		/** The database object is fetched */
		$database_obj                        = $this->GetComponent("ic_database");
		/** The authors table attributes */
		$authors_table                       = array();
		$authors_table['table_name']         = $mysql_table_list["meta"];
		$authors_table['field_list']         = array(
													array("name"=>"id","type"=>"int (11) NOT NULL"),
													array("name"=>"ayat_id","type"=>"int (11) NOT NULL"),
													array("name"=>"sura_ayat_id","type"=>"int (11) NOT NULL"),
													array("name"=>"sura","type"=>"int (11) NOT NULL"),
													array("name"=>"hizb","type"=>"int (11) NOT NULL"),
													array("name"=>"juz","type"=>"int (11) NOT NULL"),
													array("name"=>"manzil","type"=>"int (11) NOT NULL"),
													array("name"=>"page","type"=>"int (11) NOT NULL"),
													array("name"=>"ruku","type"=>"int (11) NOT NULL"),
													array("name"=>"sura_ruku","type"=>"int (11) NOT NULL"),
													array("name"=>"checksum","type"=>"text NOT NULL")													
											   );
		$authors_table['primary_key']        = "id";
		$authors_table['auto_increment']     = array("`id` int (11)");		
		$authors_table['unique_indexes']     = array();
		$authors_table['comment']            = "used to store meta information of the Quranic text files";
		
		$database_obj->df_create_table($authors_table['table_name'], $authors_table['field_list'], 
		                               $authors_table['primary_key'], $authors_table['auto_increment'],
		                               $authors_table['unique_indexes'], $authors_table['comment']);		
	}
	
	/**
     * Used to create the authors table
     * 
     * It creates the structure of the authors table in the new database
     */
    public function CreateAuthorsTable() : void
	{
		/** The list of all MySQL tables given in application configuration */
		$mysql_table_list                    = $this->GetConfig("general","mysql_table_names");
		/** The database object is fetched */
		$database_obj                        = $this->GetComponent("ic_database");
		/** The authors table attributes */
		$authors_table                       = array();
		$authors_table['table_name']         = $mysql_table_list["author"];
		$authors_table['field_list']         = array(
													array("name"=>"id","type"=>"int (11) NOT NULL"),
													array("name"=>"file_name","type"=>"varchar (20) NOT NULL"),
													array("name"=>"name","type"=>"varchar (255) NOT NULL"),
													array("name"=>"translator","type"=>"varchar (11) NOT NULL"),
													array("name"=>"language","type"=>"varchar (11) NOT NULL"),
													array("name"=>"file_id","type"=>"varchar (100) NOT NULL"),
													array("name"=>"last_update","type"=>"varchar (30) NOT NULL"),
													array("name"=>"source","type"=>"varchar (100) NOT NULL"),
													array("name"=>"rtl","type"=>"int (11) NOT NULL"),
													array("name"=>"css_attributes","type"=>"varchar (255) NOT NULL"),
													array("name"=>"dictionary_url","type"=>"varchar (255) NOT NULL"),
													array("name"=>"checksum","type"=>"text NOT NULL"),
													array("name"=>"created_on","type"=>"int (11) NOT NULL")
											   );
		$authors_table['primary_key']        = "id";
		$authors_table['auto_increment']     = array("id int(11)");		
		$authors_table['unique_indexes']     = array("name"=>"file_name","field_list"=>array("file_name"));
		$authors_table['comment']            = "used to store narrator and language information of the Quranic text files";
		
		$database_obj->df_create_table($authors_table['table_name'], $authors_table['field_list'], 
		                               $authors_table['primary_key'], $authors_table['auto_increment'],
		                               $authors_table['unique_indexes'], $authors_table['comment']);		
	}
		
	/**
     * Used to create the database tables in the new database
     * 
     * It creates the structure of the backedup tables in the new database
     */
    public function CreateTables() : void
	{
		/** The authors table is created */
		$this->CreateAuthorsTable();
		/** The meta table is created */
		$this->CreateMetaTable();
		/** The sura table is created */
		$this->CreateSuraTable();
		/** The aya table is created */
		$this->CreateAyaTable();
	}
	
	/**
     * Used to backup the Holy Quran data to data files
     * 
     * It backs up the Holy Quran data using the DataObjectBackupRestore class	
     */
    public function BackupHolyQuranData() : void
	{
		/** The maximum number of rows per file */
		$max_rows_per_file                                 = 6236;
		/** The absolute path to the backup folder */
		$backup_folder_path                                = $this->GetConfig("path","data_folder") . DIRECTORY_SEPARATOR . "backup";		
		/** The list of all MySQL tables */
		$mysql_table_list                                  = $this->GetConfig("general","mysql_table_names");
		/** The application configuration */
		$meta_information['configuration']                 = $this->GetConfigObject();					
		/** The mysql table key field */
		$meta_information['key_field']                     = "id";
		/** The database object to use */
		$meta_information['database_object']               = $this->GetComponent("database"); 
		/** It indicates that checksum needs to be calculated on the data */
		$meta_information['validate_checksum']             = true;
		/** The parameters used to read the data from database */			
		$parameters                                        = array("fields"=>"*", "condition" => false, "read_all" => true);
		/** Each mysql table is backed up */
		foreach ($mysql_table_list as $short_table_name => $mysql_table_name) {
		    /** The short name for the mysql table */
		    $meta_information['data_type']                 = $short_table_name;			
			/** The object information */
			$object_information                            = array("type" => "\Framework\object\MysqlDataObject", "meta_information" => $meta_information, "parameters" => $parameters);
			/** The path to the backup file */
			$backup_file_path                              = $backup_folder_path . DIRECTORY_SEPARATOR . $mysql_table_name . ".tlv";
			/** The data is backed up */
			$this->Backup($object_information, $backup_file_path, $max_rows_per_file);
		}		
	}	
}
