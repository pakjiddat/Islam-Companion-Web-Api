<?php

declare(strict_types=1);

namespace Framework\Application\Libraries;

use \Framework\Config\Config as Config;

/**
 * This class provides functions that allow translating text
 *
 * @category   Libraries
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class Translation
{
    /**
     * It returns the text for the given context and key
     *
     * @param string $context the context for the text
     * @param string $text_key the text key
     *    
     * @return string $translated_text the text for the given key and context is returned
     */
    public function GetText(string $context, string $text_key) : string
    {
        /** The translated data is fetched */
        $translated_data         = Config::$config["general"]["site_text"];

        /** The long text */
        $long_text               = (!$text_key) ? $translated_data : $translated_data[$context][$text_key];
        
        return $long_text;
    }
    /**
     * This function reads the translation file provided by the application
     * The translation data is saved to application config
     */
    public function ReadTranslationText() : void
    {
        /** The site text */
        $site_text     = array();
        /** The absolute path to the language file */
        $language_file = Config::$config["path"]["language_folder_path"] . DIRECTORY_SEPARATOR .
                         Config::$config["general"]["language"] . ".txt";
        /** If language file exists */
        if (is_file($language_file)) {            
            /** If the language file exists then it is read */
            $translation_data = UtilitiesFramework::Factory("urlmanager")->GetFileContent($language_file);
            /** The language file contents are converted to array */
            $translation_data = explode("\n", trim($translation_data));
            /** Each translation data item is parsed */
            for ($count = 0; $count < count($translation_data); $count++) {
                /** If the line is empty, then the loop continues */
                if ($translation_data[$count] == '') continue;
                /** The translation line */
                list($context, $key, $value)  = explode("~", $translation_data[$count]);
                /** The site text */
                $site_text[$context][$key]    = $value;
            }
            /** The site text is saved to application config */
            Config::$config["general"]["site_text"] = $site_text;
        }
    }
}
