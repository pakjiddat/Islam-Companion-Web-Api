"use strict";

export class Alert {
    /**
     * Used to display an alert popup
     *
     * @param string alert_title the alert box title
     * @param string alert_text the alert box text
     * @param boolean is_success indicates if the alert is a success alert
     */
    Display(alert_title, alert_text, is_success) {
        /** The alert title is set to success */
        document.getElementById("alert-title").innerHTML = alert_title;
        /** The server response is displayed */
        document.getElementById("alert-text").innerHTML = alert_text;
        /** The color classes are removed */
        document.getElementById("alert-box").className = document.getElementById("alert-box").className.replace("w3-green", "");
        document.getElementById("alert-box").className = document.getElementById("alert-box").className.replace("w3-red", "");
        /** If the alert is a success */
        if (is_success) {
            document.getElementById("alert-box").className += " w3-green";
        }
        /** If the alert is an error */
        else {
            document.getElementById("alert-box").className += " w3-red";
        }
        /** The alert box top position is set */
        document.getElementById("alert-box").style.top = window.scrollY + "px";
        /** The alert box is shown */
        document.getElementById("alert-box").className = document.getElementById("alert-box").className.replace("w3-hide", "");
        /** The alert box is hidden after 3 seconds */
        setTimeout("document.getElementById('alert-box').className+=' w3-hide';", 2000);
    }
}
