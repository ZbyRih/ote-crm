<?php
class ImportCollection{
	var $data = [];
	var $defaultId = NULL;
	var $nameKey = NULL;
	var $modelObj = NULL;

	/**
	 *
	 * @param ModelClass $modelObj
	 * @param String $nameKey
	 * @param String $idKey
	 */
	function __construct($modelObj, $idKey, $nameKey){
		$this->modelObj = $modelObj;
		$this->nameKey = $nameKey;
		$data = $modelObj->FindAll();
		$this->data = MArray::MapValToKeyFromMArray($data, $modelObj->name, $idKey, $nameKey);
		$this->defaultId = key($this->data);
	}

	function getIdByName($str){
		$str = trim($str);
		foreach($this->data as $id => $name){
			if($name == $str){
				return $id;
			}
		}
		return NULL;
	}

	function getIdByNameOrDefault($str){
		if($id = $this->getIdByName($str)){
			return $id;
		}
		return $this->getFirstId();
	}

	function getFirstId(){
		return $this->defaultId;
	}

	function isIsSet($id){
		if(isset($this->data[$id])){
			return true;
		}
		return false;
	}

	function addNew($key, $val){
		if(!empty($val)){
			$this->data[$key] = $val;
		}
	}

	function createNew($val, $saveData){
		$val = trim($val);
		$saveData[$this->modelObj->name][$this->nameKey] = $val;
		$this->modelObj->Save($saveData);
		$this->addNew($this->modelObj->id, $val);
		return $this->modelObj->id;
	}
}