<?php


class ModelListClass extends ListClass{

	/**
	 *
	 * @var ModelListConfigClass
	 */
	var $config = NULL;

	/**
	 *
	 * @var ModelClass
	 */
	var $model = NULL;

	/**
	 * manualni razeni seznamu
	 * @var ListFieldConfig - 'model.col'
	 */
	var $positionSrc = NULL;

	/**
	 *
	 * @var ListFieldConfig
	 */
	var $primaryKey = NULL;

	/**
	 *
	 * @var ModuleFieldsRightsClass
	 */
	var $rightsObj = NULL;

	/**
	 *
	 * @var ModelListFilterClass
	 */
	var $filter = NULL;

	protected function createConfig($data){
		return new ModelListConfigClass($data);
	}

	/**
	 * Inicializace hodnot pro list
	 */
	protected function prepair(){
		$this->model = $this->config->model;

		if($this->rightsObj === NULL){
			$this->rightsObj = new ModuleFieldsRightsClass($this->info, $this->config->actions);
		}

		$this->initDefaultActions();

		$this->sortObj = new ListSortClass($this, $this->config->sort);
		$this->filter = new ModelListFilterClass($this->config->filter, $this);

		$this->fieldMap = [];
		$this->head = [];
		$this->sort = [];

		$this->visibleSrc = new ListFieldConfig($this->model->name, $this->config->visibleSrc);
		$this->positionSrc = new ListFieldConfig($this->model->name, $this->config->positionSrc);
		$this->primaryKey = new ListFieldConfig($this->model->name, $this->config->primaryKey);

		$this->handleAjax();
	}

