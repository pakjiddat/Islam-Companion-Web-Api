<?php

declare(strict_types=1);

namespace Framework\Utilities\Examples;

use \Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

error_reporting(E_ALL);
ini_set("display_errors", "1");
include('../autoload.php');

/**
 * Provides functions for test database package
 *
 * @category   UtilityClassTests
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
final class DatabaseTest
{
	/**
     * Used to test the Database class
     */
    public function TestPdoAbstraction() : void
    {
        /** The database object is fetched */
        $connection_info     = array(
									"dsn" => "mysql:host=localhost;dbname=pakjiddat_crm;charset=utf8",
									"user_name" => "nadir",
									"password" => "kcW5eFSCbPXb#7LHvUGG8T8",
									"debug" => 2,
									"use_cache" => false,
									"app_name" => "Test Application"
								);
        /** The Database Initializer object is created */								
        $dbinitializer       = UtilitiesFramework::Factory("dbinitializer", $connection_info);
        /** The Database object is created */								
        $database            = $dbinitializer->GetDbManagerClassObj("Database");
        /** The insert query for adding data to pakphp_cached_data table */
        $insert_str          = "INSERT INTO pakphp_cached_data (app_name, function_name, function_parameters, data, created_on) VALUES (?,?,?,?,?)";
        /** The sql query parameters */
        $parameters          = array("Test Application", "InsertQueryTest", "Test Parameters", "Test Data", time());
        /** The sql query is prepared */        
        $sth                 = $database->Prepare($insert_str);
        /** The data is added to database */
        $database->Execute($insert_str, $parameters, $sth);
        
        /** The select query for fetching data from the pakphp_cached_data table */
        $select_str          = "SELECT * FROM pakphp_cached_data";
        /** The sql query is prepared */        
        $sth                 = $database->Prepare($select_str);
        /** The first row is fetched */
        $row                 = $database->FirstRow($select_str, null, $sth);
        /** All rows are fetched */
        $rows                = $database->AllRows($select_str, null, $sth);
        print_R($rows);exit;
    }
}

/** The DatabaseTest class object is created */
$database = new DatabaseTest();
/** The PdoAbstractionTest is run */
$database->TestPdoAbstraction();
