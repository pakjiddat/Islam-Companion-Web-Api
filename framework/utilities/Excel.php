<?php

declare(strict_types=1);

namespace Framework\Utilities;

/**
 * This class provided functions for working with Excel files
 * It uses PhpExcel library (https://github.com/PHPOffice/PHPExcel)
 *
 * @category   UtilityClass
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
final class Excel
{    
    /** @var Excel $instance The single static instance */
    protected static $instance;
    
    /**
     * Used to return a single instance of the class
     * 
     * Checks if instance already exists. If it does not exist then it is created. The instance is returned
     * 
     * @param array $parameters an array containing class parameters	 		
     *  
     * @return Excel static::$instance name the instance of the correct child class is returned 
     */
    public static function GetInstance(array $parameters) : Excel
    {
        if (static::$instance == null) {
            static::$instance = new static();
        }
        return static::$instance;
    }
    
    /**
     * Reads the contents of an excel file
     * 
     * This function reads the contents of the active excel file worksheet
     * It reads the contents of the cells given by the start_cell and end_cell parameters
     * It returns the data as an array
     *
     * @param string $file_path absolute path to the excel file to be read
     * @param string $start_cell the cell co-ordinates of start cell. e.g A1
     * @param string $end_cell the cell co-ordinates of end cell. e.g F6
     * 		  
     * @return array returns the contents of the excel file
     */
    public function ReadExcelFile(string $file_path, string $start_cell, string $end_cell) : array
    {
        $input_file_type = 'Excel5';
        $input_file_name = $file_path;
        /**  Create a new Reader of the type defined in $inputFileType  */
        $excel_reader    = \PHPExcel_IOFactory::createReader($input_file_type);
        /**  Advise the Reader that we only want to load cell data  */
        $excel_reader->setReadDataOnly(true);
        /**  Load $inputFileName to a PHPExcel Object  */
        $excel_file_obj = $excel_reader->load($input_file_name);
        /** Read data from worksheet */
        $excel_data     = $excel_file_obj->getActiveSheet()->rangeToArray($start_cell . ':' . $end_cell, // The worksheet range that we want to retrieve
            NULL, // Value that should be returned for empty cells
            TRUE, // Should formulas be calculated (the equivalent of getCalculatedValue() for each cell)
            TRUE, // Should values be formatted (the equivalent of getFormattedValue() for each cell)
            TRUE // Should the array be indexed by cell row and cell column
            );
        return $excel_data;
    }
    
}
