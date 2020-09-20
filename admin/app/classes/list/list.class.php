<?php

class ListClass extends ViewElementClass{

	const ON_PREFINDALL = 'preFindAllCallback';

	const ON_DATAPROCCESS = 'dataCallback';

	static $actionsCallbacks = [
		ListAction::SELECT => '__listItemSelect',
		ListAction::VISIBLE => '__listItemShow',
		ListAction::HIDE => '__listItemHide',
		ListAction::EDIT => '__listItemEdit',
		ListAction::DELETE => '__listItemDelete',
		ListAction::MOVE_UP => '__listItemUp',
		ListAction::MOVE_DOWN => '__listItemDown',
		ListAction::MOVE_TO => '__listItemMoveTo',

		ListAction::M_HIDE => '__listItemsHide',
		ListAction::M_VISIBLE => '__listItemsShow',
		ListAction::M_DELETE => '__listItemsDelete'
	];

	static $logPopis = [
		ListAction::SELECT => '',
		ListAction::VISIBLE => 'Upraveno',
		ListAction::HIDE => 'Upraveno',
		ListAction::EDIT => '',
		ListAction::DELETE => 'Smazáno',
		ListAction::MOVE_UP => 'Upraveno',
		ListAction::MOVE_DOWN => 'Upraveno',
		ListAction::MOVE_TO => 'Upraveno',

		ListAction::M_HIDE => 'Upraveno',
		ListAction::M_VISIBLE => 'Upraveno',
		ListAction::M_DELETE => 'Smazáno'
	];

	/**
	 *
	 * @var ListConfigClass
	 */
	var $config = null;

	var $listName = null;

	/**
	 *
	 * @var ListActionsClass
	 */
	var $actions = null;

	/**
	 *
	 * @var ListSortClass
	 */
	var $sortObj = null;

	/**
	 *
	 * @var ActionsModulListRightsClass
	 */
	var $rightsObj = null;

	/**
	 *
	 * @var ListFieldConfig 'col/model.col'
	 */
	var $visibleSrc = null;

	var $head;

	var $sort = null;

	var $fieldMap;

	var $numTypes = [];

	var $userColCallback = [];

	var $ajaxEditItems = [];

	var $ajaxTeplateItems = [];

	var $ajaxColNameToIndex = [];

	var $fieldTplCallbackMap = [];

	var $avaibleDefaultActions = [
		ListAction::EDIT,
		ListAction::SELECT
	];

	var $uiTypeCallback = [
		FormUITypes::SELECT_ATTCH => '_imagePreview'
	];

	public $imagePreview = [];

	/**
	 *
	 * @var CallBackControlClass
	 */
	var $interCallBacks = null;

	private $_data = null;

	// []
	private static $listUID = 0;

	private $_listUID = null;

	private $useListID = false;

	/**
	 *
	 * @var ModuleInfoClass
	 */
	public $info;

	/**
	 *
	 * @var ModuleUrlScope
	 */
	public $scope;

	public $selected = [];

	/**
	 *
	 * @var integer
	 */
	private $numStart = 1;

	/**
	 *
	 * @var array
	 */
	private $errors = [];

	private $forceView = null;

	private $mode;

	protected $cfgFieldTplMap = [];

	protected $cfgActions = [];

	protected $cfgCols = [];

	public function __construct($type = 'list'){
		parent::__construct($type);
		$this->imagePreview = OBE_AppCore::getAppConfDef('img-preview', [
			'list' => [
				100,
				100
			]
		])['list'];
	}

	/**
	 *
	 * @param ModuleInfoClass $moduleInfo
	 * @param String $imageType - es/obe
	 */
	public function attach($info){
		if(!($info instanceof ModuleInfoClass)){
			throw new OBE_Exception('Předaný parametr není ModuleInfoClass');
		}
		$this->info = $info;
		$this->scope = $info->scope;
		$this->_listUID = $info->id . ++self::$listUID;

		$this->listName = get_class($this) . '_' . $this->_listUID;

		$this->useListID = true;
		$this->scope = clone $this->scope;
		$this->scope->setStatic(k_view_elem, $this->listName);

		$this->interCallBacks = new CallBackControlClass($this);
		$this->interCallBacks->addCallBack([
			self::ON_PREFINDALL => null,
			self::ON_DATAPROCCESS => null
		]);
		return $this;
	}

	/**
	 * inicializace hodnot pro listu
	 * @param Array $configArray - array(listActions, model, listRows, forceRows, visibleSrc, positionSrc, orderBy, dataCallBack, fieldTplMap, forcePrimaryKey)
	 * @return void
	 */
	public function configByArray($configArray = [], $prepair = true){
		$this->configurate($configArray, $prepair);
		return $this;
	}

