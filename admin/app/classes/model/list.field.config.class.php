<?php
class ListFieldConfig{
	private $_defaultModel;
	var $model = NULL;
	var $row = NULL;
	var $original = NULL;

	function __construct($defaultModelName = NULL, $source = NULL, $sourceType = ModelFieldsClass::SET_TYPE_AUTO){
		$this->model = $this->_defaultModel = $defaultModelName;
		if($source !== NULL){
			$this->set($source, $sourceType);
		}
	}

	function is_Set(){
		if(!empty($this->row)){
			return true;
		}
		return false;
	}

	function set($source, $sourceType = SET_TYPE_AUTO, $stringOriginal = NULL){
		$this->model = $this->_defaultModel;
		$this->row = NULL;
		switch($sourceType){
		case ModelFieldsClass::SET_TYPE_STRING:
			$this->setFromString($source);
			break;
		case ModelFieldsClass::SET_TYPE_PAIR_ARRAY:
			$this->setFromPairArray($source, $stringOriginal);
			break;
		case ModelFieldsClass::SET_TYPE_ASSOCIATIVE_ARRAY:
			$this->setFromAssociativeArray($source, $stringOriginal);
			break;
		case ModelFieldsClass::SET_TYPE_OLD_PAIR_FORMAT:
			$this->setFromOldFormat($source);
			break;
		default:
			$this->setAuto($source, $stringOriginal);
		}
	}

	function setAuto($source, $stringOriginal = NULL){
		if(is_string($source)){
			$this->setFromString($source);
		}elseif(is_array($source)){
			if(isset($source[_KEY_SET_MROWNAME])){
				$this->setFromOldFormat($source);
			}elseif(is_numeric(key($source))){
				$this->setFromPairArray($source, $stringOriginal);
			}else{
				$this->setFromAssociativeArray($source, $stringOriginal);
			}
		}
	}

	function setFromString($source){
		$this->setOriginal($source);
		if(ModelHelper::HaveModel($source)){
			list($model, $row) = ModelHelper::GetModelAndRow($source);
			$this->model = $model;
			$this->row = $row;
		}else{
			$this->row = $source;
		}
	}

	function setFromOldFormat($source){
		$this->setOriginal($source);
		$this->row = $source[_KEY_SET_MROWNAME];
		if(isset($source[_KEY_SET_MNAME])){
			$this->model = $source[_KEY_SET_MNAME];
		}
	}

	function setFromAssociativeArray($source, $stringOriginal = NULL){
		if($stringOriginal !== NULL){
			$this->setOriginal($stringOriginal);
		}else{
			$this->setOriginal($source);
		}
		if($model = array_keys($source)){
			$firstKey = reset($model);
			if(is_string($firstKey)){
				$this->model = $firstKey;
			}
		}
		$this->row = reset($source);
	}

	function setFromPairArray($source, $stringOriginal = NULL){
		if($stringOriginal !== NULL){
			$this->setOriginal($stringOriginal);
		}else{
			$this->setOriginal($source);
		}
		if(sizeof($source) > 1){
			$this->model = array_shift($source);
		}
		$this->row = array_shift($source);
	}

	function setOriginal($source){
		$this->original = $source;
	}

	function getModelWithRow(){
		return [$this->model => $this->row];
	}

	function getModelWithRows(){
		return [$this->model => [$this->row]];
	}

	function getModelAndRow(){
		return $this->model . '.' . $this->row;
	}

	function getModelAndRowAsArray(){
		return [$this->model, $this->row];
	}

	function getAsociateArray(){
		return [_KEY_SET_MNAME => $this->model, _KEY_SET_MROWNAME => $this->row];
	}

	function setItemInModelArray($modelArray, $value){
		if(isset($modelArray[$this->model])){
			$modelArray[$this->model][$this->row] = $value;
			return $modelArray;
		}
		return NULL;
	}

	function setItemAndGetSingleHerModel($modelArray, $value){
		$modelArray = $this->getOneModelItemArray($modelArray);
		return $this->setItemInModelArray($modelArray, $value);
	}

	function getOneModelItemArray($modelArray){
		if(isset($modelArray[$this->model])){
			return [$this->model => $modelArray[$this->model]];
		}
		return NULL;
	}

	function getItemValue($modelArray){
		if($this->model){
			if(isset($modelArray[$this->model]) && isset($modelArray[$this->model][$this->row])){
				return $modelArray[$this->model][$this->row];
			}
		}elseif($this->row && isset($modelArray[$this->row])){
			return $modelArray[$this->row];
		}
		return NULL;
	}

	function getOriginalAsString(){
		if(is_string($this->original)){
			return $this->original;
		}else{
			return $this->model . '.' . $this->row;
		}
	}
}