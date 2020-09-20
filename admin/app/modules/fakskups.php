<?php

class ModulFakskups extends AppModuleClass{

	var $modelName = 'MFakSkupList';

	var $topMenu = [
		ModuleViewClass::DEFAULT_VIEW => [
			'name' => 'Seznam fakturačních skupin',
			'icon' => 'md md-list'
		]
	];

	var $deleted = false;

	/**
	 * (non-PHPdoc)
	 * @see ModuleViewClass::__listModuleItems()
	 */
	function __listModuleItems($info){
		$List = $this->_createMainListObj($info);

		$List->setActionCallBacks([
			ListAction::EDIT => [
				$this,
				'__editModuleItem'
			]
		]);

		if($List->handleActions()){
			$info->scope->resetViewByRedirect();
		}

		$this->views->add($List);
		return true;
	}

	/**
	 * (non-PHPdoc)
	 * @see ModuleViewClass::_createMainListObj()
	 */
	function _createMainListObj($info){
		return $this->createMainList($info);
	}

	/**
	 *
	 * @param ModuleUrlScope $info
	 * @return ModelListClass
	 */
	function createMainList($info){
		$FakSkup = $this->getBaseModel();

		$List = ViewsFactory::createModelList($info);

		$FakSkup->conditions['Contacts.deleted'] = 0;

		if(AdminUserClass::isOnlyOwn()){
			$FakSkup->conditions['owner_id'] = AdminUserClass::$userId;
		}

		$List->configByArray(
			[
				'actions' => [
					ListAction::EDIT /* , ListAction::DELETE */
				],
				'model' => $FakSkup,
				'cols' => [
					'FakSkup' => [
						'cis',
						'nazev'
					]
				],
				'spcCols' => [
					'ContactDetails' => [
						'CONCAT_WS(\' \', ContactDetails.firstname, ContactDetails.lastname, ContactDetails.firm_name)' => 'Odběratel'
					],
					'Address' => [
						'CONCAT_WS(\', \', Address.city, Address.street, Address.cp, Address.co) AS adresa' => 'Adresa'
					]
				],
				'pagination' => true,
				'itemsOnPage' => 25,
				'filter' => [
					[
						'type' => 'like',
						'fields' => [
							'FakSkup.cis',
							'FakSkup.nazev',
							'ContactDetails.email',
							'ContactDetails.firstname',
							'ContactDetails.lastname'
						],
						'name' => 'Jméno, Přijmení, Mail'
					]
				],
				'sort' => [
					'FakSkup.cis',
					'FakSkup.nazev'
				],
				'numbered' => true
			]);

		return $List;
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
			'edit' => 'Fakturační skupina',
			'paneBasic',
			'odblist' => 'Odběrná místa',
			'odbmistList'
		], $this);

		if(!$info->scope->isEmptyRecId()){

			$this->createShortNav($info);

			$this->views->add($tabViewObj);

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
		$FakSkup = $this->getBaseModel();

		$Form = ViewsFactory::createModelForm($FakSkup, $info);

		$Form->getField('ContactDetails', 'kind')->setList(MContactDetails::$KIND);

		$Form->processForm();

		$this->views->add($Form);

		return true;
	}

	/**
	 *
	 * @param ModuleUrlScope $info
	 */
	function createShortNav($info){
		$name = [
			'FakSkup' => [
				'cis'
			],
			'ContactDetails' => [
				'firstname',
				'lastname',
				'firm_name'
			]
		];
		$List = $this->createMainList($info, ModuleViewClass::DEFAULT_VIEW);

		$this->views->add(ViewsFactory::createModelShortNav($List, $info->scope, $name));
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function odbmistList($info){
		$SmlOM = new MSmlOM();
		$SmlOM->removeAssociatedModelsByType([
			'MSmlOMFlags',
			'MFakSkup'
		]);
		$SmlOM->conditions['fak_skup_id'] = $info->scope->recordId;

		$List = ViewsFactory::createModelList($info);

		$List->configByArray(
			[
				'actions' => [], //ListAction::EDIT, ListAction::DELETE)
				'model' => $SmlOM,
				'cols' => [],
				'spcCols' => [
					'OdberMist' => [
						'com' => 'Č. Odb. M.',
						'eic' => 'EIC',
						'CONCAT_WS(\', \', Address.city, Address.street, Address.cp, Address.co) AS adresa' => 'Adresa'
					]
				],
				'sort' => [
					'OdberMist.com'
				]
			]);

		$this->views->add($List);
		return true;
	}
}