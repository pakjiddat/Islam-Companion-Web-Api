"use strict";

/** The ScrollTop class */
export class ScrollTop {

    /** Used to register the scroll event handler */
    Initialize() {
        /** When the user scrolls down 200px from the top of the document, show the button */
        window.onscroll = this.ToggleButton;
    }

    /** Displays/Hides the scroll to top button */
    ToggleButton() {
        /** If the current current scroll is 200px or more */
        if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
            /** The scroll to top button is displayed */
            document.getElementById("scroll-btn").style.display = "block";
        } else {
            /** The scroll to top button is hidden */
            document.getElementById("scroll-btn").style.display = "none";
        }
    }

    /** When the user clicks on the button, scroll to the top of the document */
    ScrollToTop() {
        /** The user is scrolled to the top of the page */
        document.body.scrollTop = 0;
        document.documentElement.scrollTop = 0;
    }
}
