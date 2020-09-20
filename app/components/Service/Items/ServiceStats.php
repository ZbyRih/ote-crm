<?php

namespace App\Components\Service;

class ServiceStats extends ServicePanel{

	public function __construct(){
		parent::__construct('Statistika', [
			'počet přihlášených(posledních 10 minut)' => $this->getGetter('activeUserCounts')
		]);
	}
}