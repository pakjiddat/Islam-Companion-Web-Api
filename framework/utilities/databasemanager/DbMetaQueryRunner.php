<?php

declare(strict_types=1);

namespace Framework\Utilities\DatabaseManager;

use \Framework\Utilities\Database as Database;

/**
 * This class provides functions for running meta queries such as creating tables, listing tables, updating columns etc
 *
 * @category   UtilityClass
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
class DbMetaQueryRunner
{
    /** @var array the Database object */
    private $database;
    /** @var array the LogManager object */
    private $dblogmanager;    
    /** @var object The single static instance */
    protected static $instance;
        
    /**
     * Class constructor
     * Sets the DbLogManager and Database class objects
     * 
     * @param array $parameters the constructor parameters
     *    dbinit => DbInitializer the database initializer object
     */
    function __construct(array $parameters) 
    {
        /** An object of Database class is fetched */
        $this->database        = $parameters['dbinit']->GetDbManagerClassObj("Database");
        /** An object of DbLogManager class is created */
        $this->dblogmanager    = $parameters['dbinit']->GetDbManagerClassObj("DbLogManager");
    }
    
    /**
     * Used to return a single instance of the class
     * 
     * Checks if instance already exists. If it does not exist then it is created. The instance is returned
     * 
     * @param array $parameters an array containing class parameters. it has following keys:
     *    database => Database the object for accessing the database
     *    table_name => string string the database table where the cached data will be stored		 		
     *  
     * @return CacheManager static::$instance name the class instance
     */
    public static function GetInstance(array $parameters) : DbMetaQueryRunner
    {
        if (static::$instance == null) {
            static::$instance = new static($parameters);
        }
        return static::$instance;
    }
    
    /**
     * Creates a database table
     *
     * It creates a table using the given parameters
     *
     * @param string $table_name the name of the table to creat
     * @param array $field_list the list of table fields
     * @param string $primary_key the name of the primary key for the table
     * @param array $auto_increment optional the list of fields to auto increment. e.g `id` int (11)     
     * @param array $indexes optional the list of indexes that are unique
     * @param string $comment optional the table comment
     * @param string $engine optional the SQL engine. default value is MyISAM
     * @param string $default_charset optional the default charset for the table. default value is utf8
     * @throws \Error object if table could not be created
     *
     * @return boolean $is_valid returns true if table was successfully created. throws exeption otherwise
     */
    public function CreateTable(
        string $table_name,
        array $field_list,
        string $primary_key,
        array $auto_increment = array(),
        array $indexes = array(),
        string $comment = '',
        string $engine = 'MyISAM',
        string $default_charset = 'utf8'
    ) : bool {
    
        /** The list of sql query parameters */
        $query_parameters       = array($table_name);
        /** The list of table fields */
        $table_field_list       = array();
        /** Each field is added to the table field list */
        for ($count = 0; $count < count($field_list); $count++) {
            /** The field list is built */
            $table_field_list[] = '? ?';
            /** The query parameters are updated */
            $query_parameters[] = $field_list[$count]['name'];
            /** The query parameters are updated */
            $query_parameters[] = $field_list[$count]['type'];
        }
        /** The query parameters are updated */
        $query_parameters       = array_merge($query_parameters, array($engine, $default_charset, $comment));
            
        /** The list of table fields */
        $field_names            = implode(",\n", $table_field_list);
        /** The create table sql */
        $create_table_sql       = "CREATE TABLE IF NOT EXISTS ? (";
        /** The field list is added to the create table sql */
        $create_table_sql       = $create_table_sql . $field_names;        
        
        /** The other table attributes are added */
        $create_table_sql       = $create_table_sql . ") ENGINE=? " . "AUTO_INCREMENT=1 ";
        $create_table_sql       .= "DEFAULT CHARSET=? " . "COMMENT='?'";
        
        /** The query logging is started */
        $this->dblogmanager->LogQuery(true);
        /** The create table sql is run */
        $this->database->Execute($create_table_sql, $query_parameters);
        /** The query logging is stopped */
        $this->dblogmanager->LogQuery(false, $create_table_sql);
        
        /** The primary key is added to the table */
        $this->AddPrimaryKey($table_name, $primary_key);
        
        /** If given, then the indexes are added to the table */
        if (count($indexes) > 0) {
            /** The unique indexes are added */
            $this->AddUniqueIndexes($table_name, $indexes);
        }
        /** If given, then the auto increment columns are added to the table */
        if (count($auto_increment) > 0) {
            /** The auto increment columns are added */
            $this->AddAutoIncrement($table_name, $auto_increment);
        }
    }
    /**
     * Used to add auto increment columns
     *
     * @param string $table_name the name of the default SQL table
     * @param array $auto_increment the list of fields to auto increment. e.g `id` int (11)
     */
    public function AddAutoIncrement(string $table_name, array $auto_increment) 
    {
        /** The auto increment sql is built */
        for ($count = 0; $count < count($auto_increment); $count++) {
            /** The list of sql query parameters */
            $query_parameters   = array($auto_increment[$count]);
            /** The sql query for adding auto increment */
            $auto_increment_sql = "ALTER TABLE " . $table_name;
            $auto_increment_sql .= " MODIFY ? NOT NULL AUTO_INCREMENT , AUTO_INCREMENT=1";
                
            /** The query logging is started */
            $this->dblogmanager->LogQuery( true);
            /** The create table sql is run */
            $this->database->Execute($auto_increment_sql, $query_parameters);
            /** The query logging is stopped */
            $this->dblogmanager->LogQuery(false, $auto_increment_sql);
        }
    }
    /**
     * Used to add unique indexes to the table
     *
     * @param string $table_name the name of the default SQL table
     * @param array $index_data the index information for the unique indexes
     */
    public function AddUniqueIndexes(string $table_name, array $index_data) 
    {
        /** The list of sql query parameters */
        $query_parameters       = array($table_name, $index_data['name']);
        /** The query parameters are updated */
        $query_parameters       = array_merge($query_parameters, $index_data['field_list']);
        /** The query place holders */
        $place_holders          = array_fill(0, count($index_data['field_list']), "?");
        /** The place holders are formatted */
        $place_holders          = implode(",", $place_holders);
        /** The sql query for adding index */
        $indexes_sql            = "ALTER TABLE ? " . "ADD UNIQUE KEY ? (" . $place_holders . ")";
            
        /** The query logging is started */
        $this->dblogmanager->LogQuery(true);
        /** The create table sql is run */
        $this->database->Execute($indexes_sql, $query_parameters);
        /** The query logging is stopped */
        $this->dblogmanager->LogQuery(false, $indexes_sql);
    }
    /**
     * Used to add primary key to the table
     *
     * @param string $table_name the name of the default SQL table
     * @param string $primary_key the name of the primary key for the table     
     */
    public function AddPrimaryKey(string $table_name, string $primary_key) 
    {
        /** The list of sql query parameters */
        $query_parameters       = array($table_name, $primary_key);
        /** The sql query for adding index */
        $indexes_sql            = "ALTER TABLE ? " . "ADD PRIMARY KEY (?)";
        /** The query logging is started */
        $this->dblogmanager->LogQuery(true);
        /** The indexes sql is run */
        $this->database->Execute($indexes_sql, $query_parameters);
        /** The query logging is stopped */
        $this->dblogmanager->LogQuery(false, $indexes_sql);
    }
    /**
     * Used to get the list of all the tables in the database
     *
     * @return array $table_list the list of all table names in the database
     */
    public function GetTableList() : array
    {
        /** The select query */
        $sql                   = "SHOW TABLES";
        /** The select query parameters */
        $query_parameters      = array();
        /** The query is run and all rows are returned */
        $table_list            = $this->database->AllRows($sql, $query_parameters);
        
        return $table_list;
    }
    /**
     * Truncates a database table
     *
     * It truncates the given table
     *
     * @param string $table_name the name of the table to truncate
     */
    public function TruncateTable(string $table_name) : void
    {
        /** The truncate table sql */
        $sql     = "TRUNCATE TABLE " . $table_name;
        /** The truncate table sql is run */
        $this->database->Execute($sql, null, null);
    }
    /**
     * Used to return all the table rows
     *
     * @param string $table_name the name of table
     *
     * @return array $rows all rows in the table are returned
     */
    public function AllTableRows(string $table_name) : array
    {
        /** The select query */
        $sql    = "SELECT * FROM " . $table_name;
        /** The query is run and all rows are returned */
        $rows   = $this->database->AllRows($sql, null, null);
        
        return $rows;
    }
    /**
     * Used to drop the given table column
     *
     * @param string $table_name the name of the database table
     * @param string $column_name the name of the column that is to be dropped
     *
     * @return boolean $column_dropped used to indicate if the given table column was successfully dropped
     */
    public function DropColumn(string $table_name, string $column_name) : bool
    {
        /** The sql for dropping the column */
        $sql            = "ALTER TABLE " . $table_name . " DROP COLUMN " . $column_name;
        /** The result of the drop operation. The column is dropped */
        $column_dropped = $this->database->Execute($sql, null, null);
        
        return $column_dropped;
    }
    /**
     * Used to rename the given table column
     *
     * @param string $table_name the name of the database table
     * @param string $old_column_name the name of the column that is to be renamed
     * @param string $new_column_name the new name of the column
     * @param string $column_type the type of the column
     *
     * @return boolean $column_renamed used to indicate if the given table column was successfully renamed
     */
    public function RenameColumn(
        string $table_name,
        string $old_column_name,
        string $new_column_name,
        string $column_type
    ) : bool {
    
        /** The sql for renaming the column */
        $sql                  = "ALTER TABLE " . $table_name . " CHANGE ";
        $sql                  .= $old_column_name . " " . $new_column_name . " :column_type";
        /** The select query parameters */
        $query_parameters     = array(":column_type" => $column_type);
        /** The result of the rename operation. The column is renamed */
        $column_renamed       = $this->database->Execute($sql, $query_parameters, null);
        
        return $column_renamed;
    }
    /**
     * Used to get the names of all the table fields
     *
     * @param string $table_name the name of the table
     *
     * @return array $field_names the names of all the table fields
     */
    public function GetFieldNames(string $table_name) : array
    {
        /** The sql query */
        $sql                    = "SELECT * FROM " . $table_name;
        /** The query logging is started */
        $this->dblogmanager->LogQuery(true);
        
        /** The query is run and all rows are returned */
        $field_names            = $this->database->AllRows($sql, null, null);
        /** The formatted field names */
        $formatted_field_names  = array();
        /** Each field is formatted */
        for ($count = 0; $count < count ($field_names); $count++) {
            /** The formatted field */
            $formatted_field_names[$field_names[$count]['Field']] = $field_names[$count];
        }
        /** The field names are set */
        $field_names            = $formatted_field_names;
        /** The query logging is stopped */
        $this->dblogmanager->LogQuery(false, $sql);
        
        return $field_names;
    }
}

