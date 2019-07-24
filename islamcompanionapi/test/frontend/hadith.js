"use strict";

/** The HadithTestFunctions class */
class HadithTestFunctions {

    /** The constructor */
    constructor() {
        /** The site url */
        this.site_url = "";
    }
    
    /** The function used to test if the number of Hadith books is correct */
    TestBookCount (assert, done, data) {

        /** The Ajax call */
        $.ajax({
            method: "POST",
            url: this.site_url + "/api/get_hadith_books",
            data: {
                "source": data.source, 
                "language": data.language
            }
		})
        /** This function is called after data has been loaded */
        .done(function( result ) {
            /** The data returned by the server is parsed */
            var result = JSON.parse(result);
            
            /** The message to show if test fails */
            let msg    = "The Hadith book count is equal to " + data.result.bc + ". Got " + result.length;
            /** The number of Hadith books is tested */
            assert.ok( result.length == data.result.bc, msg);
            
            /** The test is marked as completed */
            done();
        });            
    }
    
    /** The function used to test if the number of Hadith book titles is correct */
    TestBookTitleCount (assert, done, data) {

        /** The Ajax call */
        $.ajax({
            method: "POST",
            url: this.site_url + "/api/get_hadith_titles",
            data: {
                "source": data.source, 
                "language": data.language,
                "book_id": data.book_id
            }
		})
        /** This function is called after data has been loaded */
        .done(function( result ) {
            /** The data returned by the server is parsed */
            var result = JSON.parse(result);
            
            /** The message to show if test fails */
            let msg    = "The Hadith book title count is equal to " + data.result.tc + ". Got " + result.length;
            /** The number of Hadith books is tested */
            assert.ok( result.length == data.result.tc, msg);
            
            /** The test is marked as completed */
            done();
        });            
    }
    
    /** The function used to test if the Hadith count is correct */
    TestHadithCount (assert, done, data) {

        /** The Ajax call */
        $.ajax({
            method: "POST",
            url: this.site_url + "/api/get_hadith",
            data: {
                "title_id": data.title_id, 
                "language": data.language
            }
		})
        /** This function is called after data has been loaded */
        .done(function( result ) {
            /** The data returned by the server is parsed */
            var result = JSON.parse(result);
            
            /** The message to show if test fails */
            let msg    = "The Hadith text count is equal to " + data.result.hc + ". Got " + result.length;
            /** The length of the Hadith text is tested */
            assert.ok( result.length == data.result.hc, msg);
            
            /** The test is marked as completed */
            done();
        });            
    }
    
    /** The function used to test if the updated Hadith Navigator Config data is correct */
    TestHadithNavigatorConfig (assert, done, data) {

        /** The Ajax call */
        $.ajax({
            method: "POST",
            url: this.site_url + "/api/get_hadith_nav_config",
            data: {
                "source": data.source, 
                "book_id": data.book_id,
                "title_id": data.title_id,
                "language": data.language,
                "action": data.action
            }
		})
        /** This function is called after data has been loaded */
        .done(function( result ) {
            /** The data returned by the server is parsed */
            var result = JSON.parse(result);
            
            /** The message to show if test fails */
            let msg    = "The Hadith source is equal to " + data.result.s + ". Got " + result.source;
            /** The Hadith source is tested */
            assert.ok( result.source == data.result.s, msg);
            
            /** The message to show if test fails */
            msg    = "The Hadith book is equal to " + data.result.b + ". Got " + result.book_id;
            /** The Hadith book is tested */
            assert.ok( result.book_id == data.result.b, msg);
            
            /** The message to show if test fails */
            msg    = "The Hadith title is equal to " + data.result.t + ". Got " + result.title_id;
            /** The Hadith title is tested */
            assert.ok( result.title_id == data.result.t, msg);
            
            /** The test is marked as completed */
            done();
        });            
    }
    
    /** The function used to test if the Hadith count is correct */
    TestHadithSources (assert, done, data) {

        /** The Ajax call */
        $.ajax({
            method: "POST",
            url: this.site_url + "/api/get_hadith_sources",
            data: {
                "language": data.language
            }
		})
        /** This function is called after data has been loaded */
        .done(function( result ) {
            /** The data returned by the server is parsed */
            var result = JSON.parse(result);
            
            /** The message to show if test fails */
            let msg    = "The Hadith source count is equal to " + data.result.c + ". Got " + result.length;
            /** The number of hadith sources is tested */
            assert.ok( result.length  == data.result.c, msg);
            
            /** The test is marked as completed */
            done();
        });            
    }
    
    /** The function used to test if the Hadith search results count is correct */
    TestHadithSearch (assert, done, data) {

        /** The Ajax call */
        $.ajax({
            method: "POST",
            url: this.site_url + "/api/search_hadith",
            data: {
                "is_random": data.is_random,
                "language": data.language,
                "page_number": data.page_number,
                "results_per_page": data.results_per_page,
                "search_text": data.search_text
            }
		})
        /** This function is called after data has been loaded */
        .done(function( result ) {
            /** The data returned by the server is parsed */
            var result = JSON.parse(result);
            
            /** The message to show if test fails */
            let msg    = "The Hadith search results count is equal to " + data.result.c + ". Got " + result.result_count;
            /** The number of search results is tested */
            assert.ok( result.result_count  == data.result.c, msg);
            
            /** The test is marked as completed */
            done();
        });            
    }
    
