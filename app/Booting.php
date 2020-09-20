<?php

namespace App;

use Nette\Configurator;
use Tracy\Debugger;

if(!defined('APP_DIR')){
	define('APP_DIR', __DIR__ . '/');
	define('WWW_DIR', __DIR__ . '/..');
	define('DATA_DIR', __DIR__ . '/../data');
	define('OLD_DATA_DIR', __DIR__ . '/../admin/app/data');
}

class Booting{

	/**
	 * @return Configurator
	 */
	public static function boot(
		BootConfig $config)
	{
		$configurator = new Configurator();

		if($config->debug === 'yes'){
			$configurator->setDebugMode(true);
		}else if(is_string($config->debug)){
			$configurator->setDebugMode(explode(',', $config->debug));
		}else if(is_array($config->debug)){
			$configurator->setDebugMode($config->debug);
		}

		$configurator->enableDebugger($config->log);

		$configurator->setTempDirectory($config->temp);

		if($config->dirs){
			$configurator->createRobotLoader()
				->setAutoRefresh($config->mode != 'prod')
				->addDirectory($config->dirs)
				->register();
		}

		$configurator->addConfig($config->config . '/config.neon');
		$configurator->addConfig($config->config . '/mode/config.' . $config->mode . '.neon');
		$configurator->addConfig($config->config . '/hosts/config.' . $config->host . '.neon');

		Debugger::$maxLength = 0;
		Debugger::$strictMode = true;

		return $configurator;
	}
}