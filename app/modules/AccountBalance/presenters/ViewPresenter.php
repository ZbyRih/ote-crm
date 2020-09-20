<?php

namespace App\Modules\AccountBalance\Presenters;

use App\Models\Orm\Klients\KlientEntity;
use App\Modules\AccountBalance\Factories\IBalanceCompact;
use App\Modules\AccountBalance\Factories\IBalanceView;
use App\Extensions\Components\NavTabs;

class ViewPresenter extends BasePresenter{

	/** @var IBalanceView @inject */
	public $comList;

	/** @var IBalanceCompact @inject */
	public $comCompact;

	/** @var int @persistent */
	public $id;

	/** @var string @persistent */
	public $view;

	/** @var KlientEntity */
	private $klient;

	/**
	 * {@inheritdoc}
	 * @see \App\Presenters\BasePresenter::startup()
	 */
	protected function startup()
	{
		parent::startup();

		$this->view = !$this->view ? 'compact' : $this->view;
	}

	public function actionDefault(
		$id)
	{
		$this->klient = $this->orm->klients->getById($id);
	}

	public function renderDefault()
	{
		$this->template->setParameters([
			'view' => $this->view
		]);
	}

	public function createComponentNav()
	{
		$n = new NavTabs($this->dispatcher);
		$n->setItems([
			'compact' => 'default',
			'list' => 'seznam'
		]);
		$n->setTab($this->view);

		$n->onChange[] = function (
			$tc,
			$tab)
		{
			$this->view = $tab;
			$this->redirect('this');
		};

		return $n;
	}

	public function createComponentList()
	{
		$com = $this->comList->create();
		$com->setKlient($this->klient);
		$com->setYear($this->year);
		return $com;
	}

	public function createComponentCompact()
	{
		$com = $this->comCompact->create();
		$com->setKlient($this->klient);
		$com->setYear($this->year);
		return $com;
	}
}