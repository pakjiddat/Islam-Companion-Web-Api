import {Login} from '/framework/ui/widgets/w3css/login/js/login.js';

/** The login button click handler is registered */
document.getElementById("login-btn").addEventListener("click", () => {
    /** Login object is created */
    let login = new Login();   
    /** Credentials are validated */
    login.ValidateCredentials();
});
