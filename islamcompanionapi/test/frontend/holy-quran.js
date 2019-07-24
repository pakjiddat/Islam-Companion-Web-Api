"use strict";

/** The HolyQuranTestFunctions class */
class HolyQuranTestFunctions {

    /** The constructor */
    constructor() {
        /** The site url */
        this.site_url = "";
    }
    
    /** The function used to test suras */
    TestSuras (assert, done, data) {

        /** The Ajax call */
        $.ajax({
            method: "POST",
            url: this.site_url + "/api/get_suras_in_division",
            data: {
                "division": data.d, 
                "div_num": data.n
            }
		})
        /** This function is called after data has been loaded */
        .done(function( result ) {
            /** The data returned by the server is parsed */
            var result = JSON.parse(result);
            
            /** The message to show if test fails */
            let msg    = "The number of suras is equal to " + data.result.e + ". Got " + result.length;
            /** The number of suras is tested */
            assert.ok( result.length == data.result.e, msg);
            /** The test is marked as completed */
            done();
        });            
    }

    /** The function used to test if the rukus and ayas in the given division and sura have the correct range */
    TestRukuList (assert, done, data) {

        /** The Ajax call */
        $.ajax({
            method: "POST",
            url: this.site_url + "/api/get_ruku_list",
            data: {
                "division": data.d, 
                "div_num": data.n, 
                "sura": data.s
            }
		})
        /** This function is called after data has been loaded */
        .done(function( result ) {
            /** The data returned by the server is parsed */
            var result = JSON.parse(result);
            
            /** The message to show if test fails */
            let msg    = "The start ruku number is equal to " + data.result.sr + ". Got " + result.start_ruku;
            /** The start ruku is tested */
            assert.ok( result.start_ruku == data.result.sr, msg);
            
            /** The message to show if test fails */
            msg    = "The end ruku number is equal to " + data.result.sr + ". Got " + result.end_ruku;          
            /** The end ruku is tested */
            assert.ok( result.end_ruku == data.result.er, msg);
            
            /** The test is marked as completed */
            done();
        });            
    }
    
    /** The function used to test verses */
    TestVerses (assert, done, data) {

        /** The Ajax call */
        $.ajax({
            method: "POST",
            url: this.site_url + "/api/get_verses",
            data: {
                "start_ayat": data.sa, 
                "end_ayat": data.ea, 
                "sura": data.s, 
                "language": "Urdu", 
                "narrator": "Abul A'ala Maududi"
            }
		})
        /** This function is called after data has been loaded */
        .done(function( result ) {
            /** The data returned by the server is parsed */
            var result = JSON.parse(result);
            
            /** The message to show if test fails */
            let msg    = "The number of verses is equal to " + data.result.vc + ". Got " + result.length;
            /** The number of verses is tested */
            assert.ok( result.length == data.result.vc, msg);
            /** The test is marked as completed */
            done();
        });            
    }
    
    /** The function used to test languages */
    TestLanguageList (assert, done, data) {

        /** The Ajax call */
        $.ajax({
            method: "POST",
            url: this.site_url + "/api/get_languages",
            data: {}
		})
        /** This function is called after data has been loaded */
        .done(function( result ) {
            /** The data returned by the server is parsed */
            var result = JSON.parse(result);
            
            /** The message to show if test fails */
            let msg    = "The number of languages is equal to " + data.result.lc + ". Got " + result.length;
            /** The number of languages is tested */
            assert.ok( result.length == data.result.lc, msg);
            /** The test is marked as completed */
            done();
        });            
    }
    
    /** The function used to test narrators */
    TestNarratorList (assert, done, data) {

        /** The Ajax call */
        $.ajax({
            method: "POST",
            url: this.site_url + "/api/get_narrators",
            data: {
                "language": data.la
            }
		})
        /** This function is called after data has been loaded */
        .done(function( result ) {
            /** The data returned by the server is parsed */
            var result = JSON.parse(result);
            
            /** The message to show if test fails */
            let msg    = "The number of narrators is equal to " + data.result.nc + ". Got " + result.length;
            /** The number of narrators is tested */
            assert.ok( result.length == data.result.nc, msg);
            /** The test is marked as completed */
            done();
        });            
    }
    
    /** The function used to test navigation data */
    TestNavigatorConfig (assert, done, data) {

        /** The Ajax call */
        $.ajax({
            method: "POST",
            url: this.site_url + "/api/get_quran_nav_config",
            data: {
                "division": data.d, 
                "div_num": data.n, 
                "sura": data.s, 
                "sura_ruku": data.r, 
                "action": "ruku_box"
            }
		})
        /** This function is called after data has been loaded */
        .done(function( result ) {
            /** The data returned by the server is parsed */
            var result = JSON.parse(result);
            
            /** The message to show if test fails */
            let msg    = "The start ayat is equal to " + data.result.sa + ". Got " + result.start_ayat;
            /** The start ayat number is tested */
            assert.ok( result.start_ayat == data.result.sa, msg);
            
            /** The message to show if test fails */
            msg    = "The end ayat is equal to " + data.result.ea + ". Got " + result.end_ayat;
            /** The end ayat number is tested */
            assert.ok( result.end_ayat == data.result.ea, msg);   
            
            /** The message to show if test fails */
            msg    = "The division number is equal to " + data.result.n + ". Got " + result.div_num;
            /** The division number is tested */
            assert.ok( result.div_num == data.result.n, msg);
            
            /** The message to show if test fails */
            msg    = "The sura number is equal to " + data.result.s + ". Got " + result.sura;          
            /** The sura number is tested */
            assert.ok( result.sura == data.result.s, msg);
            
            /** The message to show if test fails */
            msg    = "The ruku number is equal to " + data.result.r + ". Got " + result.sura_ruku;
            /** The ruku number is tested */
            assert.ok( result.sura_ruku == data.result.r, msg);
            
            /** The test is marked as completed */
            done();
        });            
    }
    
