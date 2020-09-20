<?php

class ModelListFilterClass{
	const GET_FILTER = 'filter';
	const GET_RESET = 'rFilter';
	const GET_GLOBAL_RESET = 'gFilter';
	const GET_GLOBAL_PUT = 'pgFilter';

	const FILTER_FIELD_PREFIX = 'ff_';
	const FILTERS_SES_KEY = 'listFilterCFG';

	/**
	 * @var ModelClass
	 */
	var $model = NULL;
	var $config = NULL;
	var $defaults = [];
	var $getKey = NULL;
	var $fields = [];

	/**
	 *
	 * @param Array $config
	 * @param ModelListClass $list
	 */
	function __construct($config, $list){
		$this->config = $config;

		$this->getKey = '_' .  $list->getRealUID();
		$this->sessionKey = 'f2l_' .  $list->getRealUID();

		$this->init($list);
		$this->handle();
	}

	function init($list){
		if($this->config){
			$this->createFields($list);
			$this->loadSessionData();
		}
	}

	function createFields($list){
		foreach($this->config as $index => $params){
			$fieldKeyName = self::FILTER_FIELD_PREFIX . $index;
			$field = new ModelListFilterItemClass($fieldKeyName, $params, $list->info);
			$this->fields[$index] = $field;
		}
	}

	function loadSessionData(){
		$ses = [];
		$sessions = OBE_Session::read(self::FILTERS_SES_KEY);
		if(isset($sessions[$this->sessionKey])){
			$ses = $sessions[$this->sessionKey];
		}

		if(count($ses) != count($this->config)){
			$ses = array_pad([], count($this->config), NULL);
		}


		foreach($this->fields as $i =>  $field){
			if(isset($ses[$i]) && $ses[$i] !== NULL){
				$field->setValue($ses[$i]);
			}
		}
	}

	function handle(){
		if($this->config){
			$this->handleUrl();
			$this->save();
		}
	}

	function handleUrl(){
		if(OBE_Http::isGetIs(self::GET_RESET . $this->getKey, 'reset') || OBE_Http::isGetIs(self::GET_GLOBAL_RESET, 'reset')){
			OBE_Log::log('reset filter');
			if($this->config){
				foreach($this->config as $index => $params){
					$this->fields[$index]->resetValue();
				}
			}
		}else if(OBE_Http::issetGet(self::GET_GLOBAL_PUT)){
			$value = OBE_Http::getGet(self::GET_GLOBAL_PUT);
			$first = reset($this->fields);
			$first->setValue($value);
		}else{
			if($filterParams = OBE_Http::getGet(self::GET_FILTER . $this->getKey)){
				$getFilterItems = explode(';', $filterParams);

				foreach($getFilterItems as $i => $value){
					if(!empty($value) || $value === 0 || $value === '0'){
						$this->fields[$i]->setValue($value);
					}
				}
			}
		}
	}

	function save(){
		$ses = [];

		foreach ($this->fields as $i => $field){
			$ses[] = $field->getValue();
		}

 		$sessions = OBE_Session::read(self::FILTERS_SES_KEY);
		$sessions[$this->sessionKey] = $ses;
		OBE_Session::write(self::FILTERS_SES_KEY, $sessions);
	}

	/**
	 * @param ModelClass $model
	 */
	function setUpModel($model){
		if($this->config){
			$prev = null;
			foreach($this->config as $i => $params){
				$this->fields[$i]->setUpModel($model, $prev);
				$prev = $this->fields[$i];
			}
		}
	}

	function getView(){
		$fields  = [];
		foreach($this->fields as $field){
			$fields[] = $field->getView();
		}
		return $fields;
	}

	/**
	 *
	 * @param string $key
	 * @return ModelListFilterItemClass
	 */
	function getItem($key){
		if(isset($this->fields[$key])){
			return $this->fields[$key];
		}
	}
}