	private function configurate($configArray = [], $pripair = true){
		$this->config = $this->createConfig($configArray);
		if($pripair){
			$this->prepair();
		}
	}

	protected function createConfig($data){
		return new ListConfigClass($data);
	}

	/**
	 * Inicializace listu
	 */
	protected function prepair(){
		$this->sortObj = new ListSortClass($this, $this->config->sort);
		$this->mode = $this->config->mode;

		$this->fieldMap = [];
		$this->head = [];

		$this->cfgFieldTplMap = [];

		foreach($this->config->cols as $index => $rowDesc){
			$this->head[$index] = $rowDesc;
			$this->sort[$index] = $this->sortObj->getSortItem($index);
			$this->fieldMap[$index] = $index;

			$numType = 0;
			if(isset($this->config->numTypes[$index])){
				$numType = $this->config->numTypes[$index];
			}
			$this->numTypes[$index] = $numType;

			$this->_fieldTplMap($this->config->fieldTplMap, $index);
			$this->_ajaxFieldMap($index);
		}

		/* priprava na modifikaci poli podle $this->fieldTplMap */
		foreach($this->fieldMap as $key => $index){
			if(isset($this->config->fieldTplMap[$index])){
				$this->fieldTplCallbackMap[$index] = $this->config->fieldTplMap[$index];
			}
		}

		$this->rightsObj = new ActionsModulListRightsClass($this->info->access);

		$this->initDefaultActions();

		if($this->config->visibleSrc === null){
			$this->actions->removeAction(ListAction::VISIBLE);
			$this->actions->removeAction(ListAction::HIDE);
		}

		$this->visibleSrc = new ListFieldConfig(null, $this->config->visibleSrc, ModelFieldsClass::SET_TYPE_STRING);

		$this->handleAjax();
	}

	protected function initDefaultActions(){
		$this->cfgActions = $this->config->actions;
		if(isset($this->cfgActions[ListAction::MOVE_DOWN]) && !isset($this->cfgActions[ListAction::MOVE_TO])){
			$this->cfgActions[] = ListAction::MOVE_TO;
		}

		$this->actions = new ListActionsClass($this->rightsObj, self::$actionsCallbacks, $this);
		$this->actions->initAvaibleDefaultActions($this->cfgActions);

		$this->actions->initNonDefaultActions($this->cfgActions);

		$this->actions->setDefaultAction($this->config->defaultAction);
	}

	protected function handleAjax(){
		if($this->checkListAction()){
			$ajaxKey = $this->info->scope->module . 'Ajax';

			if(OBE_Http::issetGet($ajaxKey) && $this->config->ajaxHandle){

				$this->info->setMode(ModuleInfoClass::MODE_AJAX);

				try{
					$this->callAjax($ajaxKey);

					if(get_class($this) == 'ModelListClass'){
						$mid = $this->info->scope->recordId;
						$masterScope = $this->info->scope->getMaster();

						if($this->info->scope->parent){
							$mid = $this->info->scope->getMasterId();
						}

						$masterScope->info->activityLog('Upraveno', $this->info->name, $mid);
					}

					// logovat
					OBE_Http::respond('200 OK');
				}catch(AjaxException $e){
					OBE_Http::respond('500 Internal Server Error', $e->getMessage());
				}catch(OBE_Exception $e){
					OBE_Http::respond('500 Internal Server Error', $e->name);
				}
			}
		}
	}

	private function callAjax($ajaxKey){
		call_user_func_array($this->config->ajaxHandle, [
			OBE_Http::getGet('ajax_field'),
			OBE_Http::getGet($ajaxKey),
			$this
		]);
	}

	/**
	 * (non-PHPdoc)
	 * @see ViewElementClass::getElementView()
	 */
	public function getElementView(){
		return $this->build();
	}

	protected function build($data = null){
		AttachmentCtrlClass2::$self->setPreviewSize(OBE_AppCore::getAppConf('list_preview_size'));

		if($data === null){
			if($this->_data !== null){
				$data = $this->_data;
			}else{
				throw new OBE_Exception('Nema data ke zpracovani (data jsou NULL)');
			}
		}
		$this->interCallBacks->runCallBackParams(self::ON_PREFINDALL, [
			$this
		]);

		if($this->config->postProccess){
			$data = self::_processData($data);
		}

		$this->data = $this->toArray($data);

		return $this;
	}

