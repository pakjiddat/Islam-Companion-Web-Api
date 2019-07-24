<?php

declare(strict_types=1);

namespace Framework\Application;

use \Framework\Config\Config as Config;

/**
 * This class provides base class for API applications
 *
 * @category   Application
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
class Api extends \Framework\Application\Web
{
    /**
     * This function is used to call the XML-RPC functions of the given server
     *
     * It calls the XML-RPC function given in the function parameters
     * It uses the php xmlrpc extension
     *
     * @param array $parameters the parameters for making the RPC call
     *    rpc_function => the name of the RPC function
     *    rpc_function_parameters => the parameters used by rpc function
     *
     * @return string $response the response from the xml rpc server
     */
    final public function MakeXmlRpcCall(array $parameters) : string
    {
        /** The RPC function parameters for the request */
        $rpc_function_parameters   = $parameters;    
        /** The RPC function name */
        $rpc_function_name         = $parameters['rpc_function'];								  
        /** The RPC server url */
        $rpc_server_url            = $parameters['rpc_server_url'];
        
        /** The RPC function name is removed from the parameters */
        unset($rpc_function_parameters['rpc_function']);
        /** The RPC server url is removed from the parameters */
        unset($rpc_function_parameters['rpc_server_url']);
                
        /** The xml request */
        $xml_request              = xmlrpc_encode_request($rpc_function_name, $rpc_function_parameters);

        /** The xml tags are removed */
        $request                  = str_replace('<?xml version="1.0" encoding="iso-8859-1"?>', '', $xml_request);
        /** The new lines are also removed */
        $request                  = str_replace("\n", "", $request);
    
		/** The http request header */
        $request_headers[]        = "Content-type: text/xml";
		/** The http request header */        
        $request_headers[]        = "Content-length: " . strlen($request);

		/** The XML RPC request is made */
        $response                 = Config::GetComponent("filesystem")->GetFileContent(
                                        $rpc_server_url,
                                        "POST",
                                        $request,
                                        $request_headers
                                    );
     
        return $response;
    }
    
    /**
     * It sends HTTP headers that allow cross domain AJAX calls
     * The headers are only sent if enabled in application config
     */
    final protected function SendHttpHeaders() : void
    {
        /** If cross domain ajax calls need to be enabled, then the function EnableCrossDomainAjaxCalls is called */
        if (Config::$config["general"]["enable_cors"]) {
            /** It sends http header for allowing cross domain ajax calls */
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Headers: X-Requested-With");
            /** If the client sent the http request with http method 'OPTIONS', then the application ends */
            if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'OPTIONS') die();
        }
    }
    /**
     * It is used to extract data from the current url
     * The extracted data is used as application parameters and is saved to application config
     * This function may be overriden by classes that extend the Api class
     */
    protected function ParseUrl() : void
    {
        /** The information submitted by the user */
        $parameters                                 = Config::$config["general"]["http_request"];
        /** The page parameters are saved to application config */
        Config::$config["general"]["parameters"]    = $parameters;
    }
    /**
     * Used to initialize the API application
     *
     * It sends http headers allowing Cross Domain Ajax Calls
     * It generates application parameters from the url and submitted data
     * It generates url routing information that determines which method object to call
     *
     * @param array $parameters the application parameters     
     */
    final public function InitializeApplication($parameters) : void 
    {
    	/** It sends the required http headers. It also checks if cross domain ajax calls need to be enabled */
        $this->SendHttpHeaders();
        /** The application parameters are generated */
        $this->GenerateParameters();
		/** The url routing information is generated */
        Config::GetComponent("urlrouting")->GetCallback();        
    }
}
