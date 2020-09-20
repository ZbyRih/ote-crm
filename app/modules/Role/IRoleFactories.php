<?php

namespace App\Modules\Role\Factories;

interface IRoleGrid{

	/**
	 * @return \App\Modules\Role\Grids\RoleGrid
	 */
	function create();
}

interface IRoleGridDataSource{

	/**
	 * @return \App\Modules\Role\Grids\RoleGridDataSource
	 */
	function create();
}

interface IEditRole{

	/**
	 * @return \App\Modules\Role\Components\EditRole
	 */
	function create();
}