    /** The function used to test if the number of random hadith is correct */
    TestRandomHadith (assert, done, data) {

        /** The Ajax call */
        $.ajax({
            method: "POST",
            url: this.site_url + "/api/get_random_hadith",
            data: {
                "hadith_count": data.hadith_count,
                "language": data.language
            }
		})
        /** This function is called after data has been loaded */
        .done(function( result ) {
            /** The data returned by the server is parsed */
            var result = JSON.parse(result);
            
            /** The message to show if test fails */
            let msg    = "The Hadith count is equal to " + data.result.c + ". Got " + result.length;
            /** The number of results is tested */
            assert.ok( result.length  == data.result.c, msg);
            
            /** The test is marked as completed */
            done();
        });            
    }
}

/** The Hadith test class */
class Hadith {

    /** The function used to run a single test for each test data */
    RunTests (func, test_data, assert, assert_count) {
        /** The number of expected asserts */
        assert.expect(test_data.length * assert_count);
        /** An object of class HadithTestFunctions is created */
        var test_functions = new HadithTestFunctions();
        /** The test is run for each test data */
        for (let count = 0; count < test_data.length; count++) {
            /** Indicates async test */
            var done  = assert.async();
            /** The suras are tested */
            test_functions[func](assert, done, test_data[count]);
        }
    }

    /** The Hadith Navigator functions are tested */
    TestHadithNavigator () {
        /** The API call for fetching book information is tested */
        QUnit.test("Book Test", assert => {
            /** The test data */
            var data = [
                {source: '&#x633;&#x646;&#x646; &#x623;&#x628;&#x64A; &#x62F;&#x627;&#x648;&#x62F;', language: "Urdu", result: {bc: 40}},
                {source: '&#x633;&#x646;&#x646; &#x627;&#x628;&#x646; &#x645;&#x627;&#x62C;&#x6C1;', language: "Urdu", result: {bc: 36}},
                {source: '&#x633;&#x646;&#x646; &#x62A;&#x631;&#x645;&#x630;&#x6CC;', language: "Urdu", result: {bc: 44}},
                {source: '&#x633;&#x646;&#x646; &#x646;&#x633;&#x627;&#x626;&#x6CC;', language: "Urdu", result: {bc: 52}},
                {source: '&#x635;&#x62D;&#x6CC;&#x62D; &#x628;&#x62E;&#x627;&#x631;&#x6CC;', language: "Urdu", result: {bc: 95}}
            ];
            /** The test function is run for each test data */
            this.RunTests("TestBookCount", data, assert, 1);            
        });
        
        /** The API call for fetching book title information is tested */
        QUnit.test("Book Title Test", assert => {
            /** The test data */
            var data = [
                {book_id: "87", language: "Urdu", result: {tc: 6}}, 
                {book_id: "10", language: "English", result: {tc: 27}}, 
                {book_id: "15", language: "Arabic", result: {tc: 28}}
            ];
            /** The test function is run for each test data */
            this.RunTests("TestBookTitleCount", data, assert, 1);            
        });
        
        /** The API call for fetching book text is tested */
        QUnit.test("Hadith Count Test", assert => {
            /** The test data */
            var data = [
                {title_id: 3, language: "Urdu", result: {hc: 2}}
            ];
            /** The test function is run for each test data */
            this.RunTests("TestHadithCount", data, assert, 1);            
        });
        
        /** The API call for fetching updated Hadith navigator configuration is tested */
        QUnit.test("Hadith Navigator Config Test", assert => {
            /** The test data */
            var data = [
                {
                    source: "&#x635;&#x62D;&#x6CC;&#x62D; &#x628;&#x62E;&#x627;&#x631;&#x6CC;",
                    language: "Urdu",
                    book_id: 87,
                    title_id: 3,
                    action: "next",
                    result: {s: "صحیح بخاری", b: 87, t: 5}
                }
            ];
            /** The test function is run for each test data */
            this.RunTests("TestHadithNavigatorConfig", data, assert, 3);
        });
        
        /** The API call for fetching updated Hadith navigator configuration is tested */
        QUnit.test("Hadith Source Test", assert => {
            /** The test data */
            var data = [
                {
                    language: "English",
                    result: {c: 8}
                }
            ];
            /** The test function is run for each test data */
            this.RunTests("TestHadithSources", data, assert, 1);
        });
        
        /** The API call for searching Hadith text is tested */
        QUnit.test("Hadith Search Test", assert => {
            /** The test data */
            var data = [
                {
                    is_random: "no",
                    language: "English",
                    page_number: 1,
                    results_per_page: 5,
                    search_text: "house",
                    result: {c: 1257}
                }
            ];
            /** The test function is run for each test data */
            this.RunTests("TestHadithSearch", data, assert, 1);
        });
        
        /** The API call for searching random Hadith is tested */
        QUnit.test("Random Hadith Test", assert => {
            /** The test data */
            var data = [
                {
                    hadith_count: 10,
                    language: "English",
                    result: {c: 10}
                }
            ];
            /** The test function is run for each test data */
            this.RunTests("TestRandomHadith", data, assert, 1);
        });
    }
}

/** The Hadith class is defined and exported using class expression */
export let hadith = new Hadith();
