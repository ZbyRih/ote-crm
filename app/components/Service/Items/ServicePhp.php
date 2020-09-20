<?php

namespace App\Components\Service;

class ServicePhp extends ServicePanel{

	public function __construct()
	{
		$tzIni = ini_get('date.timezone');
		$tzScript = date_default_timezone_get();

		$items = [
			'version' => phpversion(),
			'timeout' => ini_get('max_execution_time'),
			'max mem.' => ini_get('memory_limit'),
			'max post size' => ini_get('post_max_size'),
			'xdebug' => extension_loaded('xdebug') ? 'ano' : 'ne',
			'cache' => function_exists('xcache_clear_cache') ? 'xcache' : (function_exists('opcache_reset') ? 'opcache' : 'ne'),
			'timezone ini' => $tzIni,
			'timezona script' => $tzScript
		];

		parent::__construct('PHP', $items);
	}
}