<?php

//include 'init.php';

/**
 * define application name
 */
define('APP_NAME', 'app');

/**
 * define application directory
 */
define('DIR_APP', __DIR__);

//environment
define('ENVIRONMENT', 'development');

//base path
define('APP_PATH', DIR_APP .DIRECTORY_SEPARATOR. APP_NAME);

//config folder
define('CACHE_PATH', APP_PATH . '/cache/');

//config folder
define('CONFIG_PATH', APP_PATH . '/config/');

/**
 * controller path
 */
define('CONTROLLER_PATH', APP_PATH . '/controllers/');

/*
 * models folder
 */
define('MODELS_PATH', APP_PATH . '/models/');
/*
 * views files folder
 */
define('VIEWS_PATH', APP_PATH . '/views/');
/*
 * logs files folder
 */
define('LOGS_PATH', APP_PATH . '/logs/');

/*
 * error files folder
 */
define('ERROR_PATH', APP_PATH . '/errors/');

/*
 * error files folder
 */
define('VENDOR_PATH', APP_PATH . '/vendor/');

/**
 * include bootsrap file
 */
require_once '../framework/system/Bootstraper.php';

/**
 * run the application
 */
App::instance()->run();
?>
