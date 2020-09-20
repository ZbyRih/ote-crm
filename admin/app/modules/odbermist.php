<?php

class ModulOdbermist extends AppModuleClass{

	var $modelName = 'MOdberMist';

	var $topMenu = [
		ModuleViewClass::DEFAULT_VIEW => [
			'name' => 'Seznam odběrných míst',
			'icon' => 'md md-list'
		],
		'create' => [
			'name' => 'Vytvořit nové odběrné místo',
			'icon' => 'md md-my-library-add'
		],
		'export' => [
			'name' => 'Exportovat odběrná místa',
			'icon' => 'glyphicon glyphicon-export',
			'callback' => 'export'
		]
	];

	public function __construct($moduleData = NULL, $modelName = NULL){
		parent::__construct($moduleData, $modelName);
	}

	function __listModuleItems($info){
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

	function _createMainListObj($info){
		$Model = $this->getBaseModel();

		$cols = [
			'OdberMist' => [
				'com',
				'eic'
			]
		];
		$spcCols = [
			'OdberMist' => [
				'CONCAT_WS(\', \', Address.city, Address.street, Address.cp, Address.co) AS adresa' => 'Adresa',
				'DATE_FORMAT(createdate, \'%d.%m. %Y\')' => 'Datum vytvoření'
			]
		];
		$filter = [];
		$filter[] = [
			'type' => 'like',
			'fields' => [
				'OdberMist.eic',
				'OdberMist.com',
				'Address.street',
				'Address.city'
			],
			'name' => 'EIC, č. o.m., adresa'
		];

		$date = null;

		if($info->isAjax()){
			$Model->conditions['deprecated'] = 0;
			if(OBE_Http::issetGet('ex')){
				$Model->conditions[] = '!o.odber_mist_id != ' . OBE_Http::getGet('ex');
			}
			$numbered = false;
			$pages = 15;

			if(OBE_Http::issetGet('od')){
				$date = OBE_DateTime::convertToDB(OBE_Http::getGet('od'));

				if(OBE_Http::issetGet('do')){
					$dateDo = OBE_DateTime::convertToDB(OBE_Http::getGet('do'));
					$Model->conditions['odber_mist_id'] = [
						'NOT IN' => 'SELECT o.odber_mist_id FROM tx_sml_om AS o WHERE \'' . $date . '\' BETWEEN o.od AND o.do OR \'' . $dateDo . '\' BETWEEN o.od AND o.do'
					];
				}else{
					$Model->conditions['odber_mist_id'] = [
						'NOT IN' => 'SELECT o.odber_mist_id FROM tx_sml_om AS o WHERE o.do > \'' . $date . '\''
					];
				}
			}

			if(OBE_Http::issetGet('for') && OBE_Http::getGet('for') == 'mwh'){

				if(OBE_Http::issetGet('in')){
					$spcCols['OdberMist']['!(SELECT IF(COUNT(o.id) > 0, TRUE, FALSE) FROM tx_cena_mwh AS o WHERE o.klient_id = ' . OBE_Http::getGet('in') . ' AND o.odber_mist_id = OdberMist.odber_mist_id) AS exi'] = 'X';
				}
			}

			if(OBE_Http::issetGet('in')){
				$Model->conditions['odber_mist_id'] = [
					'IN' => 'SELECT o.odber_mist_id FROM tx_sml_om AS o WHERE o.klient_id = ' . OBE_Http::getGet('in') . ''
				];
			}
		}else{
			$numbered = true;
			$pages = 30;
			$filter[] = [
				'type' => 'x',
				'fields' => [
					'OdberMist.deprecated'
				],
				'name' => 'Neaktivní'
			];
		}

		if(AdminUserClass::isOnlyOwn()){

			$do = '';
			if($date){
				$do = 'AND so.do < \'' . $date . '\'';
			}

			$Model->conditions[] = 'AND';
			$Model->conditions[] = [
				'owner_id' => AdminUserClass::$userId,
				'OR',
				'!odber_mist_id NOT IN (
					SELECT so.odber_mist_id
					FROM tx_sml_om AS so, es_klients AS k
					WHERE k.owner_id != ' . AdminUserClass::$userId . '
						AND k.deleted = 0 AND k.active = 1 AND k.disabled = 0
						AND so.klient_id = k.klient_id
						' . $do . '
				)
			'
			];
		}

		$List = ViewsFactory::createModelList($info);
		$List->configByArray(
			[
				'actions' => [
					ListAction::EDIT,
					ListAction::DELETE
				],
				'model' => $Model,
				'cols' => $cols,
				'spcCols' => $spcCols,
				'numbered' => $numbered,
				'pagination' => true,
				'itemsOnPage' => $pages,
				'filter' => $filter,
				'sort' => [
					'CONCAT_WS(\', \', Address.city, Address.street, Address.cp, Address.co)',
					'OdberMist.eic',
					'OdberMist.com',
					'DATE_FORMAT(OdberMist.createdate, \'%d.%m. %Y\')'
				],
				'headInfo' => [
					'exi' => 'Nemá zadanou cenu za mwh'
				],
				'valuesSubstitute' => [
					'OdberMist' => [
						'exi' => [
							1 => '',
							0 => 'x'
						]
					]
				]
			]);
		$List->setActionCallBacks([
			ListAction::DELETE => [
				$this,
				'onListDeleteOdberMist'
			]
		]);

		if($List->filter->getItem(1) && $List->filter->getItem(1)->getValue() == 1){
			if($action = $List->actions->get(ListAction::DELETE)){
				$action->setIcon('md md-undo');
				$action->setTitle('Vrátit');
				$action->setMass('Vrátit');
			}
		}

		return $List;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param ModelListClass $list
	 * @return Boolean
	 */
	function onListDeleteOdberMist($info, $list){
		if($ids = ListClass::getActionIds($info)){
			$OdbMist = $this->getBaseModel();
			$OdbMist->removeAssociateModels();
			foreach($ids as $id){
				if($om = $OdbMist->FindOneById($id)){
					$om[$OdbMist->name]['deprecated'] = (($list->filter->getItem(1)->getValue() == 1) ? 0 : 1);
					$OdbMist->Save($om);
				}
			}
			return true;
		}
		return false;
	}

	function createMainShortNav($info){
		$shortNav = ViewsFactory::createModelShortNav($this->_createMainListObj($info), $info, [
			'Contacts' => 'email'
		]);
		$this->views->add($shortNav);
	}

	/**
	 *
	 * @return boolean
	 */
	function export(){
		$OdbExpView = new OdbMistExportView();
		$OdbExpView->init($this->info);
		$this->views->add($OdbExpView);

		return true;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function __editModuleItem($info){
		parent::__editModuleItem($info);

		$tabViewObj = ViewsFactory::createTabs($info);
		$tabViewObj->setMulti([
			'edit' => 'Odběrné místo',
			'paneBasic',
			'hist' => 'Historie odběratelů',
			'histOdb'
		], $this);

		if(!$info->scope->isEmptyRecId()){
			$this->views->add($tabViewObj);

			$this->addLinkToOdb($info);

			return $tabViewObj->handleCallBacks($info);
		}

		$tabViewObj->reset(); // kdyz jsme v create tak reset na prvni zalozku

		return $this->paneBasic($info);
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function paneBasic($info){
		$OdbMist = $this->getBaseModel();

		$Form = ViewsFactory::createModelForm($OdbMist, $info);

		$Form->getField('OdberMist', 'dist_id')->setList(MOdberMist::$DIST);

		$User = new MUser();
		$User->conditions[] = '!login IS NOT NULL';

		$field = $Form->getField('OdberMist', 'owner_id');
		if(AdminUserClass::isChangeOwner()){
			$field->setAccess(FormFieldRights::EDIT);
		}
		$field->setListByModel($User, 'id', 'jmeno', false);

		if($info->scope->isEmptyRecId()){
			$Form->removeField('OdberMist_created_by');
		}else{
			$field = $Form->getField('OdberMist', 'created_by');
			$field->setListByModel($User, 'id', 'jmeno', false);
		}

		$Form->setAppCallBack(AppFormClass2::ON_BEFORE_SAVE, [
			$this,
			'onBeforeSaveBasic'
		]);

		$Form->processForm();

		$this->views->add($Form);

		return true;
	}

	/**
	 *
	 * @param array $data
	 * @param ModelFormClass2 $form
	 */
	function onBeforeSaveBasic($data, $form){
		if(!array_key_exists('created_by', $data['OdberMist'])){
			$data['OdberMist']['created_by'] = AdminUserClass::$userId;
		}else{
			unset($data['OdberMist']['created_by']);
		}

		return $data;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function histOdb($info){
		$SmlOM = new MSmlOMWContact();
		$SmlOM->order = [
			'od'
		];
		$SmlOM->conditions['odber_mist_id'] = $info->scope->recordId;

		$List = ViewsFactory::createModelList($info);

		$List->configByArray(
			[
				'actions' => [], //ListAction::EDIT, ListAction::DELETE)
				'model' => $SmlOM,
				'cols' => [],
				'spcCols' => [
					$SmlOM->name => [
						'DATE_FORMAT(od, \'%d.%m. %Y\')' => 'Od',
						'DATE_FORMAT(do, \'%d.%m. %Y\')' => 'Do'
					],
					'ContactDetails' => [
						'kind' => 'Forma',
						'firstname' => 'Jméno',
						'lastname' => 'Přijmení',
						'firm_name' => 'Název subjektu'
					]
				],
				'valuesSubstitute' => [
					'ContactDetails' => [
						'kind' => MContactDetails::$KIND_SHR
					]
				]
			]);

		$this->views->add($List);
		return true;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function addLinkToOdb($info){
		$SmlOM = new MSmlOMWContact();

		if($sml = $SmlOM->FindOne([
			'odber_mist_id' => $info->scope->recordId,
			'!NOW() BETWEEN od AND do'
		])){

			$Link = ViewsFactory::createLink($info->scope->getLink('to_odb'),
				'Přejít do aktuálního odběratele ' . MContactDetails::sname($sml['ContactDetails']), 'md md-account-child');

			if($info->scope->action == 'to_odb'){
				AdminApp::Redirect('module=contacts&contactsv=edit&contactsr=' . $sml['SmlOM']['klient_id']);
			}

			$this->views->add($Link);
		}
		return true;
	}
}