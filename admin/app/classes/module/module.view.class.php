<?php

class ModuleViewClass extends SubModule{

	/**
	 * array(key => array('type' => 'link'|'select', 'name' => 'Název', 'callback' => 'callbackfce') key - jako parametr v url, nazev jako nazev
	 * @var ViewTopModuleMenuClass
	 */
	public $topMenu = [];

	public $handlers = [
		  self::DEFAULT_VIEW => '__listModuleItems'
		, self::CREATE => '__createModuleItem'
		, ListAction::SELECT => '__selectModuleItem'
		, ListAction::EDIT => '__editModuleItem'
	];

 	/**
 	 * @param array/string $modul
 	 * @param SubModule $parent
 	 * @param string $name
 	 */
	function __construct($moduleData = NULL, $parent = NULL, $name = null){
		parent::__construct($moduleData, $parent);
		$this->topMenu = new ViewTopModuleMenuClass($this->topMenu);
	}

	function initTopMenu(){
		$this->topMenu->init($this, $this->info->access);
	}

	/**
	 * @return boolean
	 */
	function callback(){
		if($result = $this->topMenu->callback($this->scope->getView(), $this)){
			return $result;
		}
		return parent::callback();
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function __createModuleItem($info){
		return $this->__editModuleItem($info);
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function _createMainListObj($info){
		return false;
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function __listModuleItems($info){
		return false;
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function __selectListItems($info){
		return $this->__listModuleItems($info);
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function __selectModuleItem($info){
		$this->info->selectedItem = $info->scope->recordId;
		return false;
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function __editModuleItem($info){
		$this->__handleEdit($info);
		return true;
	}

	/**
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function __handleEdit($info, $action = ListAction::EDIT, $toView = ListAction::EDIT){
		if(OBE_Http::isGetIs($info->scope->getActionKey(), $action)){
			$info->scope->resetViewByRedirect($info->scope->recordId, $toView);
		}
	}
}