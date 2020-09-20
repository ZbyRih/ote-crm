<?php

class FakturySpojeniPlatebSubModule extends SubModule{

	var $handlers = [
		self::DEFAULT_VIEW => 'editSpojeni'
	];

	var $qn = null;

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function editSpojeni($info){

		$qn = $this->createYearsNav($info);
		$this->views->add($qn);

		$this->qn = $qn;

		$List = ViewsFactory::createList($info);

		$List->configByArray(
			[
				'cols' => [
					'odb' => 'Odběratel',
					'om' => 'O.M.',
					'vs' => 'Variabilní symbol',
					'vyse' => 'Fakturováno',
					'splatnost' => 'Splatnost',
					'when' => 'Příchozí',
					'platba' => 'Platba',
					'cu' => 'Z účtu',
					'klient' => 'Klient',
					'preplatek' => 'Přeplatek / nedoplatek'
				],
				'numTypes' => [
					'vyse' => 3,
					'platba' => 3
				],
				'valuesSubstitute' => [
					'preplatek' => [
						0 => '<>',
						1 => '='
					]
				],
				'static' => true,
				'sorting' => false
			]);

		$akce = new ListAction('sparovat');
		$akce->setTitle('Spárovat platby na faktury')
			->setIcon('md md-link')
			->setRight(FormFieldRights::EDIT)
			->setMass('sparovat');

		$List->actions->addAction('sparovat', $akce);

		$List->setActionCallBacks([
			'sparovat' => [
				$this,
				'onSparovat'
			]
		]);

		if($ret = $List->handleActions()){
			$info->scope->parent->ResetViewByRedirect();
		}

		try{
			$List->setData((new PlatbyParFaktury($qn->curr))->load()
				->spojit()
				->getResult());

			$this->views->add($List);

			$this->views->setTitle('Manuální párování plateb na faktury');
		}catch(PlatbyParZalohyException $e){
			AdminApp::postMessage($e->getMessage(), 'warning');
		}

		return true;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function onSparovat($info){
		if($ids = ListClass::getActionIds($info)){
			$F = new MFaktury();

			foreach($ids as $id){
				$els = explode('_', $id);
				$F->sparovat($els[1], $els[3]);
			}

			AdminApp::postMessage('Spojeno ' . count($ids) . ' plateb s fakturamy.', 'success');

			return true;
		}
		return true;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 */
	private function createYearsNav($info){
		$Platby = new MPlatby();
		if($items = $Platby->FindAll([
			'link' => 0,
			'deprecated' => 0
		], [
			'YEAR(when) AS y'
		])){
			foreach($items as $i){
				$years[] = $i[$Platby->name]['y'];
			}
		}else{
			$years[] = OBE_DateTime::getYear();
		}

		$qn = ViewsFactory::createShortNav($info->scope, 'year', array_combine($years, $years), 2);
		$qn->cardTitle = false;
		$qn->setCurrent(MArray::numeric_nearest($years, OBE_DateTime::getYear()));

		return $qn;
	}
}