	/**
	 * Vytvori pole pro sablonu
	 * @param array $data - data pro seznam
	 * @return Array - pole pro sablonu
	 */
	protected function toArray($data){
		return [
			'LIST' => [
				'data' => $data,
				'scope' => $this->scope,
				'listUID' => $this->getRealUID(),
				'mode' => $this->mode,
				'caption' => $this->config->caption,
				'header' => $this->head,
				'sort' => $this->sort,
				'actions' => $this->actions->getForSmarty(),
				'headSize' => $this->actions->updateSizeHead(sizeof($this->head)),
				'numTypes' => $this->numTypes,
				'tplToField' => $this->cfgFieldTplMap, //$this->config->fieldTplMap,
				'ajaxEditItems' => $this->ajaxEditItems,
				'ajaxTemplItems' => $this->ajaxTeplateItems,
				'ajaxResetView' => $this->config->ajaxResetView,
				'numbered' => $this->config->numbered,
				'numStart' => $this->numStart,
				'errors' => $this->errors,
				'static' => $this->config->static,
				'sorting' => $this->config->sorting,
				'headInfo' => $this->config->headInfo,
				'selected' => $this->selected
			]
		];
	}

	public function setMode($mode){
		$this->mode = $mode;
	}

	public function getRealUID(){
		$view = ($this->forceView) ? $this->forceView : (($this->info->control->isDefaultView()) ? '' : $this->info->scope->getView());

		$portableName = 'v' . $view . 'm' . $this->_listUID;

		return $portableName;
	}

	private function _fieldTplMap($fieldMap, $index){
		$defVal = null;

		if(isset($fieldMap[$index]) && ($tpl = $this->_getListTpl($fieldMap[$index]))){
			$defVal = $tpl;
		}
		$this->cfgFieldTplMap[$index] = $defVal;
	}

	private function _ajaxFieldMap($index){
		$defVal = null;
		$templVal = null;
		if(isset($this->config->ajaxRowsEdit[$index])){
			$type = $this->config->ajaxRowsEdit[$index];
			$defVal = $templVal = [
				'key' => 'ajax_f' . $index,
				'data' => null
			];
			$defVal['tpl'] = $this->_getListTpl($type, FormUITypes::TPL_FOR_AJAX);
			$templVal['tpl'] = $this->_getListTpl($type, FormUITypes::TPL_FOR_FORM);
		}
		$this->ajaxEditItems[$index] = $defVal;
		$this->ajaxTeplateItems[$index] = $templVal;
	}

	protected function _getListTpl($uiType, $type = FormUITypes::TPL_FOR_LIST){
		if(!(is_numeric($uiType) && ($tpl = FormUITypes::GetTPL($uiType, $type)))){
			if($type == FormUITypes::TPL_FOR_AJAX){
				return null;
			}
			$tpl = $uiType;
		}
		return $tpl;
	}

	public function getRealColName($col){
		return $col;
	}

	public function getAjaxItem($index){
		if(isset($this->ajaxTeplateItems[$index]) && $this->ajaxEditItems[$index] !== null){
			return $this->ajaxTeplateItems[$index];
		}
		return null;
	}

	public function setAjaxItem($index, $item){
		$this->ajaxTeplateItems[$index] = $item;
	}

