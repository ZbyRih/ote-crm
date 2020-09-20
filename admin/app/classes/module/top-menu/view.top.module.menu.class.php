<?php

class ViewTopModuleMenuClass{
	var $items = [];
	var $configs = [];

	/**
	 *
	 * @param Array $config
	 */
	function __construct($config = []){
		$this->configs = $config;
	}

	/**
	 *
	 * @param Object $parent
	 * @param integer $userLevel
	 */
	function init($parent, $userLevel){
		foreach($this->configs as $key => $item){
			$newItem = NULL;
			if(is_object($item)){
				$newItem = $item;
			}else{
				$newItem = new TopMenuItemClass();
				$newItem->init($item);
			}
			if($newItem->checkAccess($userLevel)){
				$this->items[$key] = $newItem;
			}
		}
	}

	function getDefault(){
		if(is_array($this->items) && !empty($this->items)){
			reset($this->items);
			return key($this->items);
		}
	}

	function getView(){
		return $this->items;
	}

	function isCallAble($key){
		if(isset($this->items[$key])){
			return $this->items[$key]->isCallAble();
		}
		return false;
	}

	function callback($key, $parent){
		if(isset($this->items[$key])){
			return $this->items[$key]->callback($parent);
		}
		return false;
	}
}