    /** The function used to test search results */
    TestSearchResults (assert, done, data) {

        /** The Ajax call */
        $.ajax({
            method: "POST",
            url: this.site_url + "/api/search_ayat",
            data: {
                "is_random": "no",            
                "language": data.la,
                "narrator": data.na,
                "page_number": 1,
                "search_text": data.st,                
                "results_per_page": 10
            }
		})
        /** This function is called after data has been loaded */
        .done(function( result ) {
            /** The data returned by the server is parsed */
            var result = JSON.parse(result);
            
            /** The message to show if test fails */
            let msg    = "The number of search results is equal to " + data.result.rc + ". Got " + result.search_results.length;
            /** The number of search results is tested */
            assert.ok( result.search_results.length == data.result.rc, msg);
            /** The test is marked as completed */
            done();
        });            
    }
    
    /** The function used to test random verses */
    TestRandomVerses (assert, done, data) {

        /** The Ajax call */
        $.ajax({
            method: "POST",
            url: this.site_url + "/api/get_random_verses",
            data: {          
                "language": data.la,
                "narrator": data.na               
            }
		})
        /** This function is called after data has been loaded */
        .done(function( result ) {
            /** The data returned by the server is parsed */
            var result = JSON.parse(result);
            
            /** The message to show if test fails */
            let msg    = "The random verse text length should be 4 or more characters. Got " + result.translation.length;
            /** The length of random verse translation is tested */
            assert.ok( result.translation.length >= 4, msg);
            /** The test is marked as completed */
            done();
        });            
    }
}

/** The HolyQuran test class */
class HolyQuran {

    /** The function used to run a single test for each test data */
    RunTests (func, test_data, assert, assert_count) {
        /** The number of expected asserts */
        assert.expect(test_data.length * assert_count);
        /** An object of class HolyQuranTestFunctions is created */
        var test_functions = new HolyQuranTestFunctions();
        /** The test is run for each test data */
        for (let count = 0; count < test_data.length; count++) {
            /** Indicates async test */
            var done  = assert.async();
            /** The suras are tested */
            test_functions[func](assert, done, test_data[count]);
        }
    }

    /** The Holy Quran Navigator functions are tested */
    TestHolyQuranNavigator () {

        /** The API call for fetching sura information is tested */
        QUnit.test("Sura Test", assert => {
            /** The test data */
            var data = [
                {d:"hizb", n:10, result: {e:1}},
                {d:"juz", n:5, result: {e:1}},
                {d:"ruku", n:1, result: {e:114}}
            ];
            /** The test function is run for each test data */
            this.RunTests("TestSuras", data, assert, 1);            
        });

        /** The API call for fetching verse information is tested */
        QUnit.test("Verse Test", assert => {
            /** The test data */
            var data = [
                {sa: 1, ea: 7, s: 1, result: {vc:7}},
                {sa: 10, ea: 15, s: 2, result: {vc:6}},
                {sa: 15, ea: 30, s: 3, result: {vc:16}}
            ];
            /** The test function is run for each test data */
            this.RunTests("TestVerses", data, assert, 1);
        });

        /** The API call for fetching verse information is tested */
        QUnit.test("Ruku List Test", assert => {
            /** The test data */
            var data = [
                {d: "manzil", n: 2, s: 5, result: {sr:1, er:16}},
                {d: "hizb", n: 4, s: 2, result: {sr:7, er:9}},
                {d: "juz", n: 6, s: 5, result: {sr:1, er:11}}
            ];
            /** The test function is run for each test data */
            this.RunTests("TestRukuList", data, assert, 2);        
        });
        
        /** The API call for fetching updated navigator information is tested */
        QUnit.test("Navigator Config Test", assert => {
            /** The test data */
            var data = [
                {d: "ruku", n: 3, s: 2, r: 4, a: "ruku_box", result: {ea: 39, sa: 30, n: 5, s: 2, r: 4}},
            ];
            /** The test function is run for each test data */
            this.RunTests("TestNavigatorConfig", data, assert, 5);        
        });
        
        /** The API call for fetching language information is tested */
        QUnit.test("Language List Test", assert => {
            /** The test data */
            var data = [
                {result: {lc:43}}
            ];
            /** The test function is run for each test data */
            this.RunTests("TestLanguageList", data, assert, 1);        
        });
        
        /** The API call for fetching narrator information is tested */
        QUnit.test("Narrator List Test", assert => {
            /** The test data */
            var data = [
                {la: "English", result: {nc:16}},
                {la: "Urdu", result: {nc:8}}
            ];
            /** The test function is run for each test data */
            this.RunTests("TestNarratorList", data, assert, 1);        
        });
        
        /** The API call for fetching search results is tested */
        QUnit.test("Search Results Test", assert => {
            /** The test data */
            var data = [
                {st: "رحمان", na: "Abul A'ala Maududi", ir: "no", pn: 1, rp: 10, la: "Urdu", result: {rc: 10}},
            ];
            /** The test function is run for each test data */
            this.RunTests("TestSearchResults", data, assert, 1);        
        });
        
        /** The API call for fetching random verses is tested */
        QUnit.test("Random Verse Test", assert => {
            /** The test data */
            var data = [
                {la: "Urdu", na: "Abul A'ala Maududi", result: {}},
            ];
            /** The test function is run for each test data */
            this.RunTests("TestRandomVerses", data, assert, 1);        
        });
    }
}

/** The Holy Quran class is defined and exported using class expression */
export let holyquran = new HolyQuran();
