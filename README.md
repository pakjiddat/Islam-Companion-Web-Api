<p><img class="img-fluid" src="https://www.pakjiddat.pk/pakjiddat/ui/images/islamcompanion-web-api.png" alt="Islam Companion Web API"/></p>
<h3>Introduction</h3>
<p>The "<b>Islam Companion Web API</b>" project is a RESTFul API (Application Programming Interface) that allows users to add Holy Quran and Hadith data to their applications. It provides Holy Quran translation in 42 languages. Following languages are supported: <b>Amharic, Arabic, Bosnian, Bengali, Bulgarian, Amazigh, Czech, German, Divehi, Spanish, English, Persian, French, Hindi, Hausa, Indonesian, Italian, Japanese, Korean, Kurdish, Malayalam, Malay, Dutch, Norwegian, Portuguese, Polish, Russian, Romanian, Swedish, Somali, Sindhi, Albanian, Swahili, Turkish, Tajik, Tamil, Tatar, Thai, Uzbek, Urdu, Uyghur and Chinese</b>. Hadith translation is provided in <b>Urdu, English and Arabic languages</b></p>
<p>An example of a website that uses the Islam Companion Web API is the <a href='https://islamcompanion.pakjiddat.pk/'>Islam Companion website</a>. The <a href='https://islamcompanion.pakjiddat.pk/holy-quran'>Holy Quran Reader</a> and <a href='https://islamcompanion.pakjiddat.pk/hadith'>Hadith Reader</a> were developed using the Islam Companion Web API</p>
<p>The goal of the Islam Companion Web API is to help users developed applications that promote knowledge about Islam.</p>
<h3>Features</h3>
<p>The Islam Companion Web API has the following features:</p>
<div>
  <ul>
    <li>It provides translations of Holy Quran in 42 languages</li>
    <li>It provides translations of Hadith in Urdu, English and Arabic languages</li>
    <li>It provides 8 API functions for fetching Quranic data</li>
    <li>It provides 7 API functions for fetching Hadith data</li>
    <li>The source code is available under <a href='https://github.com/nadirlc/islamcompanion-web-api/blob/master/LICENSE'>GPL License</a></li>
    <li>The source code is well commented and easy to update</li>
  </ul>
</div>
<h3>Requirements</h3>
<p>The Islam Companion Web API requires Php >= 7.2. It also requires MySQL server >= 5.6.</p>
<h3>Installation</h3>
<p>The following steps can be used to install the "Islam Companion Web API" project on your own server:</p>
<div>
  <ul>
    <li>Download the <a href='https://github.com/nadirlc/islamcompanion-web-api/archive/master.zip'>source code</a> from GitHub</li>
    <li>Move the source code to the document root of a virtual host</li>
    <li>Download the contents of the database from: <a href='https://islamcompanion.pakjiddat.pk/islamcompanion/data/islamcompanion-website.sql.tar.bz2'>here</a></li>
    <li>Extract the downloaded file</li>
    <li>Create a database and import the contents of the sql file to the database. Note down the credentials used for connecting to the database</li>
    <li>Enter the database credentials in the file <b>api/config/RequiredObjects.php</b></li>
    <li>In the file: <b>api/Config.php</b>, on <b>line 37</b> enter the domain names that will be used to access the api</li>
    <li>Customize the following variables in the file: <b>api/config/General.php</b>. <b>$config['app_name'], $config['dev_mode'] and $config['site_url']</b></li>
  </ul>
</div>

<h3>Download Hadith data</h3>
<p>We have compiled a Hadith database with the purpose of spreading knowlege of Hadith. The database should be used by developers in their own applications. The Hadith database contains text in Urdu, English and Arabic languages.</p>

<p><a href='https://islamcompanion.pakjiddat.pk/islamcompanion/data/hadith.sql.tar.bz2'>Click here</a> to download the Hadith database in <b>.sql</b> format for MySQL server. <a href='https://islamcompanion.pakjiddat.pk/islamcompanion/data/hadith.db.tar.bz2'>Click here</a> to download the Hadith database in <b>.db</b> format for SQLite server</p>

