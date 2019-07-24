<?php

declare(strict_types=1);

/**
 * The application bootstrap file
 *
 * This file is the main entry point for the application
 * All url requests to the application are handled by this file
 *
 * @link              http://www.pakjiddat.pk
 * @package           Framework
 *
 * Description:       Pak Php Framework
 * Version:           2.0
 * Author:            Nadir Latif
 * Author URI:        http://www.pakjiddat.pk
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       pakphp
 */
namespace Framework;
/** The autoload.php file is included */
require ("autoload.php");

/** The application parameters */
$parameters = (isset($argc)) ? $argv : $_REQUEST;
/** The application request is handled */
$output     = \Framework\Application\Application::RunApplication($parameters);
/** If the output is not suppressed then the application output is echoed back */
if (!defined("NO_OUTPUT")) echo $output;

