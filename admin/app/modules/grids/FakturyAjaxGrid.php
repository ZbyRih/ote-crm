<?php

namespace App\Faktury\Grids;

class FakturyAjaxGrid extends \App\Components\ModelGridComponent{

	protected function config($info){
		$U = new \MUser();
		$us = \MArray::MapValToKeyFromMArray($U->FindAll(), $U->name, 'id', 'jmeno');

		return [
			'form' => 'faktury',
			'mode' => 'select',
			'actions' => [
				\ListAction::SELECT
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
					'suma_a_dph' => 'Suma vč. DPH',
					'preplatek' => 'Přeplatek'
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
				]
			],
// 			'static' => true,
// 			'sorting' => '4:desc',
			'pagination' => true,
			'itemsOnPage' => 30
		];
	}

	public function setUpModel(\ModelListClass $Grid){
		$F = new \MFaktury();
		$F->conditions = [
			'!' . \OBE_Http::getGet('year') . ' = YEAR(vystaveno)',
			'!deleted IS NULL'
		];

		if(\OBE_Http::isGetIsIs('uhr', 0)){
			$F->conditions[] = '!getFakUhrDne(Faktura.preplatek, Faktura.id) IS NULL';
		}elseif(\OBE_Http::isGetIsIs('uhr', 1)){
			$F->conditions[] = '!getFakUhrDne(Faktura.preplatek, Faktura.id) IS NOT NULL';
		}

		if(\OBE_Http::issetGet('kli')){
			$F->conditions['klient_id'] = \OBE_Http::getGet('kli');
		}

		return $F;
	}
}