	protected function initDefaultActions(){
		parent::initDefaultActions();

		if($this->config->visibleSrc === NULL){
			$this->actions->removeAction(ListAction::VISIBLE);
			$this->actions->removeAction(ListAction::HIDE);
		}
		if($this->config->positionSrc === NULL){
			$this->actions->removeAction(ListAction::MOVE_UP);
			$this->actions->removeAction(ListAction::MOVE_DOWN);
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see ViewElementClass::getElementView()
	 */
	public function getElementView(){
		return $this->build();
	}

	/**
	 * Ziskani pole hodnot pro list
	 * @return Array - pole pro sablonu
	 */
	protected function build($data = NULL){
		if(is_object($this->model)){
			$this->createListCols();

			$this->interCallBacks->runCallBackParams(self::ON_PREFINDALL, [
				$this
			]);

			$cols = $this->getCols();
			$order = $this->getOrder();

			$this->filter->setUpModel($this->model);

			$limit = NULL;
			$page = 1;
			$tplPages = NULL;

			$conditions = [];

			if($this->config->pagination){
				$pageing = new MLPagerClass($this->model, $this->config->itemsOnPage, $cols, $conditions);
				$tplPages = $pageing->getPages();
				$page = $pageing->getPage();
				$limit = $pageing->getSize();
			}

			$order = $this->sortObj->updateOrderForModel($order);

// 			$data = MArray::RetMultiModelArray($this->model->FindAll($conditions, $cols, $order, $limit, $page));
			// TODO: zkusit na jedno zaznamu

			$data = $this->model->FindAll($conditions, $cols, $order, $limit, $page);

			if($this->config->postProccess){
				$data = $this->_processData($data);
			}

			$this->numStart = 1 + $limit * ($page - 1);

			$this->data = parent::toArray($data);

			$this->data['LIST']['pages'] = $tplPages;
			$this->data['LIST']['filter'] = $this->filter->getView();

			$this->sortObj->save();
		}
		return $this;
	}

	public function createListCols(){
		$this->cfgFieldTplMap = [];
		$this->cfgCols = $this->config->cols;

		$avaibleFields = [];
		$cFields = $this->rightsObj->GetFields($this->config->form);

		if(!empty($cFields)){
			foreach($cFields as $f){
				if(isset($f['field'])){
					list($model, $col) = explode('.', $f['field']);
					$avaibleFields[$model][$col] = $f;
				}
			}

			foreach($this->config->cols as $model => $cols){
				foreach($cols as $i => $title){
					$col = $title;
					if(!is_numeric($i)){
						$col = $i;
					}
					if(isset($avaibleFields[$model]) && isset($avaibleFields[$model][$col])){
						$field = $avaibleFields[$model][$col];
						if(is_numeric($i)){
							$title = $field['title'];
						}
						$this->addModelFieldToMap($col, $model, $title, $this->config->fieldTplMap, $this->sortObj, (isset($field['num']) ? $field['num'] : 0));
					}
				}
			}
		}

		if($this->config->spcCols){
			foreach($this->config->spcCols as $model => $fields){
				if(is_array($fields)){
					foreach($fields as $key => $field){
						$headCol = $field;
						if(!is_numeric($key)){
							$field = $key;
						}
						$numType = $this->getAvailFieldNumType($avaibleFields, $model, $field);

						$this->addModelFieldToMap($field, $model, $headCol, $this->config->fieldTplMap, $this->sortObj, $numType);
					}
				}else{
					list($model, $field) = ModelHelper::GetModelAndRow($model);
					$numType = $this->getAvailFieldNumType($avaibleFields, $model, $field);
					$this->addModelFieldToMap($field, $model, $fields, $this->config->fieldTplMap, $this->sortObj, $numType);
				}
			}
		}

		/* priprava na modifikaci poli podle $this->fieldTplMap */
		foreach($this->fieldMap as $key => $field){
			if(!empty($this->config->fieldTplMap[$key])){
				$this->fieldTplCallbackMap[$field['model']][$field['row']] = $this->config->fieldTplMap[$key];
				$this->cfgFieldTplMap[$key] = $this->_getListTpl($this->config->fieldTplMap[$key]);
			}
		}
	}

	private function getAvailFieldNumType($avaible, $model, $col){
		if(isset($avaible[$model]) && isset($avaible[$model][$col])){
			return (isset($avaible[$model][$col]['num'])) ? $avaible[$model][$col]['num'] : 0;
		}
		return 0;
	}

	/**
	 *
	 * @param string $field
	 * @param string $modelName
	 * @param string $headDesc
	 * @param array $fieldTplMap
	 * @param ListSortClass $sortObj
	 * @param integer $numsType
	 */
	private function addModelFieldToMap($field, $modelName, $headDesc, $fieldTplMap, $sortObj, $numType = 0){
		$fo = new DBField($field);
		$correctRowName = $fo->addModel($modelName);
		if($fo->hasAlias()){
			$correctRowName = $fo->getRealName();
		}
		$this->head[$correctRowName] = $headDesc;
		$this->sort[$correctRowName] = $sortObj->getSortItem($correctRowName);

		if(isset($this->config->numTypes[$modelName][$field])){
			$numType = $this->config->numTypes[$modelName][$field];
		}

		$this->numTypes[] = $numType;
		$this->_ajaxModelFieldMap($modelName, $field, $numType);
		$this->_modelFieldTplMap($fieldTplMap, $modelName, $field);
		$this->cfgCols[$modelName][] = $field;
		$this->fieldMap[] = [
			'model' => $modelName,
			'row' => $fo->getRealName()
		];
	}

	private function _ajaxModelFieldMap($modelName, $fieldName, $numType = 0){
		$defVal = NULL;
		$templVal = NULL;
		$fullName = $modelName . '_' . $fieldName;
		if($this->rightsObj->access >= FormFieldRights::EDIT){
			if(isset($this->config->ajaxRowsEdit[$modelName]) && isset($this->config->ajaxRowsEdit[$modelName][$fieldName])){
				$type = $this->config->ajaxRowsEdit[$modelName][$fieldName];
				$templVal = $defVal = [
					'key' => $fullName,
					'data' => NULL,
					'mask' => NULL,
					'num' => (($numType == 3) ? true : false)
				];
				$defVal['tpl'] = $this->_getListTpl($type, FormUITypes::TPL_FOR_AJAX);
				$templVal['tpl'] = $this->_getListTpl($type, FormUITypes::TPL_FOR_FORM);
			}
		}
		$this->ajaxEditItems[] = $defVal;
		$this->ajaxTeplateItems[] = $templVal;
		$keys = array_keys($this->ajaxEditItems);
		$this->ajaxColNameToIndex[$fullName] = end($keys);
	}

	private function _modelFieldTplMap($fieldTplMap, $modelName, $rowName){
		$defVal = NULL;
		if(isset($fieldTplMap[$modelName]) && array_key_exists($rowName, $fieldTplMap[$modelName])){
			$defVal = $fieldTplMap[$modelName][$rowName];
		}
		$this->cfgFieldTplMap = $defVal;
	}

	public function getCols(){
		$list = $this->cfgCols;
		if($this->visibleSrc->is_Set()){
			$list = MArray::MergeMultiArray($list, $this->visibleSrc->getModelWithRows());
		}
		if(!isset($list[$this->model->name]) || !in_array($this->model->primaryKey, $list[$this->model->name])){
			$list[$this->model->name][] = $this->model->primaryKey;
		}
		if($this->primaryKey->is_Set()){
			$list = MArray::MergeMultiArray($list, $this->primaryKey->getModelWithRows());
		}
		if($this->config->parentKey){
			$parentKey = new ListFieldConfig($this->model->name, $this->config->parentKey);
			$list = MArray::MergeMultiArray($list, $parentKey->getModelWithRows());
		}
		return MArray::CleanDoubledVals($list);
	}

	public function getOrder(){
		$orderBy = new ModelFieldsClass($this->model->name, $this->config->orderBy);

		if($this->positionSrc->is_Set()){
			$orderBy->addFieldToList($this->positionSrc);
		}

		return $orderBy->getAsStringsArray();
	}

	public function getRealColName($col){
		$fo = new DBField($col);
		return $fo->getRealName();
	}

	private function createFilter(){
		$filterObj = new ModelListFilterClass($this->model, $this->config->filter, $this->getRealUID());
		return $filterObj;
	}

	private function _getPrimaryKeyModelAndRowName(){
		if($this->primaryKey->is_Set()){
			return $this->primaryKey->getModelAndRowAsArray();
		}else{
			return [
				$this->model->name,
				$this->model->primaryKey
			];
		}
	}

	/**
	 * projde vsechny radky vysledku a mapuje radek dat na radek listu
	 * @param Array $data - data z vysledku volani modelu
	 * @return Array - data pro list
	 */
	protected function _processData($data){
		list($modelName, $primaryKey) = $this->_getPrimaryKeyModelAndRowName();

		$lf = null;
		$sub = null;

		$lcs = $this->config->linesColor;

		if(!empty($lcs)){

			$lf = new ListFieldConfig($this->model->name, key($lcs));
			$sub = reset($lcs);
		}

		$listData = [];

		if(empty($data)){
			return $listData;
		}

		foreach($data as $key => $item){
			if(array_key_exists($modelName, $item)){
				$id = $item[$modelName][$primaryKey];
				$newData['color'] = null;
				if($lf && $c = $lf->getItemValue($item)){
					if(isset($sub[$c])){
						$newData['color'] = $sub[$c];
					}
				}
				$newData['data'] = $this->_getMapedField($item);
				$newData['spec'] = NULL;
				$listData[$id] = $this->_dataProcessCallBack($newData, $item);
			}
		}

		return $listData;
	}

	/**
	 * odchyceni a zpracovani sloupce pro ikonu viditelnosti
	 * @param Array $item - jedna radka pro list
	 * @param Array $orgItem - jedna radka vysledku volani modelu
	 * @return Array - vraci modifikovanej radek dat pro list
	 */
	protected function _dataProcessCallBack($item, $orgItem){
		return parent::_dataProcessCallBack($item, $orgItem);
	}

	/**
	 * namapovani radku vysledku na radek listu
	 * @param Array $item - array('modelName' => array(row => val, ...), ...)
	 * @return Array - radek listu
	 */
	protected function _getMapedField($item){

		foreach($this->fieldTplCallbackMap as $model => $rows){
			foreach($rows as $row => $uitype){
				if(isset($item[$model]) && isset($this->uiTypeCallback[$uitype])){
					$item[$model][$row] = $this->{$this->uiTypeCallback[$uitype]}($item[$model][$row]);
				}
			}
		}

		foreach($this->userColCallback as $model => $rows){
			foreach($rows as $row => $fce){
				if(isset($item[$model]) && isset($item[$model][$row])){
					$item = call_user_func($fce, $item, $this);
				}
			}
		}

		foreach($this->config->valuesSubstitute as $model => $cols){
			foreach($cols as $col => $list){
				if(isset($item[$model]) && array_key_exists($col, $item[$model]) && array_key_exists($item[$model][$col], $list)){
					$item[$model][$col] = $list[$item[$model][$col]];
				}else{
					$item[$model][$col] = ''; //'undefined';
				}
			}
		}

		$row = [];
		foreach($this->fieldMap as $field){
			if(isset($item[$field['model']]) && isset($item[$field['model']][$field['row']])){
				$row[] = $item[$field['model']][$field['row']];
			}else{
				$row[] = NULL;
			}
		}

		return $row;
	}

	/**
	 *
	 * @param ModelNameKeyPair $fieldDef
	 * @param integer $to
	 */
	protected function moveToFirstColumn($fieldDef){
		if(count($this->fieldMap) > 1){
			$index = $this->getIndex($fieldDef);

			$lcfm = $this->cfgFieldTplMap[$index];
			$ati = $this->ajaxTeplateItems[$index];
			$aei = $this->ajaxEditItems[$index];
			$fm = $this->fieldMap[$index];
			$h = $this->head[$fieldDef->back()];
			$s = $this->sort[$fieldDef->back()];

			$this->removeColumn($fieldDef);

			array_unshift($this->cfgFieldTplMap, $lcfm);
			array_unshift($this->ajaxTeplateItems, $ati);
			array_unshift($this->ajaxEditItems, $aei);
			array_unshift($this->fieldMap, $fm);
			array_unshift($this->head, $h);
			array_unshift($this->sort, $s);

			$this->ajaxColNameToIndex = [];

			foreach($this->fieldMap as $i => $p){
				$this->ajaxColNameToIndex[$p['model'] . '_' . $p['row']] = $i;
			}
		}
	}

	/**
	 *
	 * @param ModelNameKeyPair $fieldDef
	 */
	private function removeColumn($fieldDef){
		$index = $this->getIndex($fieldDef);

		unset($this->cfgFieldTplMap[$index]);
		unset($this->ajaxColNameToIndex[$fieldDef->modelName . '_' . $fieldDef->rowName]);
		unset($this->ajaxTeplateItems[$index]);
		unset($this->ajaxEditItems[$index]);
		unset($this->fieldMap[$index]);
		unset($this->head[$fieldDef->back()]);
		unset($this->sort[$fieldDef->back()]);
	}

	/**
	 *
	 * @param ModelNameKeyPair $fieldDef
	 */
	private function getIndex($fieldDef){
		$index = NULL;
		foreach($this->fieldMap as $k => $item){
			if($item['model'] == $fieldDef->modelName && $item['row'] == $fieldDef->rowName){
				$index = $k;
				break;
			}
		}

		if($index === NULL){
			throw new Exception('nema index (nei ve field mapě)' . $fieldDef->modelName . '.' . $fieldDef->rowName);
		}

		return $index;
	}

	/**
	 * $model, $row, $callBack
	 * {@inheritdoc}
	 * @see ListClass::SetColCallBack()
	 */
	public function setColCallBack($index, $callBack, $f = null){
		$this->userColCallback[$index][$callBack] = $f;
	}

	/**
	 * vytvori objekt ovladani listu
	 * @return ListCtrlClass
	 */
	protected function _createListControl(){
		if($this->positionSrc->is_Set()){

			$parentKey = NULL;
			if($this->config->parentKey != NULL){
				$parent = new ListFieldConfig($this->model->name, $this->config->parentKey);
				$parentKey = $parent->row;
			}

			return new ListCtrlClass($parentKey, $this->_getPosition(), $this->model);
		}
		return NULL;
	}

	/**
	 * vrati nazev sloupce urcujiciho pozici
	 * @return String
	 */
	private function _getPosition(){
		return $this->positionSrc->row;
	}

	/**
	 *
	 * @param ListFieldConfig $field
	 * @param Mixed $value
	 * @return void
	 */
	private function saveSingleModelItem($field, $recordId, $value){
		if($item = $this->model->FindBy($this->model->primaryKey, $recordId)){
			$saveItem = $field->setItemAndGetSingleHerModel(reset($item), $value);
			return $this->model->Save($saveItem, NULL, 0);
		}
		return false;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param ModelListClass $list
	 * @return Boolean
	 */
	function __listItemShow($info, $list){
		if($this->visibleSrc->is_Set()){
			return $this->saveSingleModelItem($this->visibleSrc, $this->scope->recordId, 1);
		}
		return false;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param ModelListClass $list
	 * @return Boolean
	 */
	function __listItemHide($info, $list){
		if($this->visibleSrc->is_Set()){
			return $this->saveSingleModelItem($this->visibleSrc, $this->scope->recordId, 0);
		}
		return false;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param ModelListClass $list
	 * @return Boolean
	 */
	function __listItemDelete($info, $list){
		if(is_object($info)){
			$ids = $info->scope->getCarry(k_mIds);
			if(!$ids){
				$ids = $info->scope->recordId;
			}
		}else{
			$ids = $info;
		}

		if($this->positionSrc->is_Set()){
			if($CtrlList = $this->_createListControl()){
				return $CtrlList->Delete($ids, $this->model->conditions, 0);
			}
		}else{
			if(isset($this->model->associatedModels['MEntity'])){
				$entity = new MEntity();
				return $entity->Delete($ids);
			}else{
				return $this->model->Delete($ids, null, false);
			}
		}
		return false;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param ModelListClass $list
	 * @return Boolean
	 */
	function __listItemUp($info, $list){
		if($CtrlList = $this->_createListControl()){
			return $CtrlList->MoveUp($this->scope->recordId, $this->model->conditions);
		}
		return false;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param ModelListClass $list
	 * @return Boolean
	 */
	function __listItemDown($info, $list){
		if($CtrlList = $this->_createListControl()){
			return $CtrlList->MoveDown($this->scope->recordId, $this->model->conditions);
		}
		return false;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param ModelListClass $list
	 * @return Boolean
	 */
	function __listItemMoveTo($info, $list){
		if(OBE_Http::issetGet('to')){
			$CtrlList = $this->_createListControl();
			$CtrlList->moveToIndex($this->scope->recordId, OBE_Http::getGet('to'), $this->model->conditions);
			return true;
		}
		return false;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param ModelListClass $list
	 * @return Boolean
	 */
	function __listItemsHide($info, $list){
		$ret = true;
		$ids = $this->scope->info->getCarry(k_mIds);
		if($ids){
			if($this->visibleSrc->is_Set()){
				foreach($ids as $recordId){
					$ret &= $this->saveSingleModelItem($this->visibleSrc, $recordId, 0);
				}
				return ($ret === false) ? false : true;
			}
		}
		return false;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param ModelListClass $list
	 * @return Boolean
	 */
	function __listItemsShow($info, $list){
		$ret = true;
		$ids = $this->info->scope->getCarry(k_mIds);
		if($ids){
			if($this->visibleSrc->is_Set()){
				foreach($ids as $recordId){
					$ret &= $this->saveSingleModelItem($this->visibleSrc, $recordId, 1);
				}
				return $ret;
			}
		}
		return false;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param ModelListClass $list
	 * @return Boolean
	 */
	function __listItemsDelete($info, $list){
		if($this->info->scope->getCarry(k_mIds)){
			return $this->actions->runCallBackParams(ListAction::DELETE, [
				$info,
				$list
			]);
		}
		return false;
	}
}