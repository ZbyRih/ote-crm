<?php

// absolute filesystem path to this web root
define('WWW_DIR_OLD', __DIR__);
// define('WWW_DIR_OLD', __DIR__ . '/..');

// absolute filesystem path to the application root
define('APP_DIR_OLD', WWW_DIR_OLD . '/app');
// define('APP_DIR_OLD', WWW_DIR_OLD . '/admin/app');

// absolute filesystem path to the libraries
define('LIBS_DIR', WWW_DIR_OLD . '/../libs');
// define('LIBS_DIR', WWW_DIR_OLD . '/libs');

define('APP_CONF', 'Admin');

define('IMG_REL', __DIR__ . '/../');

// load bootstrap file
require APP_DIR_OLD . '/bootstrap.php';