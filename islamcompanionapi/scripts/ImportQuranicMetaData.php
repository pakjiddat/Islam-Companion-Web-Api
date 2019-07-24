<?php

declare(strict_types=1);

namespace IslamCompanionApi\Scripts;

use \Framework\Testing\Testing as Testing;
use \Framework\DataAbstraction\MysqlDataObject as MysqlDataObject;
/**
 * This class implements the import quranic meta data script
 *
 * It contains functions used to import quranic meta data
 *
 * @category   IslamCompanionApi
 * @package    Scripts
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class ImportQuranicMetaData extends Testing
{
    /**
     * Used to correct the sura names in database
     *
     * It reads Holy Quran sura names in Arabic from quran-data.xml file
     * It updates the name field in ic_quranic_suras_meta table
     */
    private function LoadXMLFile() : void
    {
        /** The full path to the Quran meta data xml file */
        $quran_meta_data_file_name = $this->GetConfig("path", "application_folder") . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "quran-data.xml";
        /** The Quran Data xml file is read */
        $xml_data = simplexml_load_file($quran_meta_data_file_name);
        return $xml_data;
    }
    /**
     * Used to import division data to database
     *
     * It saves the meta data for given division to database
     *
     * @param string $division_name name of the division whoose meta data is to be imported
     * @param array $xml_data the quranic meta data
     */
    private function ImportDivisionData(string $division_name, array $xml_data) : void
    {
        for ($count = 0;$count < count($xml_data->juz);$count++) 
        {
            $juz = (array)$xml_data->juz[$count];
            $juz_attributes = $juz['@attributes'];
            $index = $juz_attributes['index'];
            $sura = $juz_attributes['sura'];
            $aya = $juz_attributes['aya'];
            $insert_str = "INSERT INTO ic_quranic_juzs_meta(jindex,sura,aya,created_on) VALUES('" . mysql_escape_string($index) . "','" . mysql_escape_string($sura) . "','" . mysql_escape_string($aya) . "','" . mysql_escape_string(time()) . "')";
            mysql_query($insert_str);
        }
    }
    /**
     * Used to import sura meta data
     *
     * It saves the sura data to database
     *
     * @param array $xml_data the quranic meta data
     */
    private function ImportSuraData(array $xml_data) : void
    {
        for ($count = 0;$count < count($xml_data->sura);$count++) 
        {
            $sura = (array)$xml_data->sura[$count];
            $sura_attributes = $sura['@attributes'];
            $index = $sura_attributes['index'];
            $ayas = $sura_attributes['ayas'];
            $start = $sura_attributes['start'];
            $name = $sura_attributes['name'];
            $tname = $sura_attributes['tname'];
            $ename = $sura_attributes['ename'];
            $type = $sura_attributes['type'];
            $order = $sura_attributes['order'];
            $rukus = $sura_attributes['rukus'];
            $insert_str = "INSERT INTO ic_quranic_suras_meta(sindex,ayas,start,name,tname,ename,type,sorder,rukus,created_on) VALUES('" . mysql_escape_string($index) . "','" . mysql_escape_string($ayas) . "','" . mysql_escape_string($start) . "','" . mysql_escape_string($name) . "','" . mysql_escape_string($tname) . "','" . mysql_escape_string($ename) . "','" . mysql_escape_string($type) . "','" . mysql_escape_string($order) . "','" . mysql_escape_string($rukus) . "','" . mysql_escape_string(time()) . "')";
            mysql_query($insert_str);
        }
    }
    /**
     * Used to correct the sura names in database
     *
     * It reads Holy Quran sura names in Arabic from quran-data.xml file
     * It updates the name field in ic_quranic_suras_meta table
     */
    public function UpdateSuraNamesInDatabase() : void
    {
        /** The application configuration is fetched */
        $configuration = $this->GetConfigObject();
        /** The configuration object is fetched */
        $parameters['configuration'] = $configuration;
        /** The mysql data object is created */
        $mysql_data_object = new MysqlDataObject($parameters);
        /** The mysql table name */
        $table_name = $this->GetConfig("general", "mysql_table_names", "sura");
        /** The table name is set */
        $mysql_data_object->SetTableName($table_name);
        /** The key field is set */
        $mysql_data_object->SetKeyField("id");
        /** The parameters used to read the data from database */
        $parameters = array(
            "fields" => "*",
            "condition" => false,
            "read_all" => true
        );
        /** The mysql data object is loaded with data from database */
        $mysql_data_object->Read($parameters);
        /** The mysql data is fetched */
        $data = $mysql_data_object->GetData();
        /** The mysql data object is set to read/write */
        $mysql_data_object->SetReadOnly(false);
        /** The Holy Quran meta data */
        $quran_meta_data = $this->LoadXMLFile();
        /** The sura meta data */
        $sura_meta_data = (array)$quran_meta_data->suras;
        /** For each sura meta data, the sura name in Arabic is saved to database */
        for ($count = 0;$count < count($sura_meta_data['sura']);$count++) 
        {
            /** The meta data for a single sura */
            $sura = (array)$sura_meta_data['sura'][$count];
            $sura_attributes = $sura['@attributes'];
            /** The sura index value */
            $index = $sura_attributes['index'];
            /** The sura name in Arabic */
            $name = $sura_attributes['name'];
            /** The sura name in English */
            $tname = $sura_attributes['tname'];
            /** The sura data in database */
            $sura_data = $data[$count];
            /** The sura data is set to the MysqlDataObject */
            $mysql_data_object->SetData($sura_data);
            /** The sura Arabic name is updated */
            $mysql_data_object->Edit("name", $name);
            /** The sura English name is updated */
            $mysql_data_object->Edit("tname", $tname);
            /** The mysql data is saved to database */
            $mysql_data_object->Save();
        }
    }
    /**
     * Used to correct the author names in database
     *
     * It reads the author data in database
     * It updates the name field in ic_quranic_author_meta table so it is utf8 encoded
     * It also sets the value of the checksum field
     */
    public function UpdateAuthorNamesInDatabase() : void 
    {
        /** The application configuration is fetched */
        $configuration = $this->GetConfigObject();
        /** The configuration object is fetched */
        $parameters['configuration'] = $configuration;
        /** The database object is fetched */
        $parameters['database_object'] = $this->GetComponent("database");
        /** Used to indicate if the checksum should be validated */
        $parameters['validate_checksum'] = false;
        /** The data type is set to authors */
        $parameters['data_type'] = "author";
        /** The key field is set to id */
        $parameters['key_field'] = "id";
        /** The mysql data object is created */
        $mysql_data_object = new MysqlDataObject($parameters);
        /** The mysql table name */
        $table_name = $this->GetConfig("general", "mysql_table_names", "author");
        /** The table name is set */
        $mysql_data_object->SetTableName($table_name);
        /** The key field is set */
        $mysql_data_object->SetKeyField("id");
        /** The parameters used to read the data from database */
        $parameters = array(
            "fields" => "*",
            "condition" => false,
            "read_all" => true
        );
        /** The mysql data object is loaded with data from database */
        $mysql_data_object->Read($parameters);
        /** The mysql data is fetched */
        $data = $mysql_data_object->GetData();
        /** The mysql data object is set to read/write */
        $mysql_data_object->SetReadOnly(false);
        /** For each author meta data, the author name in Arabic is saved to database */
        for ($count = 0;$count < count($data);$count++) 
        {
            /** The author data in database */
            $author_data = $data[$count];
            /** The file name */
            $file_name = $author_data['file_name'];
            if ($file_name == "quran-simple.txt") continue;
            /** The text file containing author data is read */
            $line_arr = file($this->GetConfig("path", "application_path") . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "original_files" . DIRECTORY_SEPARATOR . $file_name);
            /** The author section is extracted from the text file */
            list($temp_str, $name) = explode(":", $line_arr[count($line_arr) - 8]);
            /** The translator section is extracted from the text file */
            list($temp_str, $translator) = explode(":", $line_arr[count($line_arr) - 7]);
            /** The author data is sorted */
            ksort($author_data, SORT_STRING);
            /** The checksum field is removed from the author row */
            unset($author_data['checksum']);
            /** The base64 encoding of the data is combined */
            $combined_field_values = "";
            /** The author data values */
            $author_data_values = array_values($author_data);
            /** Each field value is encoded and combined */
            for ($count1 = 0;$count1 < count($author_data_values);$count1++) 
            {
                /** The field values in the author row are combined */
                $combined_field_values = $combined_field_values . base64_encode($author_data_values[$count1]);
            }
            /** The author data is set to the MysqlDataObject */
            $mysql_data_object->SetData($author_data);
            /** The data in the checksum field is set */
            $mysql_data_object->Edit("checksum", md5($combined_field_values));
            /** The author Arabic name is updated */
            //$mysql_data_object->Edit("name", trim($name));
            /** The translator name is updated */
            //$mysql_data_object->Edit("translator", trim($translator));
            /** The data is saved to database */
            $mysql_data_object->Save();
            /** SQL query used to set the checksum of ayat table */
			/** update `ic_quranic_text-e (10)` set checksum=md5(concat(TO_BASE64(created_on), TO_BASE64(id), TO_BASE64(sura), TO_BASE64(sura_ayat_id), replace(TO_BASE64(translated_text), "\n", ""))) */
        }
    }
    /**
     * Used to correct the checksum field in the ayat tables
     *
     * It reads list of all database tables
     * For each ayat table, the script runs a sql query which sets the checksum field
     */
    public function UpdateAyatTableChecksum() : void
    {
    	/** The number of updated tables */
    	$updated_table_count = 0;
    	/** The list of all tables in database are fetched */
    	$table_list       = $this->GetComponent("database")->GetTableList();
    	/** Each table is checked */
    	foreach ($table_list as $table_name) {
    		/** If the table name starts with 'ic_quranic_text-' */
    		if (strpos($table_name['Tables_in_dev_pakjiddat'], 'ic_quranic_text-') === 0) {
    		
    			/** If the table name is 'ic_quranic_text-quran-simple' */
    			if ($table_name['Tables_in_dev_pakjiddat'] != 'ic_quranic_text-quran-simple') {
    				/** The sql query for setting the checksum */
    				$update_str = 'update `' . $table_name['Tables_in_dev_pakjiddat'] . '` set checksum=md5(concat(TO_BASE64(created_on), TO_BASE64(id), TO_BASE64(sura), TO_BASE64(sura_ayat_id), replace(TO_BASE64(' . $text_field . '), "\n", "")))';
    			}
    			else {
    				/** The sql query for setting the checksum */
	    			$update_str = 'update `' . $table_name['Tables_in_dev_pakjiddat'] . '` set checksum=md5(concat(replace(TO_BASE64(arabic_text), "\n", ""), TO_BASE64(created_on), TO_BASE64(id), TO_BASE64(sura), replace(TO_BASE64(sura_ayat_id), "\n", "")))';
    			}

    			/** The query is run */
    			$this->GetComponent("database")->Execute($update_str);
    			/** The number of tables updated is increased by 1 */
    			$updated_table_count++;
    		}
    	}
    	echo "\n\n" . $updated_table_count . " ayat tables were updated";
    }
}
?>
