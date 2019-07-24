<?php

declare(strict_types=1);

namespace Framework\Utilities\FileSystem;

/**
 * This class provides functions for managing folders
 *
 * @category   UtilityClass
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class FolderManager
{
    /** @var FileSystem $instance The single static instance */
    protected static $instance;
    
    /**
     * Class constructor
     * Used to prevent creating an object of this class outside of the class using new operator
     *
     * Used to implement Singleton class
     */
    protected function __construct() 
    {
    }
    
    /**
     * Used to return a single instance of the class
     *
     * Checks if instance already exists. If it does not exist then it is created. The instance is returned
     *
     * @return FolderManager static::$instance name the instance of the correct child class is returned
     */
    public static function GetInstance() : FolderManager
    {
        if (static ::$instance == null) 
        {
            static ::$instance = new static ();
        }
        return static ::$instance;
    }
    
	/**
     * Reads the contents of the given folder
     *
     * @param string $folder_path absolute path to the folder whoose contents are to be read
     * @param int $max_levels the number of folder levels to search. if set to -1 then folders at all levels are checked
     * @param string $include_file_pattern optional the file pattern to check. these must be present in the file path
     * @param string $exclude_file_pattern optional the file pattern to check. these must not be present in the file path
     * @param boolean $include_folder_names indicates that the folder names should be included in return value
     * @param int $current_level indicates the current level of recursion
     *
     * @return array $valid_file_list list of files in folder. the . and .. items are removed from the list
     * if the file pattern is given then only files matching the pattern are fetched 
     */
    public function GetFolderContents(
        string $folder_path,
        int $max_levels = 1,
        string $include_file_pattern = "",
        string $exclude_file_pattern = "",
        bool $include_folder_names = false,
        int $current_level = 1
    ) : array {
    
        /** If the folder is not readable then an exception is thrown */
        if (!is_dir($folder_path)) throw new \Error("Error in reading folder: " . $folder_path);
        /** The list of files in folder is fetched */
        $file_list         = scandir($folder_path);
        /** The . and .. entries are removed from list */
        $file_list         = array_slice($file_list, 2);
        /** Valid file list */
        $valid_file_list   = array();
        /** Each file name is checked */
        for ($count = 0; $count < count($file_list); $count++) {
            /** The file/directory name */
            $item_name     = $folder_path . DIRECTORY_SEPARATOR . $file_list[$count];            
            /** If the include file pattern is empty or it matches the given file/directory */
            if ((is_file($item_name) || (is_dir($item_name) && $include_folder_names)) && 
                ($include_file_pattern == "" || strpos($item_name, $include_file_pattern) !== false)) {
                /** If the exclude file pattern is empty or it is not empty and it does not match the given file */
                if ($exclude_file_pattern == "" || strpos($item_name, $exclude_file_pattern) === false)
                    $valid_file_list[] = $item_name;
            }
            /** If the file is a directory then it is scanned */
            if (is_dir($item_name) && ($current_level < $max_levels || $max_levels == -1)) {
                /** The list of folders is fetched */
                $folder_list     = $this->GetFolderContents(
                                        $item_name,
                                        $max_levels,
                                        $include_file_pattern,
                                        $exclude_file_pattern,
                                        $include_folder_names,
                                        ($current_level+1)
                                   );
                $valid_file_list = array_merge($valid_file_list, $folder_list);
            }
        }
        /** The file list is returned */
        return $valid_file_list;
    }
    /**
     * Used to recursively copy a folder
     *
     * It copies the contents of a folder recursively from source folder to destination folder
     *
     * @param string $source_folder the full path to the source folder
     * @param string $target_folder the full path to the target folder
     * @param boolean $is_recursive used to indicate if the folder should be copied recursively
     */
    public function CopyFolder(string $source_folder, string $target_folder, bool $is_recursive) : void
    {
        /** The folder contents are fetched recursively */
        $folder_contents        = $this->GetFolderContents($source_folder, $is_recursive, "", "", true) ;
        /** For each file/folder in the source folder */
        for ($count = 0; $count < count($folder_contents); $count++) {
            /** The source item name */            
            $source_item_name   = $folder_contents[$count];
            /** The source folder is replaced with target folder */
            $target_item_name   = str_replace($source_folder, $target_folder, $source_item_name);
            /** If the item is a folder then it is created in the target folder */
            if (is_dir($source_item_name)) mkdir($target_item_name);
            /** If the item is a file and the target file does not exist, then it is copied to target folder */
            if (is_file($source_item_name) && !is_file($target_item_name)) copy($source_item_name, $target_item_name);
        }
    }
}
