<?php

namespace App\Faktury\Grids;

class FakturyOverviewGrid extends \App\Components\ModelGridComponent{

	private $year;

	private $view;

	protected function config(
		$info)
	{
		$U = new \MUser();
		$us = \MArray::MapValToKeyFromMArray($U->FindAll(), $U->name, 'id', 'jmeno');

		$this->onConfig[] = function (
			$Grid){
			$Grid->filter->getItem(1)->setUserSetModel(
				function (
					$modelObj,
					$value){
					switch($value){
						case 1:
							$modelObj->having[] = '!color = 1';
							break;
						case 2:
							$modelObj->having[] = '!color = 2';
							break;
						case 3:
							$modelObj->having[] = '!color = 3';
							break;
					}
				});
		};

		return [
			'form' => 'faktury',
			'actions' => [
				\ListAction::DELETE,
				\ListAction::EDIT
			],
			'cols' => [
				'Faktura' => [
					'!IF(getFakUhrDne(Faktura.preplatek, Faktura.id) IS NOT NULL, 3, IF(DATE(NOW()) > DATE(splatnost), 2, IF(odeslano IS NOT NULL, 1 , 0))) color'
				]
			],
			'spcCols' => [
				'Faktura' => [
					'DATE_FORMAT(Faktura.od, \'%d.%m. %Y\')' => 'Od',
					'DATE_FORMAT(Faktura.do, \'%d.%m. %Y\')' => 'Do',
					'DATE_FORMAT(Faktura.vystaveno, \'%d.%m. %Y\')' => 'vyst.',
					'user_id' => 'Vystavil',
					'cis' => 'Číslo',
					'man' => 'Ruční',
					'storno' => 'Storno.',
					'IF(odeslano, 1, 0)' => 'Odesl.',
					'suma' => 'Suma',
					'dph' => 'DPH',
					'suma_a_dph' => 'Suma vč. DPH',
					'preplatek' => 'Fakturováno',
					'!isUhrFaktura(Faktura.preplatek, Faktura.id)' => 'Uhrazeno',
					'!IF(ext IS NOT NULL OR ext != \'\', 1, 0)' => 'P.S.',
					'!(SELECT tom.com FROM tx_odber_mist AS tom WHERE tom.odber_mist_id = Faktura.om_id)' => 'ČOM'
				]
			],
			'numTypes' => [
				'Faktura' => [
					'suma' => 3,
					'dph' => 3,
					'suma_a_dph' => 3,
					'preplatek' => 3
				]
			],
			'linesColor' => [
				'Faktura.color' => [
					1 => 'alert-info', // odeslana
					2 => 'alert-danger', // po splatnosti
					3 => 'alert-success' // uhrazena
				]
			],
			'valuesSubstitute' => [
				'Faktura' => [
					'storno' => self::$anoNe,
					'IF(Faktura.odeslano, 1, 0)' => self::$anoNe,
					'isUhrFaktura(Faktura.preplatek, Faktura.id)' => self::$anoNe,
					'IF(ext IS NOT NULL OR ext != \'\', 1, 0)' => self::$anoNe,
					'man' => self::$anoNe,
					'user_id' => $us
				]
			],
			'filter' => [
				[
					'type' => 'like',
					'fields' => [
						'Faktura.cis',
						'Faktura.suma',
						'Faktura.dph',
						'Faktura.suma_a_dph',
						'preplatek'
					],
					'name' => 'Čis., částka'
				],
				[
					'type' => 'list',
					'fields' => [
						'!color'
					],
					'name' => 'Stav',
					'list' => [
						0 => 'vše',
						1 => 'ok',
						2 => 'po splatnosti',
						3 => 'uhrazeno'
					]
				]

			],
// 			'static' => true,
// 			'sorting' => '4:desc',
			'pagination' => true,
			'itemsOnPage' => 30
		];
	}

	public function setUpModel(
		\ModelListClass $Grid)
	{
		$F = new \MFaktury();

		$conds = [
			'prepl' => 'preplatek < 0',
			'nedpl' => 'preplatek > 0',
			'neuhr' => '!isUhrFaktura(Faktura.preplatek, Faktura.id) IS NOT TRUE',
			'uhr' => '!isUhrFaktura(Faktura.preplatek, Faktura.id) IS TRUE',
			'neods' => '!odeslano IS NULL',
			'storno' => 'storno = 1',
			'man' => 'man = 1'
		];

		$F->conditions = [
			'!' . $this->year . ' = YEAR(vystaveno)',
			'!deleted IS NULL'
		];

		if(array_key_exists($this->view, $conds)){
			$F->conditions[] = $conds[$this->view];
		}

		$F->order['cis'] = 'DESC';

		return $F;
	}

	public function setYear(
		$year)
	{
		$this->year = $year;
		return $this;
	}

	public function setView(
		$view)
	{
		$this->view = $view;
		return $this;
	}
}