<?php

declare(strict_types=1);

namespace Framework\Application\Libraries;

use \Framework\Config\Config as Config;
use \Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

/**
 * This class provides functions for logging data
 *
 * @category   Libraries
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class LogHandling
{
    /**
     * It saves the url request information to database
     */
    public function LogUserAccess() : void 
    {
        /** The current application request method */
        $request_method      = Config::$config["general"]["request_method"];
        /** The current application parameters */
        $parameters          = Config::$config["general"]["parameters"];
        /** The execution time for the request */
        $execution_time      = UtilitiesFramework::Factory("profiler")->GetExecutionTime();
        /** The mysql table list */
        $table_name          = Config::$config["general"]["mysql_table_names"]["access_data"];
		/** The application name */
        $app_name            = Config::$config["general"]["app_name"];
        /** The site url */
        $site_url            = Config::$config["general"]["site_url"];
        /** The current url */
        $url                 = Config::$config["general"]["request_uri"];
        /** The post parameters */
        $post_params         = json_encode(Config::$config["general"]["http_post"]);
        /** The get parameters */
        $get_params          = json_encode(Config::$config["general"]["http_get"]);
        /** The http method */
        $http_method         = Config::$config["general"]["request_method"];
        
        /** If the HTTP_X_FORWARDED_FOR server config is set, then it is used as the ip address */
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) 
            $ip_address      = $_SERVER['HTTP_X_FORWARDED_FOR'];
        /** If the REMOTE_ADDR server config is set, then it is used as the ip address */        
        else if (isset($_SERVER['REMOTE_ADDR'])) 
            $ip_address      = $_SERVER['REMOTE_ADDR'];
        /** The ip address is set to localhost */
        else 
            $ip_address      = "localhost";
        
        /** The http user agent field */
        $http_user_agent     = (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : "script";
        
        /** The api access data that needs to be logged */
        $api_access_data     = array(
                                   "url" => $url,
                                   "post_params" => $post_params,
                                   "get_params" => $get_params,
                                   "http_method" => $http_method,                                   
                                   "ip_address" => $ip_address,
                                   "browser" => $http_user_agent,
                                   "app_name" => $app_name,            
                                   "time_taken" => $execution_time,
                                   "site_url" => $site_url,
                                   "created_on" => time()
                              );
        
        /** The log data is enclosed in array */
        $log_data            = array($api_access_data);
        /** The parameters used to create logmanager object */
        $parameters          = array("dbinit" => Config::GetComponent("frameworkdbinit"));
        /** The test data is saved to database */
        UtilitiesFramework::Factory("logmanager", $parameters)->InsertLogData($log_data, $table_name);
    }
    
    /**
     * It returns the MySQL query log and server information
     * The server information includes the remote ip address, http host for the current address and the request url
     *
     * @param string $type [server,database,all] optional the type of information that is needed
     *
     * @return array $request_data server and database information
     *    mysql_query_log => string the mysql query log
     *    server_data => array the server data
     *        remote_host => string the remote http host
     *        http_host => string the http host
     *        request_url => string the http request url     
     */
    public function GetRequestData(string $type = "all") : array
    {
        /** The server and database information is initialized */
        $request_data                        = array("mysql_query_log" => "", "server_data" => "");
        /** If all data is required or only database information is required */
        if ($type == "all" || $type == "database") {
            /** The DbLogManager object is fetched */
            $dblogmanager                    = Config::GetComponent("dbinit")->GetDbManagerClassObj("DbLogManager");
            /** The mysql query log */
            $request_data['mysql_query_log'] = $dblogmanager->DisplayQueryLog(false);
        }
        /** The application name is added to the error message */
        $app_name                            = Config::$config["general"]["app_name"];
        /** If all data is required or only server information is required */
        if ($type == "all" || $type == "server") {
            /** The http host */
            $http_host                       = $_SERVER['HTTP_HOST'] ?? "localhost";
            /** The remote host ip */
            $remote_addr                     = $_SERVER['REMOTE_ADDR'] ?? "127.0.0.1";
            /** The request uri */
            $request_url                     = $_SERVER['REQUEST_URI'] ?? "";
            /** The http user agent */
            $http_user_agent                 = $_SERVER['HTTP_USER_AGENT'] ?? "command line";
            /** The server data */
            $request_data['server_data']     = array(
                                                    "remote_addr" => $remote_addr,
                                                    "http_host" => $http_host,
                                                    "request_url" => $request_url
                                                );
        }
        
        return $request_data;
    }   
}
