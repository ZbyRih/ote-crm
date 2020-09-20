<?php

class ModelDataListClass extends DataListClass{
	/**
	 *
	 * @var ModelClass
	 */
	var $model = NULL;

	/**
	 *
	 * @param ModelClass $model
	 * @param Array $items
	 * @param Array $fields
	 * @param Array $conditions
	 * @param Array $order
	 */
	function __construct($model, $items = NULL, $fields = NULL, $conditions = NULL, $order = NULL){
		if(!is_object($model)){
			$this->model = new $model();
		}else{
			$this->model = $model;
		}
		if($items === NULL){
			$items = $this->model->FindAll($conditions, $fields, $order);
		}
		parent::__construct($items);
	}

	function reduceToOneModel($modelName = NULL){
		if($modelName === NULL){
			$modelName = $this->model->name;
		}
		return new DataListClass(MArray::GetMArrayForOneModel($this->items, $modelName));
	}

	function getFiltered($filterKey, $filterValues, $modelName = NULL){
		if($modelName === NULL){
			$modelName = $this->model->name;
		}
		$filterValues = MArray::AllwaysArray($filterValues);
		$result = [];
		foreach($this->items as $key => $item){
			if(isset($item[$modelName]) && isset($item[$modelName][$filterKey])){
				if(in_array($item[$modelName][$filterKey], $filterValues)){
					$result[$key] = $item;
				}
			}
		}
		return new ModelDataListClass($this->model, $result);
	}

	function addNullItem($keyIndex = NULL, $valIndex = NULL){
		if($keyIndex !== NULL){
			array_unshift($this->items, [$this->model->name => [$this->model->primaryKey => 'NULL', $keyIndex => '- nevybráno -']]);
		}
	}

	function getItem($byValueKey, $value, $modelName = NULL){
		if($modelName === NULL){
			$modelName = $this->model->name;
		}
		foreach($this->items as $key => $item){
			if(isset($item[$modelName]) && isset($item[$modelName][$byValueKey])){
				if($item[$modelName][$byValueKey] == $value){
					return $item;
				}
			}
		}
		return NULL;
	}

	function getKeyValPairs($valIndex = NULL, $keyIndex = NULL, $modelName = NULL){
		if($modelName === NULL){
			$modelName = $this->model->name;
		}
		if($valIndex === NULL && $keyIndex === NULL){
			return $this->items;
		}elseif($valIndex !== NULL && $keyIndex === NULL){
			return $this->getIndexValPair($valIndex);
		}elseif($valIndex === NULL && $keyIndex !== NULL){
			return MArray::MapModelItemToKey($this->items, $modelName, $keyIndex);
		}elseif($valIndex !== NULL && $keyIndex !== NULL){
			return MArray::MapValToKeyFromMArray($this->items, $modelName, $keyIndex, $valIndex);
		}
	}

	function getIndexValPair($valIndex, $modelName = NULL){
		if($modelName === NULL){
			$modelName = $this->model->name;
		}
		$result = [];
		foreach($this->items as $index => $item){
			if(is_array($item) && isset($item[$modelName][$valIndex])){
				$result[$index] = $item[$modelName][$valIndex];
			}
		}
		return $result;
	}
}