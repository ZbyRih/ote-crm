<?php

namespace App\Components\Service;

use Nette\Database\Connection;
use Tracy\Debugger;

class ServiceEnv extends ServicePanel{

	public function __construct(Connection $con){
		$items = [
			'Mode' => getenv('APP_MODE'),
			'Host' => getenv('APP_HOST'),
			'Debug' => getenv('APP_DEBUG') ? getenv('APP_DEBUG') : 'auto',
			'Produkce' => Debugger::$productionMode ? 'yes' : 'no',
			'DSN' => $con->getDsn(),
			'Remote IP' => $_SERVER['REMOTE_ADDR'],
			'UName' => php_uname('a')
		];
		parent::__construct('Prostředí', $items);
	}
}