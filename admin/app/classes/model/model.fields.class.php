<?php

define('_KEY_SET_MNAME', 'mName');
define('_KEY_SET_MROWNAME', 'rowName');

define('MODEL_WITH_ROWS',	true);
define('MODEL_AND_ROWS',	false);

class ModelFieldsClass{
	const SET_TYPE_AUTO = 0;
	const SET_TYPE_STRING = 1;
	const SET_TYPE_PAIR_ARRAY = 2;
	const SET_TYPE_ASSOCIATIVE_ARRAY = 3;
	const SET_TYPE_OLD_PAIR_FORMAT = 4;


	private $_defaultModelName = NULL;
	private $_fields = NULL;

	function __construct($defaultModelName, $source = NULL){
		$this->_defaultModelName = $defaultModelName;
		if($source !== NULL){
			$this->fromParamToModelAndRows($source);
		}
	}

	function is_Set(){
		if($this->_fields !== NULL){
			return true;
		}
		return false;
	}

	function _getOldParamStyle($varVal){
		if(is_string($varVal)){
			list($model, $row) = ModelHelper::GetModelAndRow($varVal);
			if(empty($model)){
				$model = $this->_defaultModelName;
			}
			return [_KEY_SET_MNAME => $model, _KEY_SET_MROWNAME => $row];
		}
		return $varVal;
	}

	function _checkOldParamStyle($sources){
		if(isset($params[_KEY_SET_MROWNAME])){
			return true;
		}
		return false;
	}

	function addFieldToList($field){
		$this->_fields[] = $field;
	}

	function addFieldFromString($source, $defModel = NULL){
		if($defModel === NULL){
			$defModel = $this->_defaultModelName;
		}
		$field = new ListFieldConfig($defModel, $source, self::SET_TYPE_STRING);
		$this->addFieldToList($field);
	}

	function addFieldFromOldFormat($source){
		$field = new ListFieldConfig($this->_defaultModelName, $source, self::SET_TYPE_OLD_PAIR_FORMAT);
		$this->addFieldToList($field);
	}

	function addFieldFromPairArray($source){
		$field = new ListFieldConfig($this->_defaultModelName, $source, self::SET_TYPE_PAIR_ARRAY);
		$this->addFieldToList($field);
	}

	function fromParamToModelAndRows($params){
		$this->_fields = NULL;
		if($this->_checkOldParamStyle($params)){
			$this->addFieldFromOldFormat($params);
		}
		if(is_array($params)){
			foreach($params as $key => $value){
				$model = $this->_defaultModelName;
				if(is_numeric($key)){
					if(is_array($value)){
						$this->_convertSubStructure($value, $key, $model);
					}else{
						$this->addFieldFromString($value);
					}
				}else{
					$this->_convertSubStructure($value, $key, $model);
				}
			}
		}else{
			$this->addFieldFromString($params);
		}
	}

	function _convertSubStructure($value, $key, $model){
		if(is_array($value)){
			$model = $key;
			foreach($value as $_key => $_value){
				if(is_numeric($_key)){
					$this->addFieldFromPairArray([$model, $_value]);
				}else{
					$this->addFieldFromString($_key . ' ' . $_value, $model);
				}
			}
		}else{
			if(in_array($value, ['ASC', 'DESC'])){
				$this->addFieldFromString($key . ' ' . $value, $model);
			}else{
				$this->addFieldFromPairArray([$key, $value]);
			}
		}
	}

	function getFieldsList(){
		return $this->_fields;
	}

	/**
	 *
	 * @return array('model' => array('row1', ..., 'rown')
	 */
	function getAsAssocArray(){
		$assocArray = [];
		if(!empty($this->_fields)){
			foreach($this->_fields as $field){
				$assocArray[$field->model][] = $field->row;

			}
		}
		return $assocArray;
	}

	/**
	 *
	 * @return array('model1.row1', , 'modeln.rown')
	 */
	function getAsStringsArray(){
		$strings = [];
		if(!empty($this->_fields)){
			foreach($this->_fields as $field){
				$strings[] = $field->getOriginalAsString();
			}
		}
		return $strings;
	}
}