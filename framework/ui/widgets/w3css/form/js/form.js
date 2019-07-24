"use strict";

/** The Form class */
export class Form {
    /** The class constructor */
    constructor() {
        /** Indicates that the mouse is not on the form and it is closable by clicking */
        this.closable = true;
        /** The container for the form which allow dragging the form */
        this.drag_container = "";
    }
    /**
     * Used to validate the current form
     *
     * @param string form_id the id of the form to validate
     *
     * @return boolean is_valid indicates if the form contains invalid input
     */
    ValidateForm(form_id) {
        /** The form is fetched */
        let form = document.getElementById(form_id);
        /** Indicates if the form contains invalid input */
        let is_valid = true;
        /** Each form element is checked */
        for (let i = 0; i < form.elements.length; i++) {
            /** The form element */
            let e = form.elements[i];
            /** The validity of the form is checked */
            if (e.checkValidity() == false) {
                /** The alert box is displayed */
                AlertBox.DisplayAlertBox("Error !", "The field " + e.name + " contains invalid data. Details: " + e.validationMessage, false);
                /** The is_valid is set to false */
                is_valid = false;
            }
        }

        return is_valid;
    }
    /**
     * Used to display a html form in a light box popup
     *
     * @param string url the url for the ajax call
     * @param string widget_id the id of the html element where the target widget is rendered     
     */
    DisplayForm(url, widget_id) {
        /** The current item is set */
        Widgets.current_item = widget_id;
        /** The parameters for the ajax call */
        let parameters = Array();
        /** The ajax call is made */
        Utilities.MakeAjaxCall(url, "GET", parameters, Form.DisplayFormCallback, Callbacks.ErrorCallBack);
    }
    /**
     * Used to post the given form's data using ajax
     *
     * @param string form_id the id of the form
     * @param string url the url for the ajax call     
     */
    PostFormData(form_id, url, widget_id) {
        /** The current item is set */
        Widgets.current_item = widget_id;
        /** The parameters for the ajax call */
        let parameters = Array();
        /** The form is fetched */
        let form = document.getElementById(form_id);
        /** Each form element is checked */
        for (let i = 0; i < form.elements.length; i++) {
            /** The form element */
            let e = form.elements[i];
            /** The form element is added */
            parameters[e.name] = e.value;
        }
        /** The form is validated. If it is valid, then it is submitted to the server */
        if (Form.ValidateForm(form_id)) {
            /** The ajax call is made */
            Utilities.MakeAjaxCall(url, "POST", parameters, Form.PostFormDataCallback, Callbacks.ErrorCallBack);
        }
    }
    /**
     * It is called when the ajax call for submitting a form gets a response from the server
     * 
     * @param array response the response from the application
     */
    PostFormDataCallback(response) {
        /**
         * If the server returned a success in the response then the success message is shown
         * And the modal form is closed
         */
        if (response.result == "success") {
            /** The list of html element ids of the elements that need to be refreshed */
            Widgets.GetRefreshWidgetIds();
            /** The response data from the server is saved */
            Widgets.server_response = response.data;
            /** The html element is refreshed */
            Widgets.RefeshWidget();
        } else {
            /** The alert box is displayed */
            AlertBox.DisplayAlertBox("Error !", "Data could not be updated. Please contact the system administrator", false);
        }
    }
    /**
     * It is used to add event handlers for dragging the form     
     */
    AddFormDragHandlers() {
        /** The drag object is created */
        Form.drag_container = new Object();
        /** The form container is added to the drag object */
        Form.drag_container.obj = document.getElementById('form-container');
        /** The form header */
        let form_header = document.getElementById('form-header');
        /** The mousedown event handler */
        form_header.addEventListener('mousedown', function (e) {
            Form.drag_container.top = parseInt(Form.drag_container.obj.offsetTop);
            Form.drag_container.left = parseInt(Form.drag_container.obj.offsetLeft);
            Form.drag_container.oldx = Form.drag_container.x;
            Form.drag_container.oldy = Form.drag_container.y;
            Form.drag_container.drag = true;
        });
        /** The mouseup event handler */
        window.addEventListener('mouseup', function () {
            Form.drag_container.drag = false;
        });
        /** The mousemove event handler */
        window.addEventListener('mousemove', function (e) {
            Form.drag_container.x = e.clientX;
            Form.drag_container.y = e.clientY;
            let diffw = Form.drag_container.x - Form.drag_container.oldx;
            let diffh = Form.drag_container.y - Form.drag_container.oldy;

            if (Form.drag_container.drag) {
                Form.drag_container.obj.style.left = Form.drag_container.left + diffw + 'px';
                Form.drag_container.obj.style.top = (Form.drag_container.top - 100) + diffh + 'px';
                e.preventDefault();
            }
        });
    }
    /**
     * It is called when the ajax call for displaying form gets a response from the server
     * 
     * @param array response the response from the application
     */
    DisplayFormCallback(response) {
        /** The response data from the server */
        let form_data = response.data;
        /** The contents of the modal form is set */
        document.getElementById('modal-form').innerHTML = form_data;
        /** The newlines are removed from the form data */
        form_data = form_data.replace(/\n/g, "");
        /** The regular expression pattern */
        let pattern = new RegExp(/<script>(.+)<\/script>/gi);
        /** The script tags are parsed */
        let script_tags = pattern.exec(form_data);
        /** Each script tag is executed */
        for (let count = 1;
            (script_tags != null && count < script_tags.length); count++) {
            /** A script tag */
            let script_content = script_tags[count];
            /** The script content is run */
            eval(script_content);
        }
        /** The modal form is shown */
        document.getElementById('modal-form').style.display = 'block';
        /** The form drag event handlers are added */
        Form.AddFormDragHandlers();
    }
    /**
     * It is used to register the click event handler on the body tag
     * 
     * If the user clicks some where outside the form, then the form is closed
     */
    RegisterFormClose() {
        /** The body element */
        let body = document.getElementsByTagName("body");
        /** Listen for click event */
        body[0].addEventListener('click', function (ev) {
            /** If the form is closable, then it is closed */
            if (Form.closable && document.getElementById('modal-form') != undefined) document.getElementById('modal-form').style.display = 'none';
        }, false);
    }
}
