<?php
class AjaxTriade{
	/**
	 *  id sloupce
	 *  @var Integer
	 */
	var $colId = NULL;
	/**
	 * prenasena data
	 * @var String
	 */
	var $data = NULL;
	/**
	 * primary-id
	 * @var Integer
	 */
	var $extId = NULL;

	var $modelName;
	var $rowName;

	function __construct($colId = k_colid, $data = k_data, $extId = k_extid, $type = OBE_Http::POST){
		$this->colId = OBE_Http::getByType($colId, $type);
		$this->data = OBE_Http::getByType($data, $type);
		$this->extId = OBE_Http::getByType($extId, $type);
	}

	/**
	 * @return Boolean
	 */
	function checkAjaxTriade(){
		if($this->colId !== NULL && $this->data !== NULL && $this->extId !== NULL){
			return true;
		}
		return false;
	}

	/**
	 *
	 * @param listClass $listObj
	 * @param Array $modelItem
	 * @return Array
	 */
	function setModelItem($listObj, $modelItem = []){
		if($ajaxItem = $listObj->GetAjaxItemForIndex($this->colId)){
// 			OBE_Log::varDump($ajaxItem, $this->data);
			$this->modelName = $ajaxItem['m'];
			$this->rowName = $ajaxItem['r'];
			$modelItem[$this->modelName][$this->rowName] = $this->data;
		}
		return $modelItem;
	}
}
