<?php

declare(strict_types=1);

namespace Framework\Utilities;

use \Framework\Utilities\DatabaseManager\Database as Database;

/**
 * This class provides functions for managing log data
 *
 * @category   UtilityClass
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class LogManager
{
    /** @var LogManager $instance The single static instance */
    protected static $instance;
    /** @var array the data used by class */
    public $data = array();
    /** @var object The Initializer class object */
    private $dbinit;
        
    /**
     * Initialize the class and set its properties
     *
     * @param array $parameters the parameters for the class constructor
     *    dbinit => DbInitializer the initializer object
     */
    protected function __construct(array $parameters) 
    {
        /** The dbinitializer property is set */
        $this->dbinit     = $parameters['dbinit'];
    }
    
    /**
     * Used to return a single instance of the class
     *
     * Checks if instance already exists. If it does not exist then it is created. The instance is returned
     *
     * @return LogManager static::$instance name the instance of the correct child class is returned
     */
    public static function GetInstance(array $parameters) : LogManager
    {
        if (static::$instance == null) {
            static::$instance = new static($parameters);
        }
        return static::$instance;
    }
    /**
     * Inserts the log data to database
     *
     * @param array $data the data that needs to be saved to database
     * @param string $table_name the name of the table
     */
    public function InsertLogData(array $data, string $table_name) 
    {
        /** If the first element does not exist, then the function returns an error */
        if (!isset($data[0])) throw new \Error("Log data is not in correct format");
        
        /** The list of fields for the insert query */
        $field_list       = implode(",", array_keys($data[0]));
        /** The place holder list */
        $placeholder_list = rtrim(str_repeat("?,", count(array_values($data[0]))), ",");
        /** The insert query */
        $insert_str       = "INSERT INTO " . $table_name . " (" . $field_list . ") VALUES(" . $placeholder_list . ")";
        /** The Database class object is fetched */
        $database         = $this->dbinit->GetDbManagerClassObj("Database");
        /** The database query is prepared */
        $sth              = $database->Prepare($insert_str);
  
        /** Each array is added to database */
        for ($count = 0; $count < count($data); $count++) {
            /** The data to be added */
            $log_data         = $data[$count];
            /** The list of query parameters */
            $query_params     = array_values($log_data);
            /** The database query is run */
            $is_run           = $database->Execute($insert_str, $query_params, $sth);
            /** If the query was not run, then an Exception is thrown */
            if (!$is_run) {
                /** The database error message */
                $error        = $this->dbinit->GetId()->errorInfo();
                /** The error is thrown */
                throw new \Error("Log data could not be added to database. Details: " . $error[2]);
            }
        }           
    }
    
    /**
     * Updates the given log data in database
     *
     * @param array $data the data that needs to be updated. it must be an associative array in key => value format
     * @param string $table_name the name of the table
     * @param array $condition the condition used to fetch the data
     *    condition => string the where clause containing placeholders
     *    values => array the where clause condition parameters
     */
    public function UpdateLogData(array $data, string $table_name, array $condition) 
    {
        /** The list of fields to update */
        $field_list              = array_keys($data);
        /** The list of field values to set */
        $query_params        = array_values($data);
        /** The query parameters are merged with the condition parameters */
        $query_params        = array_merge($query_params, $condition['values']);
        /** The update clause */
        $update_clause           = array();
        /** Each field to update is set */
        for ($count = 0; $count < count($field_list); $count++) {
            /** The update clause list is updated*/
            $update_clause[]     = $field_list[$count] . "=?";
        }
        /** The update clause is converted to ',' separated list */
        $update_clause           = implode(",", $update_clause);
        /** The update query */
        $update_str              = "UPDATE " . $table_name . " SET " . $update_clause . " WHERE " . $condition['condition'];
        
        /** The Database class object is fetched */
        $database                = $this->dbinit->GetDbManagerClassObj("Database");
        /** The database query is run */
        $is_run                  = $database->Execute($update_str, $query_params, null);
        /** If the query was not run, then an Exception is thrown */
        if (!$is_run) {
            /** The database error message */
            $error               = $this->dbinit->GetId()->errorInfo();
            /** The error is thrown */
            throw new \Error("Log data in database could not be updated. Details: " . $error[2]);
        }
    }
    
    /**    
     * It is used to fetch log data from database
     *
     * @param string $table_name the name of the log table
     * @param array $params optional the condition used to fetch the data
     *    condition => string the where clause containing placeholders
     *    values => array the where clause condition parameters
     *
     * @return array $log_data the log data
     */
    public function GetLogData(string $table_name, ?array $condition = null) : array
    {
        /** The select query */
        $sql              = "SELECT * FROM " . $table_name;
        /** If the condition for fetching test data is given */
        if (is_array($condition)) {
            /** The where clause is added to sql query */
            $sql          .= " WHERE " . $condition['condition'];
        }
        /** The Database class object is fetched */
        $database         = $this->dbinit->GetDbManagerClassObj("Database");
        /** The query parameters */
        $query_params     = $condition['values'];
        /** All rows are fetched */
        $log_data         = $database->AllRows($sql, $query_params);
        
        /** If the log data was not found, then log data is set to empty array */
        if ($log_data == null)
            $log_data = array();
        
        return $log_data;
    }
    
    /**    
     * It is used to determine if the log data exists in database
     *
     * @param string $table_name the name of the log table
     * @param array $condition the condition used to fetch the data
     *    condition => string the where clause containing placeholders
     *    values => array the where clause condition parameters
     *
     * @return bool $data_exists indicates if data exists in database
     */
    public function LogDataExists(string $table_name, array $condition) : bool
    {
        /** Indicates if data exists in database */
        $data_exists      = false;
        /** The select query */
        $sql              = "SELECT count(*) as total FROM " . $table_name . " WHERE " . $condition['condition'];
        /** The Database class object is fetched */
        $database         = $this->dbinit->GetDbManagerClassObj("Database");
        /** The query parameters */
        $query_params     = $condition['values'];
        /** The total number of rows is fetched */
        $row              = $database->FirstRow($sql, $query_params);
        
        /** If rows were found, then data exists in database */
        $data_exists      = ($row['total'] > 0) ? true : false;
        
        return $data_exists;
    }
    
    /**
     * It removes all data from the given log table
     *
     * @param string $table_name the name of the log table  
     */
    public function ClearLogData(string $table_name) 
    {
        /** The DbMetaQueryRunner class object is fetched */
        $this->dbinit->GetDbManagerClassObj("DbMetaQueryRunner")->TruncateTable($table_name);
    }
}

