<?php

declare(strict_types=1);

namespace Framework\Utilities;

/** 
 * This class provides functions for getting the function execution time,
 * stack trace, cpu and memory usage and code coverage data
 *
 * @category   UtilityClass
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class Profiler
{
    /** @var int $start_time The start execution time */
    private $start_time;
    /** @var int $start_memory The initial real memory usage by the current script */
    private $start_memory;

    /** @var Profiling $instance The single static instance */
    protected static $instance;
        
    /**
     * Initialize the class and set its properties
     */
    protected function __construct() 
    {
    }
    
    /**
     * Used to return a single instance of the class
     *
     * Checks if instance already exists. If it does not exist then it is created. The instance is returned
     *
     * @return Profiler static::$instance name the instance of the correct child class is returned
     */
    public static function GetInstance(array $parameters) : Profiler
    {
        if (static::$instance == null) {
            static::$instance = new static($parameters);
        }
        return static::$instance;
    }
    
    /**
     * Used to start the profiling
     * It sets the current time or memory depending on the required data
     *
     * @param string $required_data [execution_time,memory_delta] the profiling data that is required
     *
     * @return float $start_value the current value of the required metric
     */
    public function StartProfiling(string $required_data) : float
    {
        /** If the execution time is required */
        if ($required_data == "execution_time") {
        	/** The current time in micro seconds is fetched */
            $this->start_time   = microtime(true);
            /** The time is saved to start value */
            $start_value        = $this->start_time;
        }
        /** If the memory delta is required */
        else if ($required_data == "memory_delta") {
	        /** The current memory usage in bytes is fetched */
            $this->start_memory = memory_get_usage(true);
            /** The memory usage is saved to start value */
            $start_value        = $this->start_memory;
        }
        
        return $start_value;
    }
    /**
     * Used to get the total execution time
     *
     * It gets the difference between the current time and the start time
     * The time difference is returned
     *
     * @return float $execution_time the total execution time in microseconds
     */
    public function GetExecutionTime() : float
    {
        /** The total execution time */
        $execution_time = (microtime(true) - $this->start_time);

        return $execution_time;
    }
    /**
     * Used to get the real memory usage delta
     *
     * It gets the difference between the current real memory usage and the start real memory usage
     * The memory difference is returned
     *
     * @return int $memory_delta the total memory delta in bytes
     */
    public function GetMemoryDelta() : int
    {
        /** The memory delta in bytes */
        $memory_delta = (memory_get_usage(true) - $this->start_memory);
        
        return $memory_delta;
    }
    /**
     * Used to return formatted time
     *
     * It returns the time passed since the given time
     *
     * @param int $time the time that needs to be formatted. it should be in unix timestamp format
     *
     * @return string $formatted_time the formatted time
     */
    public function FormatTime(int $time) : string
    {
        /** The number of seconds since the given time */
        $time = (time() - $time);
        /** The unit is set to seconds */
        $unit = "sec";
        /** If the time is larger then 60 seconds then it is converted to minutes */
        if ($time > 60) {
            $time = ceil($time / 60);
            /** The unit is set to minutes */
            $unit = "min";
            /** If the time is larger then 60 minutes then it is converted to hours */
            if ($time > 60) {
                $time = ceil($time / 60);
                /** The unit is set to hours */
                $unit = "hours";
            }
        }
        /** The formatted time */
        $formatted_time = $time . " " . $unit . " ago";
        
        return $formatted_time;
    }
}