	/**
	 * projde vsechny radky vysledku a mapuje radek dat na radek listu
	 * @param Array $data - data z vysledku volani modelu
	 * @return Array - data pro list
	 */
	protected function _processData($data){
		$listData = [];

		if(!empty($data)){
			foreach($data as $key => $item){
				if(!is_array($item)){
					$item = [
						$item
					];
				}
				$newData['data'] = $this->_getMapedField($item);
				$newData['spec'] = null;

				$listData[$key] = $this->_dataProcessCallBack($newData, $item);
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
		if($this->visibleSrc->is_Set()){
			if($spec = $this->visibleSrc->getItemValue($orgItem)){
				$item['spec'] = $spec;
			}
		}

		if($this->interCallBacks->isCallable(self::ON_DATAPROCCESS)){
			$item = $this->interCallBacks->runCallBackParams(self::ON_DATAPROCCESS, [
				$item,
				$orgItem,
				$this
			]);
		}
		return $item;
	}

	/**
	 * namapovani radku vysledku na radek listu
	 * @param Array $item - array('modelName' => array(row => val, ...), ...)
	 * @return Array - radek listu
	 */
	protected function _getMapedField($item){
		foreach($this->fieldTplCallbackMap as $key => $uitype){
			if(isset($this->uiTypeCallback[$uitype])){
				$item[$key] = $this->{$this->uiTypeCallback[$uitype]}($item[$key]);
			}
		}
		foreach($this->userColCallback as $key => $callBack){
			$item = call_user_func($callBack, $item, $this);
		}

		$row = [];
		foreach($this->config->valuesSubstitute as $index => $list){
			if(array_key_exists($index, $item) && array_key_exists($item[$index], $list)){
				$item[$index] = $list[$item[$index]];
			}else{
				$item[$index] = ''; // 'undefined';
			}
		}

		foreach($this->cfgFieldTplMap as $index => $tpl){

			if(isset($item[$index])){
				$row[$index] = $item[$index];
			}else{
				$row[$index] = null;
			}
		}
		return $row;
	}

	public function handleActions($bIgnoreRecordID = false){
		$action_key = $this->info->scope->getActionKey();
		$mIds = null;

		if(OBE_Http::issetGet($action_key) && $this->checkListAction()){

			if($this->info->scope->isSetRecId() && $this->info->scope->isEmptyRecId() && !$bIgnoreRecordID){
				return false;
			}

			try{
				OBE_App::$db->startTransaction();

				$result = $this->actions->catchByGet($action_key, [
					$this->info,
					$this
				]);

				OBE_App::$db->finishTransaction();
// 				OBE_App::$db->finishTransaction(!OBE_App::$db->anyError());

				// logovani
				if(get_class($this) == 'ModelListClass'){
					$a = OBE_Http::getGet($action_key);
					if(isset(self::$logPopis[$a]) && !empty(self::$logPopis[$a])){

						$mid = null;
						$masterScope = $this->info->scope->getMaster();

						if($this->info->scope->parent){
							$mid = $this->info->scope->getMasterId();
						}

						if(!$mIds && $this->info->scope->recordId){
							$mIds[] = $this->info->scope->recordId;
						}
						if(!empty($mIds)){
							foreach($mIds as $i){
								$masterScope->info->activityLog(self::$logPopis[$a], $this->info->name, ($mid) ? $mid : $i);
							}
						}
					}
				}
			}catch(ModelSaveException $e){
				$this->addErr($e->getErrors());
				return false;
			}
			return $result;
		}
		return false;
	}

	public function checkListAction(){
		$action_key = $this->info->scope->getActionKey();
		$mIds = null;

		if(OBE_Http::issetGet($action_key)){
			if(OBE_Http::issetGet(k_mIds)){
				$mIds = explode(',', OBE_Http::getGet(k_mIds));
				$this->info->scope->setCarry(k_mIds, $mIds);
			}
		}

		if($this->useListID && !(OBE_Http::isGetIs(k_view_elem, $this->listName))){
			return false;
		}

		return true;
	}

	public function setActionCallBacks($callback, $action = null){
		if($action == null && is_array($callback)){
			foreach($callback as $key => $item){
				$this->actions->setCallBack($key, $item);
			}
		}else{
			$this->actions->setCallBack($action, $callback);
		}
	}

	public function setColCallBack($index, $callBack, $f = null){
		$revMap = array_reverse($this->fieldMap);
		$this->userColCallback[$revMap[$index]] = $callBack;
	}

	private function _imagePreview($item){
		AdminApp::PageForceReload();
		return AttachmentCtrlClass2::$self->getView($item, $this->imagePreview);
	}

	public function setAppCallBack($key, $callBack = null){
		$this->interCallBacks->setCallBack($key, $callBack);
	}

	public function setData($data){
		$this->_data = $data;
	}

	public function setForceView($view){
		$this->forceView = $view;
	}

	public function disableActions(){
		$this->actions->disableAllActions();
	}

	public function disableAjaxEdit(){
		$this->ajaxEditItems = [];
		$this->ajaxColNameToIndex = [];
		$this->ajaxTeplateItems = [];
		$this->config->ajaxRowsEdit = [];
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 */
	public static function getActionIds($info){
		$ids = $info->scope->getCarry(k_mIds);
		if(!$ids && $info->scope->isSetRecId()){
			$ids = [
				$info->scope->recordId
			];
		}
		return $ids;
	}

	function addErr($error){
		$this->errors[] = $error;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param ListClass $list
	 * @return Boolean
	 */
	function __listItemSelect($info, $list){
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param ListClass $list
	 * @return Boolean
	 */
	function __listItemShow($info, $list){
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param ListClass $list
	 * @return Boolean
	 */
	function __listItemHide($info, $list){
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param ListClass $list
	 * @return Boolean
	 */
	function __listItemEdit($info, $list){
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param ListClass $list
	 * @return Boolean
	 */
	function __listItemDelete($info, $list){
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param ListClass $list
	 * @return Boolean
	 */
	function __listItemUp($info, $list){
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param ListClass $list
	 * @return Boolean
	 */
	function __listItemDown($info, $list){
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param ListClass $list
	 * @return Boolean
	 */
	function __listItemMoveTo($info, $list){
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param ListClass $list
	 * @return Boolean
	 */
	function __listItemsHide($info, $list){
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param ListClass $list
	 * @return Boolean
	 */
	function __listItemsShow($info, $list){
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param ListClass $list
	 * @return Boolean
	 */
	function __listItemsDelete($info, $list){
	}
}