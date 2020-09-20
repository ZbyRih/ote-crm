<?php

define('_KEY_SET_GROUP_SOURCE', 'g_src');
define('_KEY_SET_PRESERVEMODELS', 'unset');
define('_KEY_SET_NAME_FOR_GROUP', 'gname');
define('_KEY_SET_APPEND', 'append');

class GroupModelListConfigClass extends ModelListConfigClass{

}

class ModelGroupListClass extends ModelListClass{

	/**
	 *
	 * @var ListFieldConfig
	 */
	var $grouppedBySrc = null;

	/**
	 *
	 * @var ModelFieldsClass
	 */
	var $groupNames = null;

	var $groupAddsCallback = null;

	var $groupAddsCallbackParam = null;

	var $groupIds = [];

	public function __construct($type = null){
		parent::__construct('glist');
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param string $form
	 */
	public function attach($info, $form){
		$this->config = new GroupModelListConfigClass();
		$this->modelInit($info, $form);
		return $this;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see ListClass::InitializeTA()
	 */
	function configByArray($configArray = [], $bInit = true){
		parent::configByArray($configArray, $bInit);
		if(isset($configArray['grouppedBy'])){
			if(isset($this->config->grouppedBy[_KEY_SET_GROUP_SOURCE])){
				$this->grouppedBySrc = new ListFieldConfig($this->model->name, $configArray['grouppedBy'][_KEY_SET_GROUP_SOURCE]);
			}
			if(isset($this->config->grouppedBy[_KEY_SET_NAME_FOR_GROUP])){
				$this->groupNames = new ModelFieldsClass($this->model->name, $this->config->grouppedBy[_KEY_SET_NAME_FOR_GROUP]);
			}
			if(isset($this->config->grouppedBy[_KEY_SET_APPEND])){
				$this->groupAddsCallback = $this->config->grouppedBy[_KEY_SET_APPEND][0];
				$this->groupAddsCallbackParam = $this->config->grouppedBy[_KEY_SET_APPEND][1];
			}
		}
	}

	function getCols(){
		$cols = parent::getCols();
		$cols[] = $this->grouppedBySrc->getModelAndRow();
		return $cols;
	}

	function _orderByCombine(){
		$order = parent::_orderByCombine();

		if($this->grouppedBySrc != null && $this->grouppedBySrc->is_Set()){
			$groupByField = $this->grouppedBySrc->getOriginalAsString();
			array_unshift($order, $groupByField);
		}
		return $order;
	}

	/**
	 * projde vsechny radky vysledku a mapuje radek dat na radek listu
	 * @param Array $data - data z vysledku volani modelu
	 * @return Array - data pro list
	 */
	function _processData($data){
		/**
		 * v grupped budou uvedeny tabulky jez sou vijmuty z vysledku, vysledek se pak preda k zpracovani na radky
		 */
		$listData = [];
		if($this->config->grouppedBy){
			if($this->grouppedBySrc != null && $this->grouppedBySrc->is_Set()){
				list($gModelName, $gGroupKey) = $this->grouppedBySrc->getModelAndRowAsArray();
			}
		}else{
			$listLinearData = parent::_processData($data);
			return [
				0 => $this->getNewGroup(0, null, $listLinearData)
			];
		}

		if(!MArray::isNumericKey($data)){
			$data = [
				$data
			];
		}

		/* key je vzdycky cislo */
		foreach($data as $key => $item){
			if(isset($item[$gModelName]) && array_key_exists($gGroupKey, $item[$gModelName])){
				$groupKey = $item[$gModelName][$gGroupKey];
				if(!isset($listData[$groupKey])){
					$newListData = $this->getNewGroup($groupKey, $item, []);
					$newListData = $this->addCallbackAdds($groupKey, $newListData, $item);
					$listData[$groupKey] = $newListData;
				}
				$item = $this->_getCleanItem($item);
				$listData[$groupKey]['rows'] += $this->_getRows($item);
			}
		}
		return $listData;
	}

	function addCallbackAdds($groupKey, $listData, $item){
		$listData['adds'] = null;
		if(is_callable($this->groupAddsCallback)){
			$params = [];
			if(!is_null($this->groupAddsCallbackParam)){
				$params = MArray::AllwaysArray($this->groupAddsCallbackParam);
			}
			array_unshift($params, $item);
			array_unshift($params, $groupKey);
			$listData['adds'] = call_user_func_array($this->groupAddsCallback, $params);
		}
		return $listData;
	}

	function getNewGroup($groupId, $item, $data){
		$this->groupIds[] = $groupId;
		return [
			'name' => $this->_getGroupFullName($item),
			'rows' => $data
		];
	}

	function GetGroupIds(){
		return $this->groupIds;
	}

	function _getRows($item){
		if(!MArray::isNumericKey($item) && !empty($item)){
			$item = [
				$item
			];
		}
		$rows = parent::_processData($item);
		return $rows;
	}

	function _getCleanItem($item){
		if(isset($this->config->grouppedBy[_KEY_SET_PRESERVEMODELS])){
			foreach($this->config->grouppedBy[_KEY_SET_PRESERVEMODELS] as $keys){
				unset($item[$keys]);
			}
		}
		return $item;
	}

	function _getGroupFullName($item){
		if($this->groupNames->is_Set()){
			$name = [];
			if($fields = $this->groupNames->getFieldsList()){
				foreach($fields as $field){
					$name[] = $field->getItemValue($item);
				}
				return implode(' - ', $name);
			}
		}
		return 'null';
	}

	function _prepareListRows(){
		$listRows = parent::_prepareListRows();
		if($this->grouppedBySrc != null && $this->grouppedBySrc->is_Set()){
			$listRows = MArray::MergeMultiArray($listRows, $this->grouppedBySrc->getModelWithRows());
		}
		return $listRows;
	}

	/**
	 * (non-PHPdoc)
	 * @see ViewElementClass::getElementView()
	 */
	public function getElementView(){
		return $this->createList();
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param ModelListClass $list
	 * @return Boolean
	 */
	function __listItemMoveTo($info, $list){
		if(OBE_Http::issetGet('to')){
			$listCtrl = $this->_createListControl();
			$indexTo = OBE_Http::getGet('to');
			if($this->grouppedBySrc !== null && $this->grouppedBySrc->is_Set()){
				$model = clone $this->model;
				$model->associatedModels = [];
				/* korekce indexu pretazeni */
				if($counts = $model->Count($this->grouppedBySrc->getModelWithRows(), [], $this->grouppedBySrc->getModelWithRows())){
					if($orgItem = $this->getItem($recordId)){
						$orgGroupId = $orgItem[$this->grouppedBySrc->model][$this->grouppedBySrc->row];
						$newGroupId = null;
						$gnOffset = 0;
						$offset = 1;
						foreach($counts as $group){
							if($group[$this->grouppedBySrc->row] == $orgGroupId){
								$group['num']--;
							}
							$gnOffset++;
							if($indexTo >= $offset && $indexTo <= ($offset + $group['num'])){
								$newGroupId = $group[$this->grouppedBySrc->row];
								break;
							}
							$offset += $group['num'];
						}

						$indexTo -= $gnOffset;
						if($newGroupId !== null){
							if($orgGroupId != $newGroupId){
								$orgItem[$this->grouppedBySrc->model][$this->grouppedBySrc->row] = $newGroupId;
								$this->model->Save($orgItem, null, 0);
							}
						}
					}
				}
			}
			return $listCtrl->moveToIndex($recordId, $indexTo);
		}
	}

	function getItem($recordId){
		$fields = $this->grouppedBySrc->getModelWithRows();
		$fields[$this->model->name][] = $this->model->primaryKey;
		return $this->model->FindOneById($recordId, $fields);
	}
}