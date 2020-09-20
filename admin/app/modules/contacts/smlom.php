<?php

class SmlOMContactSubModule extends SubModule{

	const infinite = '31.12. 9999';

	var $handlers = [
		self::DEFAULT_VIEW => 'editOdbMist',
		self::CREATE => 'editOdbMist',
		ListAction::EDIT => 'editOdbMist'
	];

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function editOdbMist(
		$info)
	{
		if(!$info->scope->parent->isEmptyRecId()){
			$SmlOM = new MSmlOM();
			$SmlOM->removeAssociatedModelsByType('FakSkup');

			$form = 'sml_odbm';
			if($info->scope->action == 'edit' && !$info->scope->isEmptyRecId()){
				$form = 'sml_odbm_detail';
			}

			$Form = ViewsFactory::createModelForm($SmlOM, $info, $form);

			$Form->getField('SmlOM', 'typ_sml')->setList(MSmlOM::$TYP_SML);
// 				->setParalel($Form->getField('SmlOM', 'do'), array('31.12. 9999', '31.12. ' . OBE_DateTime::getYear(), '31.12. 9999'));
			$Form->getField('SmlOM', 'category')->setList(MSmlOM::$CATEGORY);
			$Form->getField('SmlOM', 'vztah')->setList(MSmlOM::$VZTAH); /* tady dosat reakci ro datumy */
			$Form->getField('SmlOM', 'interval')->setList(MZalohy::$INTERVAL);
			$Form->getField('SmlOM', 'odber_mist_id')
				->setSelect('Vybrat', MODULES::ODBER_MIST)
				->setFieldsToUrl([
				'od' => 'SmlOM.od',
				'do' => 'SmlOM.do'
			]);
			$Form->getField('SmlOMFlags', 'typ_mer')->SetList(MSmlOMFlags::$TYP_MER);
			$Form->getField('SmlOMFlags', 'typ_net')->SetList(MSmlOMFlags::$TYP_NET);
			$duv = $Form->getField('SmlOMFlags', 'duv_zad')->setList(MSmlOMFlags::$DUV_ZAD);

			$FakSkup = new MFakSkup();
			$FakSkup->removeAssociateModels();

			$conditions = [];
			if(AdminUserClass::isOnlyOwn()){
				$conditions['owner_id'] = AdminUserClass::$userId;
			}

			$skups = $FakSkup->FindAll([
				'klient_id' => $info->scope->parent->recordId
			] + $conditions, [
				'fak_skup_id',
				'CONCAT_WS(\' - \', FakSkup.cis, FakSkup.nazev) AS nazev'
			]);
			$skups = [
				'NULL' => 'mimo fakturační skupinu'
			] + MArray::MapValToKeyFromMArray($skups, 'FakSkup', 'fak_skup_id', 'nazev');
			$Form->getField('SmlOM', 'fak_skup_id')->SetList($skups);

			$Form->setAppCallBack(AppFormClass2::ON_BEFORE_FILL, [
				$this,
				'onBeforeFillOdbMist'
			]);
			$Form->setAppCallBack(AppFormClass2::ON_BEFORE_SAVE, [
				$this,
				'onBeforeSaveOdbMist'
			]);

			if($info->scope->action == ListAction::EDIT && $info->scope->isSetRecId()){

				$this->views->add(ViewsFactory::createLink($info->scope->parent->getLink(), 'Zpět na seznam podle OM', 'md md-backspace')); // md-arrow-back

				$SmlOM = new MSmlOM();
				$om = $SmlOM->FindOneById($info->scope->recordId);
				$this->views->add(ViewsFactory::newCardPane($om['OdberMist']['eic'] . ' - ' . $om['OdberMist']['com'] . ' - ' . MAddress::addr($om)));

				$SmlOM->group[] = 'OdberMist.eic';
				$List = $this->createOdbMistList($info, $SmlOM);

				$qnav = ViewsFactory::createModelShortNav($List, $info->scope, [
					'OdberMist' => 'eic'
				]);
				$this->views->add($qnav);

				$ret = $Form->processForm(false, true); // nejde vyskocit z editace SmlOM pres zrusit

				if($ret === false){
					$info->scope->resetViewByRedirect();
				}

				if($Form->isSaved()){
					$info->scope->resetViewByRedirect();
				}
			}else{
				$rec_id = $info->scope->recordId;
				$info->scope->recordId = NULL;

				$Form->buttons->addSubmit(FormButton::CREATE, 'Přidat');

				$Form->processForm(true);

				if($Form->isSaved()){
					$info->scope->resetViewByRedirect();
				}

				$SmlOM->conditions[] = 'SmlOM.klient_id = ' . $info->scope->parent->recordId;

				$info->scope->recordId = $rec_id;

				$qn = $this->createYearNav($SmlOM, $info);

				$SmlOM->conditions[] = '!' . $qn->curr . ' BETWEEN (YEAR(od) - 1) AND (YEAR(do) + 1)';
				$this->views->add($qn);

				$List = $this->createOdbMistList($info, $SmlOM);

				if($List->handleActions()){
					$info->scope->resetViewByRedirect();
				}
				$this->views->add($List);
				$this->views->add(ViewsFactory::newCardPane('Přidat odběrné místo k odběrateli'));
			}

			$this->views->add($Form);
		}
		return true;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param ModelClass $SmlOM
	 * @return ModelListClass
	 */
	function createOdbMistList(
		$info,
		$SmlOM)
	{
		$SmlOM->order = [
			'OdberMist.com',
			'OdberMist.eic',
			'SmlOM.od'
		];
		$List = ViewsFactory::createModelList($info);
		$List->configByArray(
			[
				'form' => 'sml_odbm',
				'actions' => [
					ListAction::EDIT,
					ListAction::DELETE
				],
				'model' => $SmlOM,
				'spcCols' => [
					'OdberMist' => [
						'eic' => 'EIC',
						'com' => 'Č. o.m.'
					],
					'SmlOM' => [
						'DATE_FORMAT(od, \'%d.%m. %Y\')' => 'Od',
						'DATE_FORMAT(do, \'%d.%m. %Y\')' => 'Do',
						'category' => 'Categorie'
					],
					'Address' => [
						'CONCAT_WS(\', \', Address.city, Address.street, Address.cp, Address.co) AS adresa' => 'Adresa'
					],
					'FakSkup' => [
						'CONCAT_WS(\' - \', FakSkup.cis, FakSkup.nazev) AS cis' => 'Číslo fak. skup'
					]
				],
				'valuesSubstitute' => [
					'SmlOM' => [
						'category' => MSmlOM::$CATEGORY
					]
				],
				'numbered' => true,
				'static' => true
			]);
		$List->setActionCallBacks([
			$this,
			'onListDeleteSmlOM'
		], ListAction::DELETE);
		return $List;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function onListDeleteSmlOM(
		$info)
	{
		if($ids = ListClass::getActionIds($info)){
			$SmlOM = new MSmlOM();
			$SmlOM->removeAssociateModels();

			$Zalohy = new MZalohy();
			$Zalohy->removeAssociateModels();

			foreach($ids as $id){
				$sml = $SmlOM->FindOneById($id);

				$zal = $Zalohy->FindAll(
					[
						[
							'!Zalohy.od BETWEEN \'' . $sml[$SmlOM->name]['od'] . '\' AND \'' . $sml[$SmlOM->name]['do'] . '\'',
							'OR',
							'!Zalohy.do BETWEEN \'' . $sml[$SmlOM->name]['od'] . '\' AND \'' . $sml[$SmlOM->name]['do'] . '\''
						],
						'klient_id' => $info->scope->parent->recordId,
						'odber_mist_id' => $sml[$SmlOM->name]['odber_mist_id']
					]);
				if(!$zal){
					$SmlOM->Delete($id);
				}else{
					$om = new MOdberMist();
					$om = $om->FindOneById($sml[$SmlOM->name]['odber_mist_id']);
					throw new ModelSaveException('Odběrné místo ' . $om['OdberMist']['com'] . ' nelze odstranit protože jsou pod ním již vytvořeny zálohy');
				}
			}
			return true;
		}
		return false;
	}

	function createYearNav(
		$SmlOM,
		$info)
	{
		$SmlOMy = clone $SmlOM;
		$SmlOMy->removeAssociateModels();

		$SmlOMy->conditions['klient_id'] = $info->scope->parent->recordId;

		if($min = $SmlOMy->FindAll([], [
			'!YEAR(Min(od)) AS od'
		])){
			$min = reset($min);
			$min = $min[$SmlOMy->name]['od'];
		}

		if($max = $SmlOMy->FindAll([], [
			'!YEAR(MAX(od)) AS od'
		])){
			$max = reset($max);
			$max = $max[$SmlOMy->name]['od'];
		}

		$years = [];

		if($min){
			if(!$max){
				$max = $min;
			}
			for($y = $min; $y <= $max; $y++){
				$years[$y] = ($y - 1) . ' - ' . ($y + 1);
			}
		}else{
			$years = [
				date('Y') => (date('Y') - 1) . ' - ' . (date('Y') + 1)
			];
		}

		$qn = ViewsFactory::createShortNav($info->scope, 'yso', $years, 2);
		$qn->cardTitle = false;
		$qn->setCurrent(MArray::numeric_nearest(array_keys($years), OBE_DateTime::getYear()));
		$qn->handle();

		return $qn;
	}

	/**
	 *
	 * @param Array $data
	 * @param ModelFormClass2 $Form
	 * @param boolean $fromDB
	 */
	function onBeforeFillOdbMist(
		$data,
		$Form,
		$fromDB)
	{
		if(empty($data)){
			$data = [
				'SmlOM' => [],
				'OdberMist' => [
					'eic' => NULL
				]
			];
		}

		if(empty($data['SmlOM']['od'])){
			$data['SmlOM']['od'] = OBE_DateTime::now();
		}

		if(empty($data['SmlOM']['do'])){
			$data['SmlOM']['do'] = date('d.m. Y', strtotime('12/31/' . OBE_DateTime::getYear()));
		}

		if($fromDB){
			$data['SmlOM']['od'] = OBE_DateTime::convertFromDB($data['SmlOM']['od']);
			$data['SmlOM']['do'] = OBE_DateTime::convertFromDB($data['SmlOM']['do']);
		}

		$adds['od'] = $data['SmlOM']['od'];

		$eic = NULL;
		if(!empty($data['SmlOM']['odber_mist_id'])){
			$adds['ex'] = $data['SmlOM']['odber_mist_id'];

			$OdbM = new MOdberMist();
			if($om = $OdbM->FindOneById($data['SmlOM']['odber_mist_id'])){
				$eic = $om['OdberMist']['eic'] . ', ' . $om['OdberMist']['com'] . ' - ' . MAddress::addr($om);
			}
		}

		$Form->getField('SmlOM', 'odber_mist_id')->setItemLabel($eic);

		$i = OBE_DateTime::INFINITE;

		$urc = ($data['SmlOM']['do'] == $i) ? '31.12. ' . OBE_DateTime::getYear() : $data['SmlOM']['do'];

		$Form->getField('SmlOM', 'typ_sml')->setParalel($Form->getField('SmlOM', 'do'), [
			$i,
			$urc,
			$i
		]);

		return $data;
	}

	/**
	 *
	 * @param array $data
	 * @param ModelFormClass2 $Form
	 */
	function onBeforeSaveOdbMist(
		$data,
		$Form)
	{
		if(!$data['SmlOM']['odber_mist_id']){
			$Form->addErr('Není vybrané Odběrné místo.');
			return NULL;
		}

		if($Form->scope->isRecId()){
			$old = $Form->model->FindOneById($Form->scope->recordId);
			if($old['SmlOM']['typ_sml'] == '0' && $data['SmlOM']['do'] != OBE_DateTime::INFINITE && OBE_DateTime::convertFromDB($old['SmlOM']['do']) == OBE_DateTime::INFINITE){
				$data['SmlOM']['typ_sml'] = '1';
			}
		}

		if($data['SmlOM']['typ_sml'] != '1' && $data['SmlOM']['do'] != OBE_DateTime::INFINITE){
			$data['SmlOM']['do'] = OBE_DateTime::INFINITE;
		}else if($data['SmlOM']['typ_sml'] == '1' && $data['SmlOM']['do'] == OBE_DateTime::INFINITE){
			$data['SmlOM']['typ_sml'] == '2';
		}

		$data['SmlOM']['klient_id'] = $Form->scope->parent->recordId;
		$data['SmlOM']['od'] = OBE_DateTime::convertToDB($data['SmlOM']['od']);
		$data['SmlOM']['do'] = OBE_DateTime::convertToDB($data['SmlOM']['do']);

		return $data;
	}
}