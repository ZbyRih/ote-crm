<?php

class ModulZalohy extends AppModuleClass{

	var $modelName = 'MZalohy';

	var $year = null;

	var $topMenu = [
		'export' => [
			'name' => 'Exportovat zálohy',
			'icon' => 'glyphicon glyphicon-export',
			'callback' => 'export'
		]
	];

	public function __construct(
		$moduleData = NULL,
		$modelName = NULL)
	{
		parent::__construct($moduleData, $modelName);
	}

	function __listModuleItems(
		$info)
	{
		$Zalohy = new MZalohy();
		$Zalohy->removeAssociateModels();

		$years = $Zalohy->years();
		$qn = ViewsFactory::createShortNav($info->scope, 'year', $years, 2);
		$qn->cardTitle = false;
		$qn->setCurrent(MArray::numeric_nearest($years, OBE_DateTime::getYear()));

		$this->views->add($qn);

		$this->year = $qn->curr;

		$List = $this->_createMainListObj($info);
		$List->setActionCallBacks([
			ListAction::EDIT => [
				$this,
				'__editModuleItem'
			]
		]);

		if($List->handleActions()){
			$info->scope->ResetViewByRedirect();
		}

		$this->views->add($List);
		return true;
	}

	function _createMainListObj(
		$info)
	{
		if($info->isAjax()){
			if(OBE_Http::issetGet('year')){
				$this->year = OBE_Http::getGet('year');
			}else{
				$this->year = date('Y');
			}
		}

		$Zalohy = new MOdberMistWZalSum();
		$Zalohy->associatedModels['MZalohy']['conditions'][] = [
			'YEAR(Zalohy.od)' => $this->year,
			'OR',
			'YEAR(Zalohy.do)' => $this->year
		];
		$Zalohy->associatedModels['MSmlOM']['conditions'][] = '!' . $this->year . ' BETWEEN YEAR(SmlOM.od) AND YEAR(SmlOM.do)';
		$Zalohy->group[] = 'Zalohy.odber_mist_id';
		$Zalohy->group[] = 'YEAR(Zalohy.od)';

		if($info->isAjax() && OBE_Http::issetGet('kli')){
			$Zalohy->conditions['Zalohy.klient_id'] = OBE_Http::getGet('kli');
		}

		if(AdminUserClass::isOnlyOwn()){
			$Zalohy->conditions['owner_id'] = AdminUserClass::$userId;
		}

		$List = ViewsFactory::createModelList($info);
		$List->configByArray(
			[
				'model' => $Zalohy,
				'cols' => [
					'OdberMist' => [
						'!IF(SUM(getUhrZaloha(Zalohy.zaloha_id)) >= SUM(Zalohy.vyse), 3,
							IF((SELECT DISTINCT TRUE FROM tx_zalohy AS z WHERE
								OdberMist.odber_mist_id = z.odber_mist_id
								AND YEAR(z.od) = ' . $this->year . '
								AND isUhrZaloha(z.vyse, z.zaloha_id) IS NOT TRUE
								AND LAST_DAY(z.od) <= NOW()), 2, 1)) color'
					]
				],
				'spcCols' => [
					'OdberMist' => [
						'com' => 'OM'
					],
					'Address' => [
						'CONCAT_WS(\', \', Address.city, Address.street, Address.cp, Address.co)' => 'Adresa OM'
					],
					'ContactDetails' => [
						'CONCAT_WS(\', \', ContactDetails.firm_name, ContactDetails.firstname, ContactDetails.lastname)' => 'Klient'
					],
					'SmlOM' => [
						'interval' => 'Četnost'
					],
					'Zalohy' => [
						'COUNT(zaloha_id)' => 'Počet záloh',
						'SUM(vyse)' => 'Zálohy celkem',
						'!SUM(getUhrZaloha(Zalohy.zaloha_id))' => 'Uhrazeno',
						'!SUM(vyse) - SUM(getUhrZaloha(Zalohy.zaloha_id))' => 'Rozdíl'
					]
				],
				'primaryKey' => 'Zalohy.zaloha_id',
				'pagination' => true,
				'itemsOnPage' => 30,
				'sort' => [
					'OdberMist.com',
					'CONCAT_WS(\', \', Address.city, Address.street, Address.cp, Address.co)',
					'CONCAT_WS(\', \', ContactDetails.firm_name, ContactDetails.firstname, ContactDetails.lastname)'
				],
				'filter' => [
					[
						'type' => 'like',
						'fields' => [
							'OdberMist.eic',
							'OdberMist.com',
							'Address.street',
							'Address.city',
							'ContactDetails.firm_name',
							'ContactDetails.firstname',
							'ContactDetails.lastname'
						],
						'name' => 'č.o.m., EIC, ulice, město, firma, klient'
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
				'numTypes' => [
					'Zalohy' => [
						'SUM(vyse)' => 3,
						'SUM(preplatek)' => 3,
						'!SUM(getUhrZaloha(Zalohy.zaloha_id))' => 3,
						'!SUM(vyse) - SUM(getUhrZaloha(Zalohy.zaloha_id))' => 3
					]
				],
				'linesColor' => [
					'OdberMist.color' => [
						2 => 'alert-danger',
						3 => 'alert-success'
					]
				],
				'valuesSubstitute' => [
					'SmlOM' => [
						'interval' => MZalohy::$INTERVAL
					]
				]
			]);

		$List->filter->getItem(1)->setUserSetModel(
			function (
				$modelObj,
				$value)
			{
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

		$akce = new ListAction('do_zaloh');
		$akce->setTitle('Přejít do záloh odběratele')
			->setIcon('fa fa-leanpub')
			->setRight(FormFieldRights::VIEW);

		$List->actions->addAction('do_zaloh', $akce);

		$akce = new ListAction('do_odberatele');
		$akce->setTitle('Přejít do odběratele')
			->setIcon('md md-account-child')
			->setRight(FormFieldRights::VIEW);

		$List->actions->addAction('do_odberatele', $akce);

		$List->setActionCallBacks([
			'do_zaloh' => [
				$this,
				'onDoZaloh'
			],
			'do_odberatele' => [
				$this,
				'onDoOdberatele'
			]
		]);

		return $List;
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function onDoZaloh(
		$info)
	{
		if($ids = ListClass::getActionIds($info)){
			$id = reset($ids);
			$Zaloha = new MZalohy();
			$Zaloha->removeAssociateModels();
			if($zal = $Zaloha->FindOneById($id)){
				$year = OBE_DateTime::getYearDB($zal['Zalohy']['od']);
				AdminApp::Redirect(
					'module=contacts&contactsv=edit&contactsr=' . $zal['Zalohy']['klient_id'] . '&czalohyr=' . $zal['Zalohy']['odber_mist_id'] . '&czalohya=edit&year=' . $year . '&selTab=zalohy');
			}
		}
		return true;
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function onDoOdberatele(
		$info)
	{
		if($ids = ListClass::getActionIds($info)){
			$id = reset($ids);
			$Zaloha = new MZalohy();
			$Zaloha->removeAssociateModels();
			if($zal = $Zaloha->FindOneById($id)){
				AdminApp::Redirect('module=contacts&contactsv=edit&contactsr=' . $zal['Zalohy']['klient_id'] . '&selTab=edit');
			}
		}
		return true;
	}

	function __editModuleItem(
		$info)
	{
		parent::__editModuleItem($info);

		$info->setAccess(FormFieldRights::VIEW);

		$Platby = $this->getBaseModel();

		$Form = ViewsFactory::createModelForm($Platby, $info);

		$Form->buttons->clear();
		$Form->buttons->addCancel(FormButton::CANCEL, 'Zpět');

		$Form->setAppCallBack(AppFormClass2::ON_BEFORE_FILL, [
			$this,
			'onBeforeFill'
		]);

		$Form->processForm();

		$this->views->add($Form);

		return true;
	}

	/**
	 * @param array $data
	 * @param ModelFormClass2 $form
	 * @param boolean $fromDB
	 */
	function onBeforeFill(
		$data,
		$form,
		$fromDB)
	{
		if(!empty($data) && $fromDB){
			$data['Zalohy']['od'] = OBE_DateTime::convertFromDB($data['Zalohy']['od']);
			$data['Zalohy']['do'] = OBE_DateTime::convertFromDB($data['Zalohy']['do']);
		}
		return $data;
	}

	/**
	 * @return boolean
	 */
	function export()
	{
		$OdbExpView = new ZalohyExportView();
		$OdbExpView->init($this->info);
		$this->views->add($OdbExpView);

		return true;
	}
}