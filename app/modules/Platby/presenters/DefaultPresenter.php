<?php

namespace App\Modules\Platby\Presenters;

use App\Modules\Platby\Components\PlatbyGridDataSource;
use App\Modules\Platby\Factories\IPlatbyGrid;
use App\Modules\Platby\Factories\IPlatbyGridDataSource;
use App\Components\YearControl;
use App\Extensions\Components\NavTabs;
use App\Models\Selections\PlatbaSelection;
use App\Models\Services\InfoService;
use App\Models\Services\PlatbyZaraditStrategyFactory;
use App\Models\Enums\InfoEnums;
use App\Models\Events\DBCommitEvent;

class DefaultPresenter extends BasePresenter{

	/** @var PlatbaSelection @inject */
	public $selPlatby;

	/** @var InfoService @inject */
	public $serInfo;

	/** @var PlatbyZaraditStrategyFactory @inject */
	public $facPlatbyZaradit;

	/** @var string @persistent */
	public $view;

	/** @var IPlatbyGrid @inject */
	public $comGrid;

	/** @var IPlatbyGridDataSource @inject */
	public $comGridDataSource;

	/**
	 * {@inheritdoc}
	 * @see \App\Presenters\BasePresenter::startup()
	 */
	protected function startup()
	{
		parent::startup();

		if(!$this->year){
			$years = $this->selPlatby->getYears();
			$this->year = end($years);
		}

		$this->view = !$this->view ? 'all' : $this->view;
	}

	public function createComponentYear()
	{
		$yc = new YearControl();

		$yc->setItems($this->selPlatby->getYears());
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
				PlatbyGridDataSource::VIEW_ALL => 'Vše',
				PlatbyGridDataSource::VIEW_ZARAZENE => 'Zařazené',
				PlatbyGridDataSource::VIEW_NEZARAZENE => 'Nezařazené',
				PlatbyGridDataSource::VIEW_STAZENE => 'Stažené',
				PlatbyGridDataSource::VIEW_RUCNI => 'Ručně zadané',
				PlatbyGridDataSource::VIEW_S_DOKLADY => 'Dokladové',
				PlatbyGridDataSource::VIEW_OSTATNI => 'Ostatní'
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
		$src->setUserId($this->user->isAllowed('Klients', 'view_all') ? null : $this->user->id);

		$g = $this->comGrid->create();
		$g->setDataSource($src);

		$g->onChangeType[] = function (
			$ids,
			$type)
		{
			$plas = $this->orm->platby->findById($ids);
			foreach($plas as $p){
				$p->type = $type;
				$this->orm->persist($p);
			}
			$this->dispatcher->dispatch(DBCommitEvent::NAME);
			$this->flashInfo(count($ids) . ' platbám změněn typ.');
			$this->redirect('default');
		};

		$g->onVyradit[] = function (
			$ids)
		{
			$plas = $this->orm->platby->findById($ids);
			foreach($plas as $p){
				if($p->hasZarazeni()){
					$this->orm->remove($p->zarazeni);
				}
			}
			$this->dispatcher->dispatch(DBCommitEvent::NAME);
			$this->flashInfo(count($ids) . ' platb vyřazeno.');
			$this->redirect('default');
		};

		$g->onZaradit[] = function (
			$ids)
		{
			$plas = $this->orm->platby->findById($ids);
			$info = $this->serInfo->createObj(InfoEnums::TYPE_BANK);

			$str = $this->facPlatbyZaradit->create();
			$str->setInfo($info);
			$str->zaradit($plas);

			foreach($plas as $p){
				$this->orm->persist($p);
			}

			$e = $this->serInfo->persist($info);

			$this->dispatcher->dispatch(DBCommitEvent::NAME);

			if($e){
				$this->redirect('Report:', $e->id, 'Zařazení');
			}else{
				$this->redirect('Default:');
			}
		};

		return $g;
	}
}