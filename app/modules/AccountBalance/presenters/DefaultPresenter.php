<?php

namespace App\Modules\AccountBalance\Presenters;

use App\Components\YearControl;
use App\Components\YearItems;
use App\Modules\AccountBalance\Factories\IBalanceGridDataSource;
use App\Modules\AccountBalance\Factories\IBalanceGrid;
use App\Extensions\App\PersistentItem;
use App\Extensions\App\PersistentParameterSessionStorage;
use App\Models\Selections\SmlOmSelection;

class DefaultPresenter extends BasePresenter{

	/** @var IBalanceGrid @inject */
	public $comGrid;

	/** @var IBalanceGridDataSource @inject */
	public $comGridDataSource;

	/** @var SmlOmSelection @inject */
	public $selSmlOm;

	/** @var PersistentParameterSessionStorage @inject */
	public $store;

	/** @var PersistentItem */
	private $_year;

	/** @var YearItems */
	private $years;

	/**
	 * {@inheritdoc}
	 * @see \App\Presenters\BasePresenter::startup()
	 */
	protected function startup()
	{
		parent::startup();

		$this->years = new YearItems(function ()
		{
			return $this->selSmlOm->getYears();
		});
	}

	public function actionDefault()
	{
		$this->_year = $this->store->create('year', $this->getAction(true), $this->year);
		$this->_year->restore();

		if($this->_year->get() === null){
			$this->_year->set($this->years->getDefault());
		}
	}

	public function createComponentYear()
	{
		$yc = new YearControl();

		$yc->setItems($this->years->getValues());
		$yc->setCurrent($this->_year->get());

		$yc->onChange[] = function (
			$year)
		{
			$this->_year->set($year);
			$this->redirect('this');
		};

		return $yc;
	}

	public function createComponentGrid()
	{
		$src = $this->comGridDataSource->create();
		$src->setYear($this->_year->get());

		$com = $this->comGrid->create();
		$com->setDataSource($src);

		return $com;
	}
}