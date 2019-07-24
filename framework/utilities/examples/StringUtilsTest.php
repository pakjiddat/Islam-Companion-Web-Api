<?php

declare(strict_types=1);

namespace Framework\Utilities\Examples;

use \Framework\Utilities\UtilitiesFramework as UtilitiesFramework;

error_reporting(E_ALL);
ini_set("display_errors", "1");
include('../autoload.php');

/**
 * Provides functions for test StringUtils package
 *
 * @category   UtilityClassTests
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General public License, version 2
 */
final class StringUtilsTest
{
	/**
     * Used to export given data to RSS format
     */
    public function TestStringUtils() : void
    {
        /** The StringUtils class object is fetched */
        $stringutils = UtilitiesFramework::Factory("stringutils");
        /** The data that needs to be exported to rss format */
        $data = array(
            0 => array(
                'title' => 'Big Game Spot (Deutsch)',
                'image' => 'http://i.ytimg.com/vi/ohimezN65sE/0.jpg',
                'description' => 'Terminator: Genisys',
                'source' => 'http://www.youtube.com/watch?v=ohimezN65sE'
            ) ,
            1 => array(
                'title' => 'Featurette "James Cameron',
                'image' => 'http://i.ytimg.com/vi/EcfmsxWf3X4/0.jpg',
                'description' => 'Terminator: Genisys',
                'source' => 'http://www.youtube.com/watch?v=Kt_2nYcVwWc'
            ) ,
            2 => array(
                'title' => 'Featurette "John Connor"',
                'image' => 'http://i.ytimg.com/vi/ohimezN65sE/0.jpg',
                'description' => 'Terminator: Genisys',
                'source' => 'http://www.youtube.com/watch?v=EcfmsxWf3X4'
            ) ,
            3 => array(
                'title' => 'Featurette "Kyle Reese"',
                'image' => 'http://i.ytimg.com/vi/9meOuhDHJ80/0.jpg',
                'description' => 'Terminator: Genisys',
                'source' => 'http://www.youtube.com/watch?v=9meOuhDHJ80'
            ) ,
            4 => array(
                'title' => 'Featurette "Sarah Connor"',
                'image' => 'http://i.ytimg.com/vi/klDV4nM7fG4/0.jpg',
                'description' => 'Terminator: Genisys',
                'source' => 'http://www.youtube.com/watch?v=klDV4nM7fG4'
            )
        );
        /** The list of tags that need to be prefixed with namespace */
        $namespace_attributes = array(
            "image",
            "source"
        );
        /** The xml namespace */
        $xml_namespace = array(
            "prefix" => "jwplayer",
            "name" => "jwplayer",
            "uri" => "http://rss.jwpcdn.com/"
        );
        /** The data is export as rss string */
        $rss_file = $stringutils->ExportToRss($data, $xml_namespace, $namespace_attributes);
        
        /** The RSS string contents are displayed */
        echo $rss_file;
    }
}

/** An object of class StringUtilsTest is created */
$stringutils_test            = new StringUtilsTest();
/** The TestProfiler function is called */
$stringutils_test->TestStringUtils();
