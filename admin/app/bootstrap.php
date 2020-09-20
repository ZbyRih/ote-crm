<?php
if(version_compare(PHP_VERSION, '5.6.0', '<')){
	die('Require PHP Version 5.6.0 and above');
}

if(!defined('PHP_VERSION_ID')){
	$version = explode('.', PHP_VERSION);
	define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

require __DIR__ . '/../../vendor/autoload.php';

require_once LIBS_DIR . '/obe/obe.init.php';

if(@file_put_contents(APP_DIR_OLD . '/temp/_cache', '') === FALSE){
	throw new \Exception('Make directory \'' . APP_DIR_OLD . '/temp/_cache' . '\' writable!');
}

$loader = new Nette\Loaders\RobotLoader();
$loader->addDirectory(APP_DIR_OLD);
$loader->addDirectory(WWW_DIR_OLD . '/../app/');
$loader->addDirectory(LIBS_DIR);

// nastavíme cachování na disk do adresáře 'temp'

$loader->setCacheStorage(new Nette\Caching\Storages\FileStorage(APP_DIR_OLD . '/temp'));
$loader->register(); // spustíme RobotLoader

require_once APP_DIR_OLD . '/config/defines.php'; // MODULES a sou v tom hlavně klíče

$time_e = microtime(true);