<h3>Frequently asked questions</h3>
<div>
  <ul>
    <li><b>What is the Islam Companion Web API</b>. It is a RESTFul API for accessing Holy Quran and Hadith data</li>
    <li><b>What can I do with the Islam Companion Web API</b>. You can develop web based applications that present Holy Quran and Hadith data to the user. The Islam Companion Api can work as the backend of your application.</li>
    <li><b>Which languages are supported by the Islam Companion Web API</b>. <a href="#introduction">Click Here</a></li>
    <li><b>What functions are provided by the Islam Companion Api</b>. Please read the documentation.</li>
    <li><b>From where does the API get its data</b>. The Islam Companion API uses Holy Quran translations from <a href='http://tanzil.net/trans/'>http://tanzil.net/trans/</a>. It uses Hadith data from <a href='http://hadithcollection.com/'>http://hadithcollection.com/</a>.</li>
    <li><b>How do I use the API</b>. To use the API, you have to make HTTP POST request to the server islamcompanion.pakjiddat.pk. Please see following sample code in Php language.</li>
  </ul>
</div>

<h3>Sample Code</h3>

```Php
<?php

$data = array(
    'language' => 'English',
    'narrator' => 'Mohammed Marmaduke William Pickthall'
);
 
// Prepare new cURL resource
$ch = curl_init('https://islamcompanion.pakjiddat.pk/api/get_random_verses');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLINFO_HEADER_OUT, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
 
// Submit the POST request
$result = curl_exec($ch);
 
// Close cURL session handle
curl_close($ch);

// json decode the response
$result = json_decode($result, true);

// Print the result
print_r($result);
?>
```

<h3>Usage</h3>
<p>The Islam Companion Web API supports the following functions. All function response values are in JSON format.</p>
<p>To test the API, use the following url: <a href='https://islamcompanion.pakjiddat.pk/api/'>https://islamcompanion.pakjiddat.pk/api/</a> for making API requests. All API requests must be of type HTTP POST and should include the required API parameters.</p>

<h3>API calls for fetching Holy Quran data</h3>

| Name | Description | Url | Parameters | Response |
| ---- | ----------- | --- | ---------- | -------- |
| Get Suras In Division | Returns the list of suras for the given division and division number | /api/get_suras_in_division | <ul><li><b>division</b>. The division name. It can be hizb,juz,page,manzil,ruku</li><li><b>div_num</b>. The division number. It should be a number 1 and 604</li></ul> | The names of all the suras that are in the given division: <ul> <li><b>tname</b>. The english name of the sura</li><li><b>ename</b>. A brief description of the sura in English</li><li><b>sindex</b>. The sura number. It should be a number 1 and 114</li></ul> |
| Get Ruku List | It provides the start and end ruku numbers for the given division, division number and sura | /api/get_ruku_list | <ul><li><b>division</b>. The division name. It can be hizb,juz,page,manzil,ruku</li><li><b>div_num</b>. The division number. It should be a number 1 and 604</li><li><b>sura</b>. The sura number. It should be a number 1 and 114</li></ul> | The start and end ruku numbers<ul><li><b>start_ruku</b>. The start sura ruku number. It should be a number 1 and 40</li><li><b>end_ruku</b>. The end sura ruku number. It should be a number 1 and 40</li></ul> |
| Get Verses | It returns the arabic text and translation for the given verses | /api/get_verses | <ul><li><b>start_ayat</b>. The start ayat number</li><li><b>end_ayat</b>. The end ayat number</li><li><b>language</b>. The language for the verse text</li><li><b>narrator</b>. The translator name</li><li><b>sura</b>. The sura number</li></ul> | The list of required ayas<ul><li><b>arabic_text</b>. The arabic text</li><li><b>translation</b>. The translated text</li><li><b>sura_id</b>. The sura id</li><li><b>sura_name</b>. The sura name</li><li><b>ayat</b>. The ayat number</li></ul> |
| Get Random Verses | It returns the text for a random ruku along with meta data | /api/get_random_verses | <ul><li><b>language</b>. The language for the verse text</li><li><b>narrator</b>. The translator name</li></ul> | The verse data<ul><li><b>arabic</b>. The verse text in arabic</li><li><b>translation</b>. The translated text</li><li><b>meta_data</b>. The ruku meta data</li></ul> |
| Get Holy Quran Navigator Configuration | It generates the navigator configuration data for the given action | /api/get_quran_nav_config | <ul><li><b>action</b>. The action taken by the user</li><li><b>div_num</b>. The current division number</li><li><b>division</b>. The current division</li><li><b>sura</b>. The current sura</li><li><b>sura_ruku</b>. The current sura ruku</li></ul> | The updated Navigator configuration data<ul><li><b>sura</b>. The new sura</li><li><b>sura_ruku</b>. The new ruku id</li><li><b>start_ayat</b>. The new start ayat</li><li><b>end_ayat</b>. The new end ayat</li><li><b>div_num</b>. The new division number</li><li><b>audiofile</b>. The base audio file name</li></ul> |
| Get Languages | It returns the list of all supported languages | /api/get_languages | None | The list of all supported languages |
| Get Narrators | It returns the list of all supported narrators for the given language | /api/get_narrators | <ul><li><b>language</b>. The language for the verse text</li></ul> | The list of all supported narrators |
| Search Ayat | It returns list of ayas that contain the given text | /api/search_ayat | <ul><li><b>is_random</b>. Indicates if random search results should be fetched</li><li><b>language</b>. Custom the language for the verse text</li><li><b>narrator</b>. The translator name</li><li><b>page_number</b>. The search results page number</li><li><b>results_per_page</b>. The number of results per page</li><li><b>search_text</b>. The search text</li></ul> | Contains the search results and total result count<ul><li><b>search_results</b>. The verse data that contains the given text</li><li><b>result_count</b>. The total number of results</li></ul> |

