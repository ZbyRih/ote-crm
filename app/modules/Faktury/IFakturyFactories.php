<?php

namespace App\Modules\Faktury;

interface IFakturyGrid{

	/**
	 * @return \App\Modules\Faktury\Grids\FakturyGrid
	 */
	function create();
}

interface IFakturyGridDataSource{

	/**
	 * @return \App\Modules\Faktury\Grids\FakturyGridDataSource
	 */
	function create();
}

interface IFakturyEditGeneratedForm{

	/**
	 * @return \App\Modules\Faktury\Components\EditGenerated
	 */
	function create();
}

interface IFakturyEditUserForm{

	/**
	 * @return \App\Modules\Faktury\Components\EditUser
	 */
	function create();
}

interface IFakturyOteGrid{

	/**
	 * @return \App\Modules\Faktury\Grids\FakturaOTEGrid
	 */
	function create();
}

interface IFakturyOteGridDataSource{

	/**
	 * @return \App\Modules\Faktury\Grids\FakturaOteGridDataSource
	 */
	function create();
}

interface IFakturyPlatbyGrid{

	/**
	 * @return \App\Modules\Faktury\Grids\FakturaPlatbyGrid
	 */
	function create();
}

interface IFakturyPlatbyGridDataSource{

	/**
	 * @return \App\Modules\Faktury\Grids\FakturaPlatbyGridDataSource
	 */
	function create();
}

interface IFakturyParPlatbyGrid{

	/**
	 * @return \App\Modules\Faktury\Grids\FakturyParPlatbyGrid
	 */
	function create();
}