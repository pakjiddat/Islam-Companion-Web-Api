<?php

declare(strict_types=1);

namespace IslamCompanionApi\Scripts;

use \Framework\Config\Base as Base;
use \IslamCompanionApi\DataObjects\HolyQuran as HolyQuran;

/**
 * This class implements the test class for the application
 * It contains functions that help in testing the application
 *
 * It contains unit tests for the class
 *
 * @category   IslamCompanionApi
 * @package    Testing
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class LoadTestData extends Base
{
    /**
     * This function is used to load test data
     *
     * It returns the parameters that are needed for unit testing the given function
     * It overrides the parent class function
     *
     * @param string $function_name the name of the function to be tested
     *
     * @return array $test_data the test data for the function
     */
    final public function LoadTestData(string $function_name) : array
    {
        /** The test data that will be used to test the given function */
        $test_data = array();
        /** If the function being tested is TestEtlScript */
        if ($function_name == "TestEtlScript") 
        {
            /** The test data for Etl Script is fetched */
            $test_data = $this->GetEtlScriptTestData();
        }
        return $test_data;
    }
    /**
     * This function is used to get the test data for the Etl Script
     *
     * It generates data that is used for testing the result of the Etl Script
     *
     * @return array $test_data the test data
     */
    public function GetEtlScriptTestData() : array
    {
        /** The required test data */
        $test_data = array();
        /** The language */
        $language = "Urdu";
        /** The narrator */
        $narrator = "Abul A'ala Maududi";
        /** The division */
        $division = "hizb";
        /** The meta data is fetched */
        /** The application configuration is fetched */
        $configuration = $this->GetConfigObject();
        /** The mysql data object is created */
        $holy_quran = new HolyQuran($configuration);
        /** The meta information used to fetch data */
        $meta_information = array(
            "data_type" => "meta",
            "field_name" => "id"
        );
        /** The table name and field name are set */
        $holy_quran->SetMetaInformation($meta_information);
        /** The mysql data object is loaded with data in database */
        $holy_quran->Read("*", false, true);
        /** The mysql data is fetched */
        $table_rows = $holy_quran->GetData();
        /** The list of included division numbers. Used to prevent a single division number from being added to test data multiple times */
        $included_division_numbers = array();
        for ($count = 0;$count < count($table_rows);$count++) 
        {
            /** If the ruku has already been included then it is not added to test data */
            if (in_array($table_rows[$count][$division], $included_division_numbers)) continue;
            /** The row data */
            $row_data = $table_rows[$count];
            $test_data[] = array(
                "language" => $language,
                "narrator" => $narrator,
                "division" => $division,
                "sura" => $row_data['sura'],
                "ruku" => $row_data['ruku'],
                "division_number" => $row_data[$division],
                "ayat" => $row_data['sura_ayat_id'],
                "full_page" => "1",
                "encrypt_response" => "0",
                "custom_code" => "IC_Holy_Quran_Dashboard_Widget.ic_ajax_nonce='0000000000';",
                "action" => "division_number_box"
            );
            $included_division_numbers[] = $row_data[$division];
        }
        return $test_data;
    }
}

