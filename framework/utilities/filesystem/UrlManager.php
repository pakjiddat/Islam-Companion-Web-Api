<?php

declare(strict_types=1);

namespace Framework\Utilities\FileSystem;

use Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

/**
 * This class provides functions for working with urls
 *
 * @category   UtilityClass
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
class UrlManager
{
	/**
     * Used to get the contents of a url
     *
     * @param string $url url to be fetched
     * @param string $method optional http method. defaults to "get". http method for the request
     * @param string $parameters optional parameters. the data to be sent to the remote server
     * @param array $request_headers the http headers to include in the url request
     * @param boolean $fetch_header indicates that only the http response header is required
     * @param string $ip_address the ip address from which to make the request
     * @param boolean $use_compression indicates that the contents of the url should be compressed
     *
     * @return string $file_contents. the contents of the file or the http response headers in json format
     */
    public function GetFileContent(
        string $url,
        string $method = "GET",
        string $parameters = "",
        array $request_headers = array(),
        bool $fetch_header = false,
        string $ip_address = "",
        bool $use_compression = false
    ) : string {
    
        /** If the http response headers are required */
        if ($fetch_header) {
        	/** The http headers are fetched */
            $file_contents      = get_headers($url);
            /** The http headers are json encoded */
            $file_contents      = json_encode($file_contents);
        }
        /** If the url given is a file */
        else if (strpos($url, "http://") === false && strpos($url, "https://") === false) {
        	/** The file contents are fetched */
        	$file_contents      = UtilitiesFramework::Factory("filemanager")->ReadLocalFile($url);
        }
        else {
        	/** The curl handle in initialised */
			$ch = curl_init();
			/** The request url is set */
			curl_setopt($ch, CURLOPT_URL, $url);			            
            /** Indicates, that the contents should be returned */
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
			/** Indicates, that redirect urls should be followed */
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
			/** The curl timeout is set to 60 sec */
			curl_setopt($ch, CURLOPT_TIMEOUT, 60);
			/** Indicates, that maximum of 5 redirections should be followed */
			curl_setopt($ch, CURLOPT_MAXREDIRS, 5 );
			/** If the data should be compressed */
			if ($use_compression) {
				/** Indicates that the url contents should be encoding using gzip */
				curl_setopt($ch, CURLOPT_ENCODING , "gzip");
			}
			/** If the http headers are given then they are set */
			if (count($request_headers) > 0)
			    curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
			/** Indicates that the response headers should be returned */
			//curl_setopt($ch, CURLOPT_HEADER, 1);
			/** If the ip address is given, then it added */
			if ($ip_address != "") {
				/** The source ip of the request is set */
				curl_setopt($ch, CURLOPT_INTERFACE, $ip_address);
			}
			/** If the POST method is required */
            if ($method == "POST") {            
				/** Indicates that the POST http method should be used */
				curl_setopt($ch, CURLOPT_POST, true);				
				/** If the parameters are given, then they are added */
				if ($parameters != "") {
					/** If the parameters are json encoded, then they are json decoded */
					if (UtilitiesFramework::Factory("stringutils")->IsJson($parameters)) {
						/** The parameters are json decoded */
						$parameters   = json_decode($parameters, true);
					}
					/** The parameters are added */
					curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
				}
            }
            /** The file contents are fetched */
			$file_contents = curl_exec($ch);
			/** The curl handle is closed */
			curl_close($ch);
        }
        
        return $file_contents;
    }
    /**
     * Used to determine if the current internet connection is valid
     *
     * It tries to make a tcp connection to google's server on port 80
     * It returns true if the connection works and false otherwise
     *
     * @return boolean $is_valid indicates if the tcp connection succeeded
     */
    public function IsInternetConnectionValid() : bool
    {
        try {
            /** The connection is made to googles server on port 80 */
            $connected  = @fsockopen("www.google.com", 80, $errno, $errstr, 30);
            /** If the connection succeeded */
            if ($connected) {
                $is_valid = true;
                fclose($connected);
            }
            /** If the connection failed */
            else {
                $is_valid = false;
            }
        }
        catch(\Error $e) {
            /** If the connection failed */
            $is_valid = false;
        }
        
        return $is_valid;        
    }
    /**
     * Used to determine if the given url is valid
     *
     * It checks the http response headers for the given url
     * If the response headers contain an error code, i.e 4xx, then function returns false
     * Otherwise the function returns true
     * See http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     *
     * @param string $url url to be checked
     *
     * @return boolean $is_valid indicates if the given url is valid or not
     */
    public function IsUrlValid(string $url) : bool
    {
        /** Used to indicate if the url is valid or not */
        $is_valid        = false;
        /** The http headers for the url are fetched */
        $url_headers     = @get_headers($url);
        /** Each header is checked for http error code */
        for ($count = 0; $count < count($url_headers); $count++) {
            /** The http header */
            $http_header = ($url_headers[$count]);
            /** The http header is checked for 4xx code */
            preg_match("/(http\/1\.[0,1] 4\d\d\s+[a-z]+)/i", $http_header, $matches);
            /** Indicates if the url contains error code or not */
            $is_valid    = (isset($matches[0]) && isset($matches[1])) ? false : true;
            /** If the http header contains an error code then no need to check other http headers */
            if (!$is_valid) break;
        }
        return $is_valid;
    }
}
