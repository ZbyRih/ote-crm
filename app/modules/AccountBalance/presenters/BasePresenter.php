<?php

namespace App\Modules\AccountBalance\Presenters;

class BasePresenter extends \App\Presenters\BasePresenter{

	/** @var string @persistent */
	public $year;

	public function getResource()
	{
		return 'AccountBalance';
	}
}