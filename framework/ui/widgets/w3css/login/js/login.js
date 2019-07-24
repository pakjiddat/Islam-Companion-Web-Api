"use strict";

import {Form} from '/framework/ui/widgets/w3css/form/js/form.js';
import {Utilities} from '/framework/ui/widgets/w3css/utilities/js/utilities.js';
import {Alert} from '/framework/ui/widgets/w3css/alert/js/alert.js';

/** The Login class */
export class Login {
    /**
     * Used to check if the user name and password entered by the user is correct     
     */
    ValidateCredentials() {
        /** Form object is created */
        let form                = new Form();
        /** Utilities object is created */
        let utilities           = new Utilities();
        /** The arguments for the ajax call */
        let parameters          = Array();
        /** The user name is set */
        parameters['user_name'] = window.btoa(document.getElementById('user_name').value);
        /** The password is set */
        parameters['password']  = window.btoa(document.getElementById('password').value);

        /** The login url */
        let url = "/login/validate";
        /** The form is validated. If it is valid, then it is submitted to the server */
        if (form.ValidateForm('login-form')) {
            /** The ajax call is made */
            utilities.MakeAjaxCall(url, "POST", parameters, this.LoginFormCallback, utilities.ErrorCallBack);
        }
    }
    /**
     * Used to handle the response from the server after the user has clicked on the login button
     *
     * It displays a validation error message to the user if the form contains invalid input 
     * It redirects the user to the dashboard page if the login credentials are valid
     *
     * @param array response the response from the application
     */
    LoginFormCallback(response) {
        /** Alert object is created */
        let alert                = new Alert();
        /** If the user credentials are not valid */
        if (response.is_valid == "no") {
            /** The alert box is displayed */
            alert.Display("Error !", "Please enter a valid user name and password", false);
        }
        /** If the user credentials are valid */
        if (response.is_valid == "yes") {
            /** The user is redirected */
            location.href = response.message;
        }
    }
}
