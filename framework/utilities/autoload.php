<?php

declare(strict_types=1);

namespace Framework\Utilities;

/** All errors are reported and displayed */
ini_set("display_errors", "1");
error_reporting(E_ALL);

/**
 * Function used to auto load the framework classes
 * 
 * The class is assumed to be in the current folder
 * The class file names match the class name. e.g the class
 * Encryption is in the file encryption.php
 * So the classes can be autoloaded by any psr-4 compliant autoloader
 * 
 * @param string $class_name name of the class that needs to be included
 */
function autoload_framework_classes(string $class_name) : void
{
	/** If the required class is in the global namespace then no need to autoload the class */
    if (strpos($class_name, "\\") === false) return;
    /** The namespace seperator is replaced with directory seperator */
    $class_name            = str_replace("\\", DIRECTORY_SEPARATOR, $class_name);
    /** The class name is split into namespace and short class name */
    $path_info             = explode(DIRECTORY_SEPARATOR, $class_name);
    /** The namepsace is extracted */
    $namespace             = strtolower(implode(DIRECTORY_SEPARATOR, array_slice($path_info, 0, count($path_info)-1)));
    /** The class name is extracted */
    $class_name            = $path_info[count($path_info)-1];
    /** The namespace is converted to lower case */
    $namespace_folder      = trim(str_replace("framework" . DIRECTORY_SEPARATOR . "utilities", "", $namespace), DIRECTORY_SEPARATOR);
    /** .php is added to class name */
    $class_name            = $class_name.".php";
    /** The applications folder name */
    $framework_folder_path = realpath(dirname(__FILE__));
	
    /** The application folder is checked for file name */
    $file_name             = $framework_folder_path . DIRECTORY_SEPARATOR . $namespace_folder . DIRECTORY_SEPARATOR . $class_name;

    if (is_file($file_name))
        include_once($file_name);	  
}
	
/**
 * Registers the autoload function
 */
spl_autoload_register("\Framework\Utilities\autoload_framework_classes", true, true);

?>
