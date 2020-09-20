<?php

namespace App\Modules\Helper\Factories;

interface IHelperGrid{

	/**
	 * @return \App\Modules\Helper\Grids\HelperGrid
	 */
	function create();
}

interface IHelperGridDataSource{

	/**
	 * @return \App\Modules\Helper\Grids\HelperGridDataSource
	 */
	function create();
}

interface IHelpEditForm{

	/**
	 * @return \App\Modules\Helper\Components\EditHelp
	 */
	function create();
}
