<?php
class FormFieldInfo{
	/**
	 * @var FormFieldClass
	 */
	var $parent = NULL;

	function __construct(&$arrayDefinition = [], $parent){
		$this->parent = $parent;
	}

	function extractData($data){
		if(isset($data[$this->parent->key])){
			return $data[$this->parent->key];
		}
		return NULL;
	}

	function extractDataOrDefault($data, $globalDefaultValue = NULL){
		if(is_array($data) && array_key_exists($this->parent->key, $data)){
			return $data[$this->parent->key];
		}elseif(isset($this->default)){
			return $this->default;
		}
		return $globalDefaultValue;
	}

	function isModel(){
		return false;
	}
}