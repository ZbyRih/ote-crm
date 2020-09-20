<?php

namespace App\Modules\Zalohy;

interface IZalohyGrid{

	/**
	 * @return \App\Modules\Zalohy\Grids\ZalohyGrid
	 */
	function create();
}

interface IZalohyGridDataSource{

	/**
	 * @return \App\Modules\Zalohy\Grids\ZalohyGridDataSource
	 */
	function create();
}