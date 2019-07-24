<?php

declare(strict_types=1);

namespace Framework\Utilities\DatabaseManager;

use \Framework\Utilities\UtilitiesFramework as UtilitiesFramework;
/**
 * This class provides functions for logging sql queries
 *
 * @category   UtilityClass
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
class DbLogManager
{
    /** @var array The SQL query log information */
    private $query_log = array();
    /** @var int Used to indicate the query start time */
    private $start_time;
    /** @var int Used to indicate debug level */
    private $debug;
    /** @var object The single static instance */
    protected static $instance;
    
    /**
     * Class constructor
     * Sets the debug and use_cache object properties
     * 
     * @param array $parameters the constructor parameters
     *    debug => int [0-2] the debug level
     */
    public function __construct(array $parameters) 
    {
        /** The debug level is set */
        $this->debug             = $parameters['debug'];
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
    public static function GetInstance(array $parameters) : DbLogManager
    {
        if (static::$instance == null) {
            static::$instance = new static($parameters);
        }
        return static::$instance;
    }
    
    /**
     * Used to log the given sql query
     *
     * @param boolean $is_start indicates that the query logging should be started
     * @param string $query optional the sql query to log     
     */
    public function LogQuery(bool $is_start, string $query = "") 
    {       
        /** If the debug level is less than 1, then function returns */
        if ($this->debug < 1) return;
         
        /** If query logging should be started */
        if ($is_start) {
            /** The current time is noted */
            $this->start_time    = UtilitiesFramework::Factory("profiler")->StartProfiling("execution_time");
        }            
        /** If the query logging should stop */
        else {
            /** The execution time for the query */
            $time_taken          = UtilitiesFramework::Factory("profiler")->GetExecutionTime();
            /** The logging information is saved to array */
            $sql_log             = array(
                                        array(
                                            "sql" => $query,
                                            "time_taken" => number_format($time_taken , 4)
                                        )
                                    );
            /** The query log is updated */
            $this->query_log     = array_merge($this->query_log, $sql_log);
        }  
    }
    /**
     * Used to display the query log
     *
     * @param boolean $is_display used to indicate if the query log should be displayed or returned
     *
     * @return string $query_string the SQL query log
     */
    public function DisplayQueryLog(bool $is_display) : string
    {
        /** The SQL query string */
        $query_string           = "";
        /** The line break character is set depending on whether the script is being run from command line or browser */
        $line_break             = (php_sapi_name() != "cli") ? "<br/>" : "\n";
        /** The query log is checked */
        for ($count = 0; $count < count($this->query_log); $count++) {
            /** The sql query */
            $query              = $this->query_log[$count];
            /** If the debug level is greater than 1, then the time taken is appended */
            if ($this->debug > 1) 
                $query_string   .= ($count + 1) . ". Time: " . $query['time_taken'] . " sec" . 
                                   $line_break . "    Query: " . $query['sql'] . $line_break . $line_break;
            /** If the debug level is 1 */            
            else if ($this->debug == 1) 
                $query_string   .= ($count + 1) . ".   Query: " . $query['sql'] . $line_break . $line_break;
        }
        /** If the query log should be displayed */
        if ($is_display)
            echo $query_string;
        
        return $query_string;
    }
    /**
     * Used to clear the query log
     */
    public function ClearQueryLog() 
    {
        $this->query_log = array();
    }
    /**
     * Used to get the query log
     *
     * @return array $query_log the database query log
     */
    public function GetQueryLog() : array
    {
        return $this->query_log;
    }
}

