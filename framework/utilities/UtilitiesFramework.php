<?php

declare(strict_types=1);

namespace Framework\Utilities;

/**
 *	This class provides Factory method for fetching objects of utility classes
 *
 * @category   UtilityClass
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
abstract class UtilitiesFramework
{
    /**
     * @var array $object_alias_list
     * List of aliases for utility object classes
     * An object class can be accessed using its alias name
     * The class alias can be used if the class name 
     * Cannot be calculated from the file name	 
     */
    static private $object_alias_list = array(
        "database" => "DatabaseManager\Database",
        "dbcachemanager" => "DatabaseManager\DbCacheManager",
        "dbinitializer" => "DatabaseManager\DbInitializer",
        "dblogmanager" => "DatabaseManager\DbLogManager",
        "dbmetaqueryrunner" => "DatabaseManager\DbMetaQueryRunner",
        "logmanager" => "LogManager",
        "errormanager" => "ErrorManager\ErrorManager",
        "filemanager" => "FileSystem\FileManager",
        "foldermanager" => "FileSystem\FolderManager",    										
        "urlmanager" => "FileSystem\UrlManager",    										
        "stringutils" => "StringUtils",
        "cachemanager" => "CacheManager",
        "templateutils" => "TemplateUtils",
        "profiler" => "Profiler",
        "parser" => "CommentManager\Parser",
        "commentmanager" => "CommentManager\CommentManager",
        "validator" => "CommentManager\Validator",
        "dbdeletequerybuilder" => "DatabaseManager\DbQueryBuilder\DbDeleteQueryBuilder",
        "dbinsertquerybuilder" => "DatabaseManager\DbQueryBuilder\DbInsertQueryBuilder",
        "dbselectquerybuilder" => "DatabaseManager\DbQueryBuilder\DbSelectQueryBuilder",
        "dbupdatequerybuilder" => "DatabaseManager\DbQueryBuilder\DbUpdateQueryBuilder"
    );
    /**
     * @var array $object_list List of utility objects supported by the utilities framework		 
     */
    static private $object_list = array();
    /**
     * This method provides an object instance of the required utility class
     * 
     * It calculates the hash of the parameters and stores the object in an array with the hash as the array key
     * This method can instantiate and store multiple instances of an object
     * For example an application can request multiple instances of a database abstraction object
     *      
     * @param string $object_type the type of the object that is required. e.g utilities, logging, encrpytion, database etc
     * The $object_type must match the file name of the utility object class. e.g if the file name is authentication.class.php then the $object_type should be authentication
     * The $object_type can also match an alias defined in the $object_alias_list static property
     * @param array $parameters the optional parameters for the object. for e.g for database object it will contain the database connection information
     * 
     * @return object $utility_object an object of the required utility class		 
     */
    final public static function Factory(string $object_type, array $parameters = array()) : object
    {
    	/** The data is base64 encoded */
        $object_hash               = base64_encode(json_encode($parameters));
        
        /** Each stored object is checked */
        foreach (UtilitiesFramework::$object_list as $stored_object_hash => $stored_object) {
            /** If the stored object hash matches the hash of the given parameters, then the stored object is returned */
            if ($stored_object_hash == $object_hash)
                return $stored_object;
        }
        
        /** If the object type matches a class alias then it is set to the class name */
        $object_type               = self::$object_alias_list[$object_type] ?? $object_type;
        /** Otherwise the class name is calculated */
        $class_name                = '\Framework\Utilities\\' . ucfirst($object_type);
        /** The callback function for fetching Singleton instance of utility class */
        $callable_singleton_method = array($class_name, "GetInstance");
        /** If the method is callable */
        if (is_callable($callable_singleton_method)) {
            /** If the parameters are given as associative array, then they are converted to numeric indexed array */
            if (!isset($parameters[0])) {
                $parameters        = array($parameters);
            }
            /** The utility class instance is fetched */
            $utility_object        = call_user_func_array($callable_singleton_method, $parameters);
        }
        /** Otherwise the utility object is created using new operator */
        else {
            $utility_object        = new $class_name($parameters);
        }
        /** The object is added to the list of objects */
        UtilitiesFramework::$object_list[$object_type][$object_hash] = $utility_object;
        
        return $utility_object;        
    }
}
