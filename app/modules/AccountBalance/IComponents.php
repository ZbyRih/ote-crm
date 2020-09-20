<?php

namespace App\Modules\AccountBalance\Factories;

use App\Modules\AccountBalance\Components\BalanceGrid;
use App\Modules\AccountBalance\Components\BalanceGridDataSource;
use App\Modules\AccountBalance\Components\BalanceView;
use App\Modules\AccountBalance\Components\BalanceCompact;

interface IBalanceGrid{

	/**
	 * @return BalanceGrid
	 */
	public function create();
}

interface IBalanceGridDataSource{

	/**
	 * @return BalanceGridDataSource
	 */
	public function create();
}

interface IBalanceView{

	/**
	 * @return BalanceView
	 */
	public function create();
}

interface IBalanceCompact{

	/**
	 * @return BalanceCompact
	 */
	public function create();
}