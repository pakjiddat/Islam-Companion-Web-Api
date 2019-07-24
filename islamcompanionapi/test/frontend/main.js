"use strict";

import { hadith } from './hadith.js';
import { holyquran } from './holy-quran.js';

/** The test is started */
QUnit.start();

QUnit.begin(function( details ) {
    /** Indicates if the website is in development mode */
    let dev_mode   = true;
    /** The site url is set */
    var site_url   = "https://islamcompanion.pakjiddat.pk";
    /** If the website is in development mode */
    if (dev_mode) {
        /** The site url is set */
        site_url   = "http://dev.islamcompanion.pakjiddat.pk";       
    }
    holyquran.site_url = site_url;
    hadith.site_url = site_url;    
});

QUnit.module( "Hadith", () => { 
    /** The Hadith Navigator is tested */
    hadith.TestHadithNavigator();
});
    
QUnit.module( "HolyQuran", () => { 
    /** The HolyQuran Navigator is tested */
    holyquran.TestHolyQuranNavigator();
});
