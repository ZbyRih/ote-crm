<?php

namespace App\Components\Service;

use Nette\DI\Container;

class ServiceParams extends ServicePanel{

	public function __construct(Container $cnt){
		$ps = $cnt->getParameters();

		parent::__construct('Params', [
			'Temp' => $ps['tempDir']
		]);
	}
}