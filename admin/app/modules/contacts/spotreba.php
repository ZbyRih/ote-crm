<?php

use Cake\Collection\Collection;


class SpotrebaSubModule extends SubModule{

	var $handlers = [
		self::DEFAULT_VIEW => 'viewSpotreba'
	];

	/**
	 *
	 * @param ModuleInfoClass $info
	 */
	public function viewSpotreba($info){

		$Y = date('Y');

		$SmlOM = new MSmlOM();
		$SmlOM->conditions[] = 'SmlOM.klient_id = ' . $info->scope->parent->recordId;
		$SmlOM->conditions[] = '! YEAR(do) > ' . ($Y - 3);

		$smls = $SmlOM->FindAll();

		$oms = (new Collection($smls))->indexBy('OdberMist.odber_mist_id')->map(function ($v){
			return MOdberMist::identity($v);
		})->toArray();

		$Model = new MSpotreba();
		$Model->conditions[] = '! YEAR(od) BETWEEN ' . ($Y - 3) . ' AND ' . $Y . '';
		$Model->conditions['odber_mist_id'] = array_keys($oms);
		$Model->order[] = 'odber_mist_id';
		$Model->order[] = 'YEAR(od)';

		$L = ViewsFactory::createModelList($info);
		$L->configByArray(
			[
				'model' => $Model,
				'actions' => [
					ListAction::DELETE
				],
				'spcCols' => [
					'Spotreba' => [
						'odber_mist_id' => 'COM',
						'CONCAT(DATE_FORMAT(od, \'%d.%m. %Y\'), \' - \', DATE_FORMAT(do, \'%d.%m. %Y\'))' => 'Od -Do',
						'mwh' => 'MWh'
					]
				],
				'numTypes' => [
					'Spotreba' => [
						'mwh' => 2
					]
				],
				'valuesSubstitute' => [
					'Spotreba' => [
						'odber_mist_id' => $oms
					]
				]
			]);

		$L->setActionCallBacks(
			function ($info, $list){
				foreach(ListClass::getActionIds($info) as $id){
					$s = new MSpotreba();
					$s->Delete($id);
				}
			}, ListAction::DELETE);

		if($L->handleActions()){
			$info->scope->resetViewByRedirect();
		}

		$this->views->add($L);

		$this->createForm($info, $oms);
	}

	/**
	 *
	 * @param ModuleViewClass $info
	 * @param array $oms
	 */
	public function createForm($info, $oms){

		$F = ViewsFactory::createForm($info->scope);

		$a = $F->createField('om', FormUITypes::DROP_DOWN, null, 'OM');
		$a->setList($oms)->addToForm($F);

		$a = $F->createField('od', FormUITypes::DATE, null, 'Od');
		$a->addToForm($F);
		$a = $F->createField('do', FormUITypes::DATE, null, 'Do');
		$a->addToForm($F);

		$a = $F->createField('mwh', FormUITypes::TEXT, null, 'MWh');
		$a->setType(FormFieldClass::FLOAT)->addToForm($F)->addAttribute('data-precision', 4);

		if($data = $F->handleFormSubmit()){
			$this->save($data);
			$info->scope->resetViewByRedirect();
		}

		$pack = ViewsFactory::createPack();
		$pack->add($F, 6);

		$this->views->add($pack);
	}

	public function save($data){
		$false = false;
		if(empty($data['od'])){
			$false = true;
			AdminApp::postMessage('Od musí být vyplněno', 'warning');
		}

		if(empty($data['do'])){
			$false = true;
			AdminApp::postMessage('Do musí být vyplněno', 'warning');
		}

		if(empty($data['mwh'])){
			$false = true;
			AdminApp::postMessage('Množství MWh musí být vyplněno', 'warning');
		}

		if($false){
			return;
		}

		$data = [
			'Spotreba' => [
				'odber_mist_id' => $data['om'],
				'od' => OBE_DateTime::convertToDB($data['od']),
				'do' => OBE_DateTime::convertToDB($data['do']),
				'mwh' => $data['mwh']
			]
		];

		(new MSpotreba())->Save($data);
	}
}