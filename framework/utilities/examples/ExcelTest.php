<?php

declare(strict_types=1);

namespace Framework\Utilities\Examples;

use Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

error_reporting(E_ALL);
ini_set("display_errors", "1");
include('../autoload.php');

/**
 * Provides functions for test Excel package
 *
 * @category   UtilityClassTests
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
final class ExcelTest
{
	/**
     * Excel file test
     * Used to test excel utility object functions
     */
    public function TestExcel() : void
    {
        /** The PhpExcel library is included */
        $excel_file_path = "/var/www/html/wulfmansworld/current/vendors/phpexcel/Classes/PHPExcel/IOFactory.php";
        /** If the file does not exist, then an error message is shown */
        if (!is_file($excel_file_path)) 
        {
            echo "Please enter the path to the PHPExcel IOFactory.php file!";
            return;
        }
        /** The excel file in included */
        include_once ($excel_file_path);
        
        /** The Excel class object is fetched */
        $excel_obj = UtilitiesFramework::Factory("excel");
        /** The excel file is read */
        $data_arr  = $excel_obj->ReadExcelFile("/var/www/html/pakjiddat/islamcompanion/framework/utilities/examples/test.xls", "A2", "C13");
        
        var_export($data_arr);
    }
}

/** The ExcelTest class object is created */
$exceltest = new ExcelTest();
/** The TestExcel function is called */
$exceltest->TestExcel();
