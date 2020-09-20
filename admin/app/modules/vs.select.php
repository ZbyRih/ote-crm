<?php

class ModulVsselect extends AppModuleClass{
	var $topMenu = [
		  ModuleViewClass::DEFAULT_VIEW => [
		  	  'name' => 'Seznam odběrných míst'
		  	, 'icon' => 'md md-list'
		  ]
	];

	/**
	 * {@inheritDoc}
	 * @see ModuleViewClass::__listModuleItems()
	 */
	function __listModuleItems($info){
		$List = $this->_createMainListObj($info);
		if($List->handleActions()){
			$info->scope->ResetViewByRedirect();
		}
		$this->views->add($List);
		return true;
	}

	/**
	 * {@inheritDoc}
	 * @see ModuleViewClass::_createMainListObj()
	 */
	function _createMainListObj($info){
		$Model = new MZalohyWOdb();

		$spcCols = [
			'Zalohy' => [
				  'vs' => 'Var. Symbol'
				, 'CONCAT_WS(\', \', Address.city, Address.street, Address.cp, Address.co) AS adresa' => 'Adresa O.M.'
				, 'CONCAT_WS(\', \', ContactDetails.firstname, ContactDetails.lastname) AS odb' => 'Odběratel'
			]
			, 'ContactDetails' => ['firm_name' => 'Firma']
		];

		$filter = [];
		$filter[] = [
			  'type' => 'like'
			, 'fields' => ['OdberMist.eic', 'OdberMist.com', 'Address.street', 'Address.city', 'ContactDetails.firstname', 'ContactDetails.lastname', 'ContactDetails.firm_name']
			, 'name' => 'V.S., Adresa O.M., Odběratel'
		];

		if(OBE_Http::issetGet('od')){
			$Model->conditions['Year(Zalohy.od)'] = OBE_DateTime::getYear(OBE_Http::getGet('od'));
		}

		$Model->group[] = 'Zalohy.vs';

		$pages = 15;

		if(AdminUserClass::isOnlyOwn()){
			$Model->conditions['Contacts.owner_id'] = AdminUserClass::$userId;
		}

		$List = ViewsFactory::createModelList($info);
		$List->configByArray([
			  'actions' => [ListAction::EDIT, ListAction::DELETE]
			, 'model' => $Model
			, 'spcCols' => $spcCols
			, 'pagination' => true
			, 'itemsOnPage' => $pages
			, 'filter' => $filter
			, 'sort' => ['Zalohy.vs', 'CONCAT_WS(\', \', Address.city, Address.street, Address.cp, Address.co)', 'OdberMist.eic', 'OdberMist.com', 'DATE_FORMAT(OdberMist.createdate, \'%d.%m. %Y\')']
		]);

		return $List;
	}
}