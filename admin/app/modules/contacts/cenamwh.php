<?php

class SenaMWHSubModule extends SubModule{

	var $handlers = [
		self::DEFAULT_VIEW => 'editCenaMWH',
		self::CREATE => 'editCenaMWH',
		ListAction::EDIT => 'editCenaMWH'
	];

	/**
	 *
	 * @var ShortNavClass
	 */
	var $qn = null;

	public $onQNHandle = null;

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function editCenaMWH($info){
		// seznam fak skupin a jejich editace
		if(!$info->scope->parent->isEmptyRecId()){
			$CenaMWH = new MCenaMWH();

			$this->qn = $qn = $this->createYearsNav($info);

			$Form = ViewsFactory::createModelForm($CenaMWH, $info, 'cena_mwh');
			$FormCpy = ViewsFactory::createModelForm($CenaMWH, $info, 'cena_mwh_cpy');

			$Form->setAppCallBack(AppFormClass2::ON_BEFORE_FILL, [
				$this,
				'onBeforeFillCMWH'
			]);
			$Form->setAppCallBack(AppFormClass2::ON_BEFORE_SAVE, [
				$this,
				'onBeforeSaveCMWH'
			]);

			$Form->recursionSave = -1;

			$Form->getField('CenaMWH', 'odber_mist_id')
				->setSelect('Vybrat', MODULES::ODBER_MIST)
				->setFieldsToUrl([
				'od' => 'CenaMWH.od'
			])
				->setAdds([
				'for' => 'mwh'
			]);

			if($info->scope->action == ListAction::EDIT && $info->scope->isSetRecId()){

				$this->views->add(ViewsFactory::createLink($info->scope->parent->getLink(), 'Zpět na seznam podle OM', 'md md-backspace')); // md-arrow-back

				$ret = $Form->processForm(false, true); // nejde vyskocit z editace SmlOM pres zrusit

				if($ret === false){
					$info->scope->resetViewByRedirect();
				}

				if($Form->isSaved()){
					$info->scope->resetViewByRedirect();
				}
			}else{
				$this->views->add($qn);

				$rec_id = $info->scope->recordId;
				$info->scope->recordId = NULL;

				$Form->buttons->addSubmit(FormButton::CREATE, 'Přidat');
				$Form->processForm(true);

				if($Form->isSaved()){
					$info->scope->resetViewByRedirect();
				}

				$FormCpy->buttons->addSubmit(FormButton::CREATE, 'Kopírovat vše se zadanou platností od');
				$FormCpy->setAppCallBack(AppFormClass2::ON_BEFORE_FILL, [
					$this,
					'onBeforeFillCopyCMWH'
				]);
				$FormCpy->setAppCallBack(AppFormClass2::ON_BEFORE_SAVE, [
					$this,
					'onBeforeSaveCopyCMWH'
				]);
				$FormCpy->processForm(true);

				$info->scope->recordId = $rec_id;

				$CenaMWH->conditions['klient_id'] = $info->scope->parent->recordId;
				$CenaMWH->conditions['YEAR(od)'] = $qn->curr;

				$List = ViewsFactory::createModelList($info);
				$List->configByArray(
					[
						'form' => 'cena_mwh',
						'actions' => [
							ListAction::EDIT,
							ListAction::DELETE
						],
						'model' => $CenaMWH,
						'spcCols' => [
							'OdberMist' => [
								'eic' => 'EIC',
								'com' => 'číslo odběrného místa'
							],
							'Address' => [
								'CONCAT_WS(\', \', Address.city, Address.street, Address.cp, Address.co)' => 'Adresa'
							],
							'CenaMWH' => [
								'DATE_FORMAT(od, \'%d.%m. %Y\')' => 'Platí od',
								'cena' => 'Cena MW/h'
							]
						],
						'primaryKey' => 'CenaMWH.id',
						'ajaxRowsEdit' => [
							'CenaMWH' => [
								'cena' => FormUITypes::TEXT
							]
						],
						'ajaxHandle' => [
							$this,
							'ajaxListCenaMWH'
						],
						'numTypes' => [
							'CenaMWH' => [
								'cena' => 3
							]
						],
						'sort' => [
							'OdberMist.eic',
							'OdberMist.com',
							'CenaMWH.od'
						],
						'numbered' => true,
						'static' => true
					]);

				if($List->handleActions()){
					$info->scope->resetViewByRedirect();
				}

				$this->views->add($List);
				$this->views->add(ViewsFactory::newCardPane('Přidat cenu energie pro odběrné místo'));
			}

			$this->views->add($Form);

			if(!($info->scope->action == ListAction::EDIT && $info->scope->isSetRecId())){
				$this->views->add(ViewsFactory::newCardPane('Kopírovat vše se zadanout platností a cenou'));
				$this->views->add($FormCpy);
			}
		}
		return true;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @return ShortNavClass
	 */
	private function createYearsNav($info){
		$CenaMWH = new MCenaMWH();
		$CenaMWH->removeAssociateModels();
		$CenaMWH->conditions['klient_id'] = $info->scope->parent->recordId;
		$CenaMWH->group[] = 'YEAR(od)';

		if($items = $CenaMWH->FindAll([], [
			'YEAR(od) AS y'
		])){
			foreach($items as $i){
				$years[] = $i[$CenaMWH->name]['y'];
			}
		}else{
			$years[] = OBE_DateTime::getYear();
		}

		$qn = ViewsFactory::createShortNav($info->scope, 'year', array_combine($years, $years), 2);
		$qn->cardTitle = false;
		$qn->setCurrent(MArray::numeric_nearest($years, OBE_DateTime::getYear()));
		$qn->onHandle[] = $this->onQNHandle;

		return $qn;
	}

	/**
	 *
	 * @param string $field
	 * @param mixed $value
	 * @param ListClass $List
	 * @return boolean
	 */
	function ajaxListCenaMWH($field, $value, $List){
		$CenaMWH = new MCenaMWH();
		$CenaMWH->removeAssociateModels();

		if($cena = $CenaMWH->FindOneById($List->info->scope->recordId)){
			switch($field){
				case 'CenaMWH_cena':
					$cena[$CenaMWH->name]['cena'] = OBE_Math::correctFloatNumber(OBE_Math::removeCurrency($value));
					break;
				default:
					throw new AjaxException($field . ' nebyl zpracován');
			}
			try{
				$CenaMWH->Save($cena);
			}catch(ModelSaveException $e){
				$e->log();
				throw new AjaxException($e->getMessage());
			}
		}else{
			throw new AjaxException('položka nebyla nalezena');
		}
		return true;
	}

	/**
	 *
	 * @param Array $data
	 * @param ModelFormClass2 $Form
	 * @param boolean $fromDB
	 */
	function onBeforeFillCMWH($data, $Form, $fromDB){
		if(empty($data)){
			$data = [
				'CenaMWH' => [],
				'OdberMist' => [
					'eic' => NULL
				]
			];
		}

		if(empty($data['CenaMWH']['od'])){
			$data['CenaMWH']['od'] = OBE_DateTime::now();
		}

		if($fromDB){
			$data['CenaMWH']['od'] = OBE_DateTime::convertFromDB($data['CenaMWH']['od']);
		}

		$eic = NULL;

		if(!empty($data['CenaMWH']['odber_mist_id'])){

			$OdbM = new MOdberMist();
			if($om = $OdbM->FindOneById($data['CenaMWH']['odber_mist_id'])){
				$eic = $om['OdberMist']['eic'] . ', ' . $om['OdberMist']['com'] . ' - ' . MAddress::addr($om);
			}
		}

		$Form->getField('CenaMWH', 'odber_mist_id')->setItemLabel($eic)->setAdds([
			'in' => $Form->scope->parent->recordId
		]);

		return $data;
	}

	/**
	 *
	 * @param array $data
	 * @param ModelFormClass2 $Form
	 */
	function onBeforeSaveCMWH($data, $Form){
		if(!$data['CenaMWH']['odber_mist_id']){
			$Form->addErr('Není vybrané Odběrné místo.');
			return NULL;
		}

		$data['CenaMWH']['klient_id'] = $Form->scope->parent->recordId;
		$data['CenaMWH']['od'] = OBE_DateTime::convertToDB($data['CenaMWH']['od']);

		return $data;
	}

	/**
	 *
	 * @param Array $data
	 * @param ModelFormClass2 $Form
	 * @param boolean $fromDB
	 */
	function onBeforeFillCopyCMWH($data, $Form, $fromDB){
		if(empty($data['CenaMWH']['od'])){
			$data['CenaMWH']['od'] = '1.1. ' . ($this->qn->curr + 1);
		}
		return $data;
	}

	/**
	 *
	 * @param array $data
	 * @param ModelFormClass2 $Form
	 */
	function onBeforeSaveCopyCMWH($data, $Form){
		$cena = OBE_Math::correctFloatNumber(OBE_Math::removeCurrency($data['CenaMWH']['cena']));

		if(empty($data['CenaMWH']['od'])){
			$Form->addErr('Prádné datum.');
			return NULL;
		}

		$newDate = OBE_DateTime::convertToDB($data['CenaMWH']['od']);

		$CenaMWH = new MCenaMWH();
		$CenaMWH->removeAssociateModels();
		$CenaMWH->conditions['klient_id'] = $Form->scope->parent->recordId;
		$CenaMWH->conditions['YEAR(od)'] = $this->qn->curr;

		$all = $CenaMWH->FindAll();

		foreach($all as &$i){
			unset($i['CenaMWH']['id']);
			$i['CenaMWH']['od'] = $newDate;
			if(!empty($cena)){
				$i['CenaMWH']['cena'] = $cena;
			}
		}

		if(!empty($all)){
			$CenaMWH->Save($all);
		}

		$this->activityLog(
			'Zkopírováno', 'Byly zkopírovány ceny MW/h z roku ' . $this->qn->curr . ' s nouvou platností od ' . $data['CenaMWH']['od'] . ' .', null, 'info');

		$Form->scope->resetViewByRedirect();
		return NULL;
	}
}