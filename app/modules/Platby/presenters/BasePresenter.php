<?php

namespace App\Modules\Platby\Presenters;

class BasePresenter extends \App\Presenters\BasePresenter{

	/** @var string @persistent */
	public $year;

	public function getResource()
	{
		return 'Platby';
	}
}