<?php

class ZalohySpojeniPlatbSubModule extends SubModule{

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
		$this->views->title = 'Manuální párování plateb na zálohy';

		$qn = $this->qn = $this->views->add($this->createYearsNav($info));

		$List = ViewsFactory::createList($info);

		$List->configByArray(
			[
				'cols' => [
					'odb' => 'Odběratel',
					'vs' => 'Variabilní symbol',
					'vyse' => 'Suma záloh',
					'uhrazeno' => 'Uhrazeno celk.',
					'stav' => 'Stav pokrytí',
					'platby' => 'Plateby celk.',
					'sedi' => 'výše plateb jako záloh'
				],
				'valuesSubstitute' => [
					'sedi' => [
						0 => 'ne',
						1 => 'ano'
					]
				],
				'numTypes' => [
					'vyse' => 3,
					'uhrazeno' => 3,
					'platby' => 3
				],
				'linesColor' => [
					'color' => [
						1 => 'alert-success',
						2 => 'alert-warning'
					]
				],
				'static' => true
			]);

		$akce = new ListAction('sparovat');
		$akce->setTitle('Spárovat platby se zálohami')
			->setIcon('md md-link')
			->setRight(FormFieldRights::EDIT)
			->setMass('sparovat');

		$List->actions->addAction('sparovat', $akce);

		$akce = new ListAction('prejit');
		$akce->setTitle('Přejít do plateb')
			->setRight(FormFieldRights::VIEW)
			->setIcon('md-call-made');

		$List->actions->addAction('prejit', $akce);

		$List->setActionCallBacks([
			'sparovat' => [
				$this,
				'onSparovat'
			],
			'prejit' => [
				$this,
				'onPrejit'
			]
		]);

		if($info->scope->isAction('sparovat')){
			if($this->onSparovat($info) === 'ZOBRAZIT'){
				return true;
			}else{
				$info->scope->ResetViewByRedirect();
			}
		}

		if($ret = $List->handleActions()){
			if($ret === 'ZOBRAZIT'){
				return true;
			}else{
				$info->scope->ResetViewByRedirect();
			}
		}
		$platby = [];

		$vss = (new MPlatby())->getUnlinkVSList($qn->curr);

		$t = microtime(true);

		foreach($vss as $vs){
			$res = (new PlatbyParZalohy($qn->curr))->load($vs)
				->projit()
				->getResultList();
			$platby += $res;

			if((microtime(true) - $t) > 30){
				AdminApp::postMessage('Procházení trvalo déle naž 20s, výsledek omezen', 'warning');
				break;
			}
		}

		$List->setData($platby);

		$this->views->add($List);

		return true;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function onSparovat($info){
		if($ids = ListClass::getActionIds($info)){
			$info->scope->addExt(k_mIds);

			$data = [];
			$datas = [];
			$platby = [];
			$confirm = false;

			$Link = ViewsFactory::createLink($info->scope->getLinkExt(null, null, 'sparovat') . '&spojit=true', 'Spojit', 'md md-link');

			if(OBE_Http::isGetIs('spojit', 'true')){
				$confirm = true;
			}

			foreach($ids as $id){
				$platby = (new PlatbyParZalohy($this->qn->curr))->load($id)->projit();

				$data = $platby->getMidResult();
				$datas = array_merge($datas, $data);

				if($confirm){
					$platby->save();
				}
			}

			if(!$confirm){

				$List = ViewsFactory::createList($info);

				$List->configByArray(
					[
						'cols' => [
							'od' => 'Záloha od',
							'vs' => 'Záloha var. sym.',
							'vyse' => 'Záloha výše',
							'p_vs' => 'Platba var. sym.',
							'p_od' => 'Platba přípis',
							'platba' => 'Platba'
						],
						'numTypes' => [
							'vyse' => 3,
							'platba' => 3
						]
					]);

				$List->setData($datas);

				$this->views->add($List);

				$this->views->add($Link);

				return 'ZOBRAZIT';
			}
			return true;
		}
		return true;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function onPrejit($info){
		if($ids = ListClass::getActionIds($info)){
			foreach($ids as $id){
				AdminApp::Redirect($info->scope->parent->getStaticLink(self::DEFAULT_VIEW) . '&pgFilter=' . $id);
			}
			return true;
		}
		return false;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 */
	private function createYearsNav($info){
		$Platby = new MPlatby();
		if($items = $Platby->FindAll([
			'deprecated' => 0,
			'!isLinkedPlatba(Platba.platba, Platba.platba_id) IS NOT TRUE'
		], [
			'YEAR(when) AS y'
		])){
			$years = collection($items)->extract('Platba.y')->toList();
		}else{
			$years[] = OBE_DateTime::getYear();
		}

		$qn = ViewsFactory::createShortNav($info->scope, 'year', array_combine($years, $years), 2);
		$qn->cardTitle = false;
		return $qn->setCurrent(MArray::numeric_nearest($years, OBE_DateTime::getYear()));
	}
}