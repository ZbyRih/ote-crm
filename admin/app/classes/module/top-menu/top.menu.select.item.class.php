<?php

class TopMenuSelectItemClass extends TopMenuItemClass{
	var $list = [];
	var $selected = NULL;

	function __construct($name = '', $userAccessLevel = 0, $callToView = NULL, $callToCreate = NULL){
		if(is_array($name)){
			$name['type'] = 'select';
			parent::__construct($name);
		}else{
			parent::__construct('select', $name, $userAccessLevel, $callToView, $callToCreate);
		}
	}

	function initByArray($config){
		parent::initByArray($config);
		$this->type = 'select';
		if(isset($config['items'])){
			if(is_object($config['items'])){
				$this->list = $config['items']->items;
			}else{
				$this->list = $config['items'];
			}
		}
		if(isset($config['select'])){
			$this->selected = $config['select'];
		}
	}

	function getView(){
		$array = parent::getView();
		$array = array_merge($array, [
			  'items' => $this->list
			, 'select' => $this->selected
		]);
		return $array;
	}
}