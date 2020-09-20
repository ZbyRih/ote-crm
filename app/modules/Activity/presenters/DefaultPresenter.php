<?php

namespace App\Modules\Activity\Presenters;

use App\Modules\Activity\Factories\IActivityGrid;
use App\Modules\Activity\Factories\IActivityGridDataSource;

class DefaultPresenter extends BasePresenter{

	/** @var IActivityGrid @inject */
	public $comGrid;

	/** @var IActivityGridDataSource @inject */
	public $facGridDataSource;

	public function createComponentGrid()
	{
		$src = $this->facGridDataSource->create();
		$com = $this->comGrid->create();
		$com->setDataSource($src);

		return $com;
	}
}