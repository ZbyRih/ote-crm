<?php

class DataListClass{
	var $items = NULL;

	/**
	 *
	 * @param Array $items
	 */
	function __construct($items, $idKey = null, $parentKey = null, $nameKey = null, $nodeName = null){
		$this->items = MArray::AllwaysArray($items);
	}

	function addNullItem($keyIndex = NULL, $valIndex = NULL){
		if($keyIndex === NULL && $valIndex === NULL){
			MArray::unshift($this->items, ['NULL' => '- nevybráno -']);
		}elseif($keyIndex !== NULL && $valIndex !== NULL){
			array_unshift($this->items, [$keyIndex => 'NULL', $valIndex => '- nevybráno -']);
		}elseif($keyIndex === NULL && $valIndex !== NULL){
			MArray::unshift($this->items, ['NULL' => [$valIndex => '- nevybráno -']]);
		}
	}

	function getKeyValPairs($valIndex = NULL, $keyIndex = NULL){
		if($valIndex === NULL && $keyIndex === NULL){
			return $this->items;
		}elseif($valIndex !== NULL && $keyIndex === NULL){
			return $this->getIndexValPair($valIndex);
		}elseif($valIndex === NULL && $keyIndex !== NULL){
			return MArray::MapItemToKey($this->items, $keyIndex);
		}elseif($valIndex !== NULL && $keyIndex !== NULL){
			return MArray::MapValToKey($this->items, $keyIndex, $valIndex);
		}
	}

	function getIndexValPair($valIndex){
		$result = [];
		foreach($this->items as $index => $item){
			if(is_array($item) && isset($item[$valIndex])){
				$result[$index] = $item[$valIndex];
			}
		}
		return $result;
	}

	/**
	 *
	 * @param String $filterKey
	 * @param Mixed $filterValues - String/Array
	 */
	function getFiltered($filterKey, $filterValues){
		if(is_array($filterValues)){
			return new DataListClass($this->filterByManyVals($filterKey, $filterValues));
		}else{
			return new DataListClass(MArray::FilterMArray($this->items, $filterKey, $filterValues));
		}
	}

	private function filterByManyVals($filterKey, $filterValues){
		$ret = [];
		foreach($this->items as $key => $item){
			if(isset($item[$filterKey]) && in_array($item[$filterKey], $filterValues)){
				$ret[$key] = $item;
			}
		}
		return $ret;
	}
}