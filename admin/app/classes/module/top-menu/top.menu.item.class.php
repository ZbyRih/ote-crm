<?php

class TopMenuItemClass{
	/**
	 * @var CallBackItemClass
	 */
	var $callback = NULL;

	var $name = '';
	var $icon = NULL;
	var $access = NULL;
	var $confirm = false;

	function __construct($name = '', $icon = NULL, $access = NULL, $callback = NULL){
		$this->callback = new CallBackItemClass($callback);
		$this->icon = $icon;
		$this->name = $name;
		$this->access = $access;
	}

	function init($config){
		$this->callback = (isset($config['callback']))? new CallBackItemClass($config['callback']): $this->callback;
		$this->icon = (isset($config['icon']))? $config['icon'] : $this->icon;
		$this->name = (isset($config['name']))? $config['name'] : $this->name;
		$this->access = (isset($config['access']))? $config['access'] : $this->access;
		$this->confirm = (isset($config['confirm']))? $config['confirm'] : false;
	}

	function callback($parent){
		if($this->callback->isCallAble()){
			return $this->callback->call([$this], $parent);
		}
	}

	function isCallAble(){
		return $this->callback->isCallAble();
	}

	function checkAccess($access){
		if($this->access == NULL || $access <= $this->access){
			return true;
		}
		return false;
	}
}