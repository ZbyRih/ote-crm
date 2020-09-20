<?php

namespace App\Extensions;

use App\Extensions\Components\Tisk\TiskComponent;

interface ITiskComponent{

	/**
	 *
	 * @return TiskComponent
	 */
	public function create();
}