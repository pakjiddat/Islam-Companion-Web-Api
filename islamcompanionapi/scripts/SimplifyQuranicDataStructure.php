<?php

declare(strict_types=1);

namespace IslamCompanionApi\Scripts;

use \Framework\object\MysqlDataObject as MysqlDataObject;

/**
 * This class implements the etl class for the application
 * 
 * It contains functions used to extract transform and load the quranic data into a form that is easier to use by DataObjects
 * 
 * @category   IslamCompanionApi
 * @package    Scripts
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class SimplifyQuranicDataStructure extends \Framework\Object\DataObjectEtl
{
	/**
     * Used to generate the extraction data
     * 
     * It generates the data that will be used in data extraction 
	 * 
	 * @return array $data the data used to extract the main data
     */
    protected function GetExtractionData() : array
	{
		/** The data used to extract the main data */
		$data          = array();
		/** The extraction data for hizb table */
		$hizb_data     = array("select"=>"hindex,sura,aya","table"=>$this->GetConfig("general","mysql_table_names","hizb"),"condition"=>false); 
		/** The extraction data for juzs table */
		$juzs_data     = array("select"=>"jindex,sura,aya","table"=>$this->GetConfig("general","mysql_table_names","juz"),"condition"=>false); 
		/** The extraction data for pages table */
		$pages_data    = array("select"=>"pindex,sura,aya","table"=>$this->GetConfig("general","mysql_table_names","page"),"condition"=>false);
		/** The extraction data for manzils table */
		$manzils_data   = array("select"=>"mindex,sura,aya","table"=>$this->GetConfig("general","mysql_table_names","manzil"),"condition"=>false);
		/** The extraction data for rukus table */
		$rukus_data    = array("select"=>"rindex,sura,aya","table"=>$this->GetConfig("general","mysql_table_names","ruku"),"condition"=>false);
		/** The condition for fetching the ayat data */
		$condition     = array(array("field"=>"file_name","value"=>"a (1).txt","table"=>$this->GetConfig("general","mysql_table_names","aya"),"operation"=>"=","operator"=>""));
		/** The extraction data for ayat table */
		$ayat_data     = array("select"=>"id,surah,ayat","table"=>$this->GetConfig("general","mysql_table_names","aya"),"condition"=>$condition);
		/** The extracted data is set */
		$data          = array($hizb_data, $juzs_data, $pages_data, $manzils_data, $rukus_data, $ayat_data);
		
		return $data;
	}
	
	/**
     * Used to get division data for the ayat
     * 
     * It gets the hizb, juz, page and manzil for the given ayat and sura	       
     * 
	 * @param array $division_data the data for all divisions. it is an array with following keys:
	 *    hizbs => the list of all hizbs	 
	 *    juzs => the list of all juzs 
	 *    pages => the list of all pages
	 *    manzils => the list of all manzils
	 *     
	 * @param array $ayat the ayat data. it is an array with following keys:
	 *    surah => the sura id
	 *    ayat => the sura ayat id
	 * 
	 * @return array $ayat_division_data the division data for the given ayat. it is an array with 4 keys:
	 *    hizb => the hizb number of the ayat
	 *    juz => the juz number of the ayat
	 *    page => the page number of the ayat
	 *    manzil => the manzil number of the ayat	 
     */
    public function GetDivisionData(array $division_data, array $ayat) : array
	{
		/** The ayat division data */
		$ayat_division_data                                   = array();
		/** For each division the data in the division is checked and the correct division is returned */
		foreach ($division_data as $division => $data) {
			/** The list of fields in the division */
			$division_field_list                              = array_keys($data[0]);
			/** The index field name of the division */
			$division_index_field_name                        = $division_field_list[0];
	        /** The total number of division numbers in the current division */
		    $total_divison_count                              = count($data);
			/** The sura division id. it is the division number within the given sura */
			$sura_division_id                                 = 0;
			/** The division number for the given division and ayat */
			$division_number                                  = -1;	
		    for ($count1 = 0; $count1 < $total_divison_count; $count1++) {
			    /** The data for single division number */
			    $division_number_data                         = $data[$count1];			
				/** The data for next division number */
			    $next_division_number_data                    = (isset($data[$count1+1]))?($data[$count1+1]):"N.A";				
			    /** The division ayat */
			    $division_ayat                                = $division_number_data['aya'];
			    /** The ayat for next division */
			    $next_division_ayat                           = (isset($data[$count1+1]))?$data[$count1+1]['aya']:1;				
				/** The sura for next division */
			    $next_division_sura                           = (isset($data[$count1+1]))?$data[$count1+1]['sura']:1;				
				/** If the division is ruku and ruku sura is equal to the sura for given ayat */
				if ($division == "ruku" && $division_number_data['sura'] == $ayat['surah']) {
				    /** The sura division is increased by 1 */
			        $sura_division_id++;			
				}		
			    /** If the current division is the last division, then the ayat division number is saved */
				if (($count1+1) == $total_divison_count) {
   				    $division_number                          = $division_number_data[$division_index_field_name];				    
				}				
				/** 
				 * If the current division is not the last division
				 * And the sura of the given ayat is greater than or equal to sura of current division 
				 * And the sura of the given ayat is less or equal to sura of next division
				 * Then the ayat division number is saved
				 */
				else if ($ayat['surah'] >= $division_number_data['sura'] && $ayat['surah'] <= $next_division_sura) {
					/** 
					 * If the sura of the given ayat is greater than sura of current division
					 * And sura of given ayat is less than sura of next division
					 * Then the ayat division number is saved
					 */					
					if ($ayat['surah'] > $division_number_data['sura'] && $ayat['surah'] < $next_division_sura)
				        $division_number                      = $division_number_data[$division_index_field_name];
					/** 
					 * If the sura of the given ayat is greater than current division
					 * And sura of given ayat is equal to sura of next division
					 * Then the ayat division number is saved
					 */					
					else if ($ayat['surah'] > $division_number_data['sura'] && $ayat['surah'] == $next_division_sura) {
					/**
					 * If the given ayat is less than next division ayat					 
					 * Then the ayat division number is saved
					 */	
					    if ($ayat['ayat'] < $next_division_number_data['aya'])
					        $division_number                 = $division_number_data[$division_index_field_name];					    				        
					}
					/** 
					 * If the sura of the given ayat is equal to sura of current division
					 * And sura of given ayat is less than sura of next division
					 * Then the ayat division number is saved
					 */					
					else if ($ayat['surah'] == $division_number_data['sura'] && $ayat['surah'] < $next_division_sura)
				        $division_number                     = $division_number_data[$division_index_field_name];
					/** 
					 * If the sura of the given ayat is equal to sura of current division
					 * And sura of given ayat is equal to sura of next division					 
					 * Then the ayat division number is saved
					 */					
					else if ($ayat['surah'] == $division_number_data['sura'] && $ayat['surah'] == $next_division_sura) {
					/**
					 * If the given ayat is greater than or equal to current division ayat
					 * And the given ayat is less than next division ayat
					 * Then the ayat division number is saved
					 */	
					    if ($ayat['ayat'] >= $division_number_data['aya'] && $ayat['ayat'] < $next_division_number_data['aya'])
					        $division_number                 = $division_number_data[$division_index_field_name];
					}
				}			
			    /**
			 	 * If the division number was set
			 	 * Then it is saved
			     * And the sura ruku is also saved
		        */
			    if ($division_number > 0) {
			        $ayat_division_data[$division]          = $division_number;
				    $ayat_division_data["sura_division"]    = $sura_division_id;
				    break;
			    }	
			}					
		    if (!isset($ayat_division_data[$division]))
		        throw new \Exception(ucfirst($division)." number could not be found for the ayat: ".var_export($ayat,true)." division data: ".var_export($ayat_division_data,true));		
		}		

		return $ayat_division_data;
	}

	/**
     * Used to transform the extracted data
     * 
     * It processes the extracted data and transforms it into a suitable form
     */
    protected function Transform() : void
	{
		/** The transformed data */
		$this->transformed_data                      = array();
		/** The transformed data row. it is a single row in the transformed data */
		$transformed_data_row                        = array();
		/** The sura data */
		$ayat_data                                   = $this->extracted_data[5];
		/** The division data */
		$division_data                               = array("hizb"=>$this->extracted_data[0],"juz"=>$this->extracted_data[1],"page"=>$this->extracted_data[2],"manzil"=>$this->extracted_data[3],"ruku"=>$this->extracted_data[4]);
		/** The ayat data is processed */
		for ($count =0; $count < count($ayat_data); $count++) {
			/** The data for one ayat */
			$ayat                                    = $ayat_data[$count];
			$transformed_data_row['ayat_id']         = $ayat['id'];
			$transformed_data_row['ayat_sura_id']    = $ayat['ayat'];
			$transformed_data_row['sura']            = $ayat['surah'];
			/** Used to get the division information for the ayat */
			$ayat_division_data                      = $this->GetDivisionData($division_data,$ayat);
			$transformed_data_row['hizb']            = $ayat_division_data['hizb'];
			$transformed_data_row['juz']             = $ayat_division_data['juz'];
			$transformed_data_row['manzil']          = $ayat_division_data['manzil'];
			$transformed_data_row['page']            = $ayat_division_data['page'];		
			$transformed_data_row['ruku']            = $ayat_division_data['ruku'];
			$transformed_data_row['sura_ruku']       = $ayat_division_data['sura_division'];			
			/** The transformed data row is added to the transformed data array */
			$this->transformed_data[]                = $transformed_data_row;
		}
	}
	
	/**
     * Used to load the extracted data
     * 
     * It loads the data into the destination data source
     */
    protected function Load() : void
	{
		/** The application configuration is fetched */
		$configuration                                 = $this->GetConfigObject();
		/** The mysql data object is created */
		$mysql_data_object                             = new MysqlDataObject();
		/** The application configuration object is set */
		$mysql_data_object->SetConfigObject($configuration);
		/** The mysql table name */
		$table_name                                    = $this->GetConfig("general","mysql_table_names","meta");
		/** The table name is set */
		$mysql_data_object->SetTableName($table_name);
		/** The field name is set */
		$mysql_data_object->SetKeyField("id");
		/** The mysql data object is set to read/write */
		$mysql_data_object->SetReadonly(false);
		/** The transformed data is loaded */
		for ($count = 0; $count < count($this->transformed_data); $count++) {
			/** The transformed data */
			$data                                      = $this->transformed_data[$count];						
			/** The mysql data is loaded */
			$mysql_data_object->Load($data);			
			/** The mysql data is saved to database */
			$mysql_data_object->Save();
		}
	}
}
