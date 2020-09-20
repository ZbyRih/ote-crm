<?php

namespace App\Modules\User\Factories;

interface IUserGrid{

	/**
	 * @return \App\Modules\User\Grids\UsersGrid
	 */
	function create();
}

interface IUserGridDataSource{

	/**
	 * @return \App\Modules\User\Grids\UsersGridDataSource
	 */
	function create();
}

interface IEditUser{

	/**
	 * @return \App\Modules\User\Components\EditUser
	 */
	function create();
}

interface IEditPrava{

	/**
	 * @return \App\Modules\User\Components\EditPrava
	 */
	function create();
}