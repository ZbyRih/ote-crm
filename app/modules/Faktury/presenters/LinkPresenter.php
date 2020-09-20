<?php

namespace App\Modules\Faktury\Presenters;

// use App\Components\YearControl;
use App\Models\Commands\IFakturaParPlatbaCommand;
use App\Models\Selections\FakturaSelection;

// use App\Models\Strategies\IPlatbyParFakturyStrategy;
// use App\Modules\Faktury\IFakturyParPlatbyGrid;
// use App\Models\Strategies\PlatbyParZalohyException;
class LinkPresenter extends BasePresenter{

// 	/** @var IFakturyParPlatbyGrid @inject */
// 	public $comGrid;

	// 	/** @var IPlatbyParFakturyStrategy @inject */
// 	public $facPar;

	/** @var IFakturaParPlatbaCommand @inject */
	public $cmdPar;

	/** @var FakturaSelection @inject */
	public $selFaktury;

	/** @var string @persistent */
	public $year;

	protected function startup()
	{
		parent::startup();

		if(!$this->year){
			$years = $this->selFaktury->getYears();
			$this->year = end($years);
		}
	}

// 	public function createComponentYear()
// 	{
// 		$yc = new YearControl();

// 		$yc->setItems($this->selFaktury->getYears());
// 		$yc->setCurrent($this->year);

// 		$yc->onChange[] = function (
// 			$year){
// 			$this->year = $year;
// 			$this->redirect('this');
// 		};

// 		return $yc;
// 	}

// 	public function createComponentGrid()
// 	{
// 		$par = $this->facPar->create();
// 		$par->setYear($this->year);
// 		$par->load();
// 		try{
// 			$src = $par->getTable();
// 		}catch(PlatbyParZalohyException $e){
// 			$src = [];
// 			$this->flashWarning($e->getMessage());
// 		}

// 		$g = $this->comGrid->create();
// 		$g->setDataSource($src);

// 		$g->onLink[] = function (
// 			$ids){
// 			foreach($ids as $sid){
// 				$els = explode('_', $sid);
// 				$cmd = $this->cmdPar->create();
// 				$cmd->setPlatba($els[1]);
// 				$cmd->setFaktura($els[3]);
// 				$cmd->execute();
// 			}
// 			$this->flashSuccess('Spárováno ' . count($ids) . ' plateb');
// 		};
// 		return $g;
// 	}
}