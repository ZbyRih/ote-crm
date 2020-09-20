<?php

namespace App\Modules\OteGP6\Factories;

use App\Modules\OteGP6\Grids\OteGP6Grid;
use App\Modules\OteGP6\Grids\OteGP6GridDataSource;

interface IOteGP6Grid{

	/**
	 *
	 * @return OteGP6Grid
	 */
	public function create();
}

interface IOteGP6GridDataSource{

	/**
	 *
	 * @return OteGP6GridDataSource
	 */
	public function create();
}