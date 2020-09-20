<?php

namespace App\Modules\Activity\Factories;

use App\Modules\Activity\Grids\ActivityGrid;
use App\Modules\OteZpravy\Grids\ActivityGridDataSource;

interface IActivityGrid{

	/**
	 *
	 * @return ActivityGrid
	 */
	public function create();
}

interface IActivityGridDataSource{

	/**
	 *
	 * @return ActivityGridDataSource
	 */
	public function create();
}