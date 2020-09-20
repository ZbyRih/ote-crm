<?php

namespace App\Modules\Info\Factories;

interface IInfoGrid{

	/**
	 * @return \App\Modules\Info\Grids\InfoGrid
	 */
	function create();
}

interface IInfoGridDataSource{

	/**
	 * @return \App\Modules\Info\Grids\InfoGridDataSource
	 */
	function create();
}