<h3>API calls for fetching Hadith data</h3>

| Name | Description | Url | Parameters | Response |
| ---- | ----------- | --- | ---------- | -------- |
| Get Hadith Books | It returns the list of Hadith books for the given Hadith source | /api/get_hadith_books | <ul><li><b>language</b>. The hadith language</li><li><b>source</b>. The hadith source for which the books need to be fetched</li></ul> | The list of Hadith books<ul><li><b>id</b>. The hadith book id</li><li><b>book</b>. The hadith book</li></ul> | 
| Get Hadith Titles | It fetches list of Hadith book titles for the given Hadith book and source | /api/get_hadith_titles | <ul><li><b>book_id</b>. The hadith book id</li><li><b>language</b>. The hadith language</li></ul> | The list of hadith book titles<ul><li><b>id</b>. The hadith title id</li><li><b>title</b>. The hadith title</li></ul> |
| Get Hadith | It fetches list of Hadith text for the given Hadith title and book id | /api/get_hadith | <ul><li><b>language</b>. The hadith language</li><li><b>title_id</b>. The hadith title id</li></ul> | The list of Hadith<ul><li><b>text</b>. The hadith text</li><li><b>title</b>. The hadith title</li><li><b>title_id</b>. The hadith title id</li><li><b>book_id</b>. The hadith book id</li><li><b>book</b>. The hadith book name</li><li><b>number</b>. The hadith number</li><li><b>source</b>. The hadith source</li></ul> |
| Get Random Hadith | It fetches list of random hadith text | /api/get_random_hadith | <ul><li><b>hadith_count</b>. The number of hadith to fetch</li><li><b>language</b>. The hadith language</li></ul> | The hadith text<ul><li><b>text</b>. The hadith text</li><li><b>source</b>. The hadith source</li><li><b>book</b>. The hadith book name</li><li><b>number</b>. The hadith number</li></ul><td>
| Get Hadith Sources | It fetches list of hadith sources for the given language | /api/get_hadith_sources | <ul><li><b>language</b>. The hadith language</li></ul> | The list of hadith sources<ul><li><b>hadith_sources</b>. The hadith sources</li></ul> |
| Get Hadith Navigator Configuration | It returns the navigator configuration for given navigator action | /api/get_hadith_nav_config | <ul><li><b>action</b>. The action taken by the user</li><li><b>book_id</b>. The hadith book id</li><li><b>language</b>. The hadith language</li><li><b>source</b>. The hadith source</li><li><b>title_id</b>. The hadith book title id</li></ul> | The updated Navigator configuration data<ul><li><b>source</b>. The new Hadith source</li><li><b>book_id</b>. The new Hadith book id</li><li><b>title_id</b>. The new Hadith book title id</li></ul> |
| Search Hadith | It returns list of hadith that contain the given text | /api/search_hadith | <ul><li><b>is_random</b>. Indicates if random search results should be fetched</li><li><b>language</b>. The language for the hadith text</li><li><b>page_number</b>. The search results page number</li><li><b>results_per_page</b>. The number of results per page</li><li><b>search_text</b>. The search text</li></ul> | Contains the search results and total result count<ul><li><b>search_results</b>. The search results</li><li><b>result_count</b>. The total number of results</li></ul> |
