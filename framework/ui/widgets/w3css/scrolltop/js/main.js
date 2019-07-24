import {ScrollTop} from '/framework/ui/widgets/w3css/scrolltop/js/scroll-top.js';

/** ScrollTop object is created */
let scrolltop = new ScrollTop();   
/** The ScrollTop object is initialized */
scrolltop.Initialize();

/** The scroll-top button click handler is registered */
document.getElementById("scroll-btn").addEventListener("click", () => {
    /** ScrollTop object is created */
    let scrolltop = new ScrollTop();   
    /** The ScrollTop object is initialized */
    scrolltop.ScrollToTop();
});
