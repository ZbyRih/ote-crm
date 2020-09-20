<?php

namespace App\Modules\Faktury\Presenters;

use App\Components\YearControl;
use App\Extensions\Components\NavTabs;
use App\Models\Selections\FakturaSelection;
use App\Models\Commands\IFakturaDeleteCommand;
use App\Models\Commands\IFakturaDownloadCommand;
use App\Models\Commands\IFakturaOdeslanoCommand;
use App\Models\Commands\IFakturaRecreateCommand;
use App\Models\Commands\IFakturaStornoCommand;
use App\Models\Commands\ILegacyInitCommand;
use App\Modules\Faktury\IFakturyGrid;
use App\Modules\Faktury\IFakturyGridDataSource;
use App\Modules\Faktury\Grids\FakturyGridDataSource;
use Nette\InvalidStateException;

class DefaultPresenter extends BasePresenter{

	/** @var FakturaSelection @inject */
	public $selFaktury;

	/** @var IFakturyGrid @inject */
	public $comGrid;

	/** @var IFakturyGridDataSource @inject */
	public $comGridDataSource;

	/** @var IFakturaStornoCommand @inject */
	public $cmdStorno;

	/** @var IFakturaDeleteCommand @inject */
	public $cmdDelete;

	/** @var IFakturaOdeslanoCommand @inject */
	public $cmdOdeslano;

	/** @var IFakturaRecreateCommand @inject */
	public $cmdRecreate;

	/** @var IFakturaDownloadCommand @inject */
	public $cmdDownload;

	/** @var ILegacyInitCommand @inject */
	public $cmdLegacyInit;

	/** @var string @persistent */
	public $year;

	/** @var string @persistent */
	public $view;

	/**
	 * {@inheritdoc}
	 * @see \App\Presenters\BasePresenter::startup()
	 */
	protected function startup()
	{
		parent::startup();

		if(!$this->year){
			$years = $this->selFaktury->getYears();
			$this->year = end($years);
		}

		$this->view = !$this->view ? 'all' : $this->view;
	}

	public function renderDefault()
	{
		$this->template->setParameters([
			'year' => $this->year
		]);
	}

	public function createComponentYear()
	{
		$yc = new YearControl();

		$yc->setItems($this->selFaktury->getYears());
		$yc->setCurrent($this->year);

		$yc->onChange[] = function (
			$year)
		{
			$this->year = $year;
			$this->redirect('this');
		};

		return $yc;
	}

	public function createComponentView()
	{
		$n = new NavTabs($this->dispatcher);
		$n->setItems(
			[
				FakturyGridDataSource::VIEW_ALL => 'Vše',
				FakturyGridDataSource::VIEW_PREPLATKY => 'Přeplatky',
				FakturyGridDataSource::VIEW_NEDOPLATKY => 'Nedoplatky',
				FakturyGridDataSource::VIEW_NEUHRAZENE => 'Neuhrazené',
				FakturyGridDataSource::VIEW_NEODESLANE => 'Neodeslané',
				FakturyGridDataSource::VIEW_STORNO => 'Storna',
				FakturyGridDataSource::VIEW_RUCNE_VYTVORENE => 'Zadané ručně'
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

	public function createComponentGrid()
	{
		$src = $this->comGridDataSource->create();
		$src->setYear($this->year);
		$src->setView($this->view);
		$src->setUserId(!$this->user->isAllowed('Klients', 'view_all') ? $this->user->id : null);

		$g = $this->comGrid->create();
		$g->setDataSource($src);

		$g->onPrikaz[] = function (
			array $ids)
		{
			$this->redirect(':Faktury:Prikaz:', [
				'ids' => $ids
			]);
		};

		return $g;
	}

	public function actionDelete(
		$id)
	{
		if(!$id){
			$this->redirect('default');
		}

		$cmd = $this->cmdDelete->create();
		$cmd->setId($id);

		try{
			$cmd->execute();
			$this->flashSuccess('Faktura smazána, OTE zprávy uvolněny.');
		}catch(\ModelDeleteException $e){
			$this->flashWarning($e->getMessage());
		}

		$this->redirect('default');
	}

	public function actionStorno(
		$id)
	{
		if(!$id){
			$this->redirect('default');
		}

		$cmd = $this->cmdStorno->create();
		$cmd->setId($id);
		$cmd->execute();

		$this->flashSuccess('Faktura stornována, OTE zprávy uvolněny.');
		$this->redirect('default');
	}

	public function actionSend(
		$id)
	{
		if(!$id){
			$this->redirect('default');
		}

		$cmd = $this->cmdOdeslano->create();
		$cmd->setId($id);

		try{
			$cmd->execute();
			$this->flashSuccess('Faktura označena jako odeslána.');
		}catch(InvalidStateException $e){
			$this->flashWarning($e->getMessage());
		}

		$this->redirect('default');
	}

	public function actionRecreate(
		$id)
	{
		if(!$id){
			$this->redirect('default');
		}

		$cmd = $this->cmdRecreate->create();
		$cmd->setId($id);
		$cmd->setUserId($this->user->id);

		try{
			$cmd->execute();
			$this->flashSuccess('Faktura přegenerována.');
		}catch(InvalidStateException $e){
			$this->flashWarning($e->getMessage());
		}catch(\FakturaException $e){
			$this->flashWarning($e->getMessage());
		}catch(\DownloadResponseException $e){
			$this->terminate();
		}

		$this->redirect('default');
	}

	public function actionDownload(
		$id)
	{
		if(!$id){
			$this->redirect('default');
		}

		$fa = $this->orm->faktury->getById($id);
		$cmd = $this->cmdDownload->create();
		$cmd->setFa($fa);
		$cmd->execute();
	}
}
