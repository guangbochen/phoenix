<?php
date_default_timezone_set('Australia/Sydney');

// Autoload composer packages
require './vendor/autoload.php';

$app = new \Slim\Slim();
$app->response()->header('Content-Type', 'application/json');
$app->response()->header('Access-Control-Allow-Origin', '*');
$app->response()->header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

// SETUP ENVIRONMENT
define('ENVIRONMENT', 'development');

/*
 *---------------------------------------------------------------
 * ERROR REPORTING
 *---------------------------------------------------------------
 *
 * Different environments will require different levels of error reporting.
 * By default development will show errors but testing and live will hide them.
 */

if (defined('ENVIRONMENT'))
{
	switch (ENVIRONMENT)
	{
		case 'development':
			error_reporting(E_ALL);
            ini_set('display_errors', TRUE);
            ini_set('display_startup_errors', TRUE);
		break;

		case 'testing':
		case 'production':
			error_reporting(0);
		break;

		default:
			exit('The application environment is not set correctly.');
	}
}

// Config database
require './config/database.php';

/* // Load routers */
require 'routes.php';

/* /1* // Test data - will be removed in production *1/ */
require 'controllers/seeds.php';

$app->run();
