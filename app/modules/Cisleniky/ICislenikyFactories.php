<?php

namespace App\Modules\Ciselniky\Factories;

use App\Modules\Ciselniky\Grids\CiselnikGrid;
use App\Modules\Ciselniky\Grids\CiselnikGridDataSource;
use App\Modules\Ciselniky\Grids\CiselnikySkupinyGrid;
use App\Modules\Ciselniky\Grids\CiselnikySkupinyGridDataSource;

interface ICiselnikGrid{

	/**
	 * @return CiselnikGrid
	 */
	function create();
}

interface ICiselnikGridDataSource{

	/**
	 * @return CiselnikGridDataSource
	 */
	function create();
}

interface ICiselnikySkupinyGrid{

	/**
	 * @return CiselnikySkupinyGrid
	 */
	function create();
}

interface ICiselnikySkupinyGridDataSource{

	/**
	 * @return CiselnikySkupinyGridDataSource
	 */
	function create();
}

interface ICiselnikyAddItem{

	/**
	 * @return \App\Modules\Ciselniky\Components\AddCiselnikItem
	 */
	function create();
}

interface ICiselnikySkupinyAddItem{

	/**
	 * @return \App\Modules\Ciselniky\Components\AddSkupinaItem
	 */
	function create();
}