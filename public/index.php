<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);
date_default_timezone_set('Europe/Paris');

defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

// Chemin vers l'application
define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Inclure la bibliothèque Zend
set_include_path(implode(PATH_SEPARATOR, [
    realpath(APPLICATION_PATH . '/../library'),
    realpath(APPLICATION_PATH . './application/models/'),
    get_include_path(),
]));

require_once 'Zend/Application.php';

// Créer l'application Zend
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap()->run();