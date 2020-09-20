<?php

class ModulSettings extends AppModuleClass{

	var $modelName = 'MSettings';

	var $ajaxRequests = [
		'edGN' => '_renameGroup'
	];

	var $groups;

	var $selected = NULL;

	var $fields;

// 	var $menu = [];

	/**
	 *
	 * @var SettingsHelper
	 */
	var $helper = NULL;

	function __construct(
		$moduleData = NULL,
		$userId = NULL,
		$langId = NULL,
		$modelName = NULL)
	{
		parent::__construct($moduleData, $userId, $langId, $modelName);

		$this->helper = new SettingsHelper();

		$this->groups = MArray::MapVal(MArray::FilterMArray($this->helper->getGroups(), 'active', true), 'name');
	}

	function __listModuleItems(
		$info)
	{
		$groupsTab = $this->tabsView($info);

		$this->_listEditForm($info);
		return true;
	}

	function tabsView(
		$info)
	{
		$tabs = ViewsFactory::createTabs($info, $this, 'Skupina')->setItems($this->groups);

		$this->selected = $tabs->handleValue();

		return $this->views->add($tabs);
	}

	function _listEditForm(
		$info)
	{
		$Form = ViewsFactory::createForm($this->scope);
		$Form->setStatuses([
			'lock',
			'globe'
		]);

		$data = $this->helper->setUpForm($this->selected, $Form);

		$Form->buttons->addSubmit(FormButton::SAVE, 'Uložit');

		$ret = $Form->handleFormSubmit();

		if($ret === false){
			$this->scope->resetViewByRedirect();
		}else if($ret){
			$data = $this->handleMenus($ret, $Form);
			$this->helper->save($data);
		}else{
			$Form->fillWithData($data);
		}

		$this->views->add($Form);
	}

	/**
	 *
	 * @param array $data
	 * @param AppFormClass2 $form
	 */
	function handleMenus(
		$data,
		$form)
	{
		return $data;
	}
}