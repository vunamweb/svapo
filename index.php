<?php
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(0);
// Version
// phpinfo();
define('VERSION', '3.0.3.9');

// Configuration
if (is_file('config.php')) {
	require_once('config.php');
}

// print_r($_COOKIE);
// Install
if (!defined('DIR_APPLICATION')) {
	header('Location: install/index.php');
	exit;
}

// Startup
require_once(DIR_SYSTEM . 'startup.php');

start('catalog');
