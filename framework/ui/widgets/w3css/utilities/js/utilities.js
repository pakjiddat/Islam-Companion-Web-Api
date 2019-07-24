"use strict";

import {Alert} from '/framework/ui/widgets/w3css/alert/js/alert.js';

export class Utilities {
    /**	 
     * Used to make an ajax call
     *
     * @param string url string used in ajax request.
     * @param string method [GET~POST] possible values are GET and POST.
     * @param array parameters parameters sent with ajax call.
     * @param string callaback function that is called after successfull response from server is recieved.
     * @param string error_call_back function that is called when an error response from server is received.	 
     */
    MakeAjaxCall(url, method, parameters, callback, error_call_back) {
        try {
            var request = new XMLHttpRequest();
            /** The function is called when the XMLHttpRequest object state changes */
            request.onreadystatechange = function () {
                try {
                    var DONE = this.DONE || 4;
                    if (this.readyState === DONE) {
                        var response = JSON.parse(this.responseText);
                        if (callback) callback(response);
                    }
                } catch (err) {
                    error_call_back(err);
                }
            };
            /** The function is called when an error occurs */
            request.onerror = function (err) {
                /** The error callback is called */
                error_call_back(err);
            };
            var data = "";
            /** Each parameter is added */
            for (var field_name in parameters) {
                data = data + field_name + "=" + encodeURIComponent(parameters[field_name]) + "&";
            }
            /** If the parameters are given */
            if (data != "") {
                /** The trailing & is removed */
                data = data.substr(0, data.length - 1);
                if (method == "GET") {
                    if (url.indexOf("?") > 0) url = url + "&" + data;
                    else url = url + "?" + data;
                }
                /** The XMLHttpRequest is opened */
                request.open(method, url, true);
                /** The request headers are sent. It indicates to server that ajax call is being made */
                request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                /** If the http method is set to POST */
                if (method == "POST") {
                    /** The http request headers for post data is sent */
                    request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                }
                /** The request parameters are sent */
                request.send(data);
            }
            /** If no parameters are given */
            else {
                /** The XMLHttpRequest is opened */
                request.open(method, url, true);
                /** The request headers are sent. It indicates to server that ajax call is being made */
                request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                /** The parameters for the XMLHttpRequest */
                /** If the HTTP method is GET */
                if (method == "GET") {
                    /** The request is sent */
                    request.send();
                }
                /** If the HTTP method is POST */
                else if (method == "POST") {
                    /** The request is sent */
                    request.send(null);
                }
            }
        } catch (err) {
            error_call_back(err);
        }
    }
    /**
     * It is called when the ajax call fails or if there is an error in the application
     * 
     * @param array result the response from the application
     */
    ErrorCallBack(result) {
        /** Alert object is created */
        let alert                = new Alert();
        /** The alert box is displayed */
        alert.Display("Error !", "An error has occured in the application. Please contact the system administrator", false);
    }
}
