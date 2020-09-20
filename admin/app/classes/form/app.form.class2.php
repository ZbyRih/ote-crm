<?php

class EFormEnd extends \Exception{
}

class AppFormClass2 extends ViewElementClass{

	const ON_FILL = 'field_fill';

	// vola se zvlast pro kazdej field
	const ON_BEFORE_FILL = 'bef_fill';

	const ON_AFTER_FILL = 'aft_fill';

	const ON_BEFORE_SAVE = 'bef_save';

	// pred ulozenim
	const ON_AFTER_SAVE = 'aft_save';

	// pred ulozenim
	const ON_BEFORE_REDIR = 'bef_redir';

	// pred presmerovanim
	const ON_INSERT_NEW = 'insert_new';

	// po inseru
	const ON_FIELD_IN = 'callBackIn';

	const ON_FIELD_OUT = 'callBackOut';

	const FORM_NAME = 'form_name';

	/**
	 *
	 * @var ModuleUrlScope
	 */
	var $scope;

	/**
	 *
	 * @var ListActionsClass
	 */
	var $actions = null;

	/**
	 *
	 * @var FormFieldsClass
	 */
	var $fields = null;

	var $statuses = [];

	/**
	 *
	 * @var CallBackControlClass
	 */
	var $interCallBacks = null;

	/**
	 *
	 * @var FormButtonsCLass
	 */
	var $buttons = null;

	var $formName;

	var $errors;

	var $bSave = false;

	protected $uid;

	protected static $_uid = 0;

	public function __construct($type = null){
		parent::__construct('form');

		$this->uid = ++self::$_uid;

		$this->buttons = new FormButtonsClass();
	}

	/**
	 * Konstruktor
	 * @param ModuleUrlScope $scope
	 * @param string $view - pohled
	 * @param array $fields - inicializacni pole pro formular
	 */
	public function init($scope = null, $fields = [], $formName = null, $action = null, $imageType = 'obe'){
		$this->scope = $scope;
		$this->formName = $formName;

		$this->interCallBacks = new CallBackControlClass($this);
		$this->interCallBacks->addCallBack(
			[
				self::ON_FILL => null,
				self::ON_BEFORE_FILL => null,
				self::ON_BEFORE_SAVE => null,
				self::ON_AFTER_SAVE => null,
				self::ON_INSERT_NEW => null,
				self::ON_BEFORE_REDIR => null
			]);

		$this->createFields($fields);

		return $this;
	}

	/**
	 * zachyceni poslani formulare s validaci
	 * @param string $mode - edit/create
	 * @return mixed (NULL - neposlano, false - zruseno, array - poslana data)
	 */
	function handleFormSubmit($mode = null){
		if(!$this->buttons->isInit()){
			$this->buttons->createDefault();
		}

// 		$this->fillWithData(null, true);

		if(!OBE_Http::isPostIs('_uid', $this->uid)){
			return null;
		}

		try{
			$ret = $this->isSubmit();
			if($ret === true){ // prisel submit
				if($data = $this->validate()){
					$this->fields->fillWithData($data); // handle access pre
					if(empty($this->errors)){
						$this->bSave = true;
					}
					return $this->getData(); // handle access post
				}
			}else if($ret === false){ // prisel cancel smazeme
				$this->fillWithData(null, true);
				return false;
			}
		}catch(EFormEnd $e){
		}
		return null;
	}

	function isSubmit(){
		if(!empty($this->formName) && OBE_Http::issetPost(self::FORM_NAME) && !OBE_Http::isPostIs(self::FORM_NAME, $this->formName)){
			throw new EFormEnd();
		}

		$action = $this->buttons->getSubmit();

		if($this->buttons->isSubmit($action)){
			return true;
		}else if($this->buttons->isCancel($action)){
			return false;
		}

		return null;
	}

	/**
	 * validuje post data do formulare
	 * @return boolean
	 */
	function validate(){
		$formDefine = $this->fields->getValidationDefinitions();
		if(empty($formDefine)){
			return true;
		}
		$obeForm = new OBE_FormClass();
		try{
			$obeForm->Validate($formDefine);
		}catch(OBE_Exception $e){
			foreach($obeForm->errorReport as $f => $es){
				foreach($es as $e){
					$this->errors[] = $this->fields->getField($f)->title . ' ' . $e;
				}
			}
		}
		return $obeForm->data;
	}

	function clear(){
		$this->fields->clearFields();
	}

	function fillWithData($data = null, $defaults = false, $fromDB = false){
		AttachmentCtrlClass2::$self->setPreviewSize(OBE_AppCore::getAppConf('form_preview_size'));

		if($this->interCallBacks->isCallable(self::ON_BEFORE_FILL)){
			$data = $this->interCallBacks->runCallBackParams(self::ON_BEFORE_FILL, [
				$data,
				$this,
				$fromDB
			]);
		}

		$this->fields->fillWithData($data, $defaults);

		if($this->interCallBacks->isCallable(self::ON_AFTER_FILL)){
			$this->interCallBacks->runCallBackParams(self::ON_AFTER_FILL, [
				$data,
				$this,
				$fromDB
			]);
		}
	}

	function handleActions($action_key = k_action, $record_key = k_record, $bIgnoreRecordID = false){
		$action_key = $this->scope->getActionKey();
		$record_key = $this->scope->getRecKey();
		if(OBE_Http::issetGet($action_key)){
			if($this->info->scope->isSetRecId() && $this->info->scope->isEmptyRecId() && !$bIgnoreRecordID){
				return false;
			}else if(OBE_Http::issetGet(k_mIds)){
				$this->info->scope->setCarry(k_mIds, explode(',', OBE_Http::getGet(k_mIds)));
			}
			return $this->actions->catchByGet($action_key, [
				$this->info
			]);
		}
		return false;
	}

	/**
	 * ziskava data z formulare klic => hodnota
	 * @return array
	 */
	function getData($data = null){
		return $this->fields->getData($data);
	}

	function createFields($fields){
		$this->fields = new FormFieldsClass($fields, $this);
	}

	/**
	 * Vytvori pole pro fci dle zadanych parametru
	 * @param String $keyName - nazev klice
	 * @param Integer $uitype - typ pole
	 * @param Mixed $value - hodnota
	 * @param String $name - nazev na front pred pole
	 * @param Boolean $addToMap - pridat do mapy ???
	 * @return FormFieldClass
	 */
	function createField($keyName, $uitype, $value, $name, $addToMap = false, $inCallback = null, $outCallback = null){
		return $this->fields->createField($keyName, $uitype, $value, $name, $addToMap, $inCallback, $outCallback);
	}

	/**
	 * prida externe vytvorene pole do formulare
	 * @param FormFieldClass $field
	 * @param Boolean $bAddToMap - default true
	 * @return Integer - klic nove pridaneho pole
	 */
	function addFieldToForm($field, $bAddToMap = true){
		return $this->fields->addFieldToForm($field, $bAddToMap);
	}

	function removeField($fieldKey){
		return $this->fields->removeField($fieldKey);
	}

	function setFieldData($keyName, $data){
		return $this->fields->setFieldData($keyName, $data);
	}

	function setUnMappedFieldData($keyName, $data){
		$this->fields->setUnMappedFieldData($keyName, $data);
	}

	function isUnMappedFieldExists($keyName){
		return $this->fields->isUnMappedFieldExists($keyName);
	}

	function isFieldExists($keyName){
		return $this->fields->isFieldExists($keyName);
	}

	function getFieldData($keyName){
		return $this->fields->getFieldData($keyName);
	}

	function getUnMappedField($keyName){
		return $this->fields->getUnMappedField($keyName);
	}

	function getUnMappedFieldData($keyName){
		return $this->fields->getUnMappedFieldData($keyName);
	}

	function getFieldValue($keyName){
		return $this->fields->getFieldValue($keyName);
	}

	function getField($keyName){
		return $this->fields->getField($keyName);
	}

	function setAppCallBack($key, $callBack = null){
		$this->interCallBacks->setCallBack($key, $callBack);
	}

	function setActions($actions){
		$actionsKeys = array_keys($actions);
		$this->actions = new ListActionsClass(null, $actions, $this);
		$this->actions->initAvaibleDefaultActions($actionsKeys);
		$this->actions->initNonDefaultActions($actionsKeys);
		$this->actions->setDefaultAction(null);
	}

	function addInfo($message){
		$this->errors[] = $message;
	}

	function addErr($error){
		if(is_array($error)){
			$this->errors = array_merge(MArray::AllwaysArray($this->errors), $error);
		}else{
			$this->errors[] = $error;
		}
	}

	function setStatuses($statusesArray){
		$this->statuses = $statusesArray;
	}

	/**
	 * (non-PHPdoc)
	 * @see ViewElementClass::getElementView()
	 */
	public function getElementView(){
		$this->data = $this->getForm()->data;
		return $this;
	}

	/**
	 * vraci pole na prirazeni do smarty
	 * @return array
	 */
	function getForm(){
		$data = [
			'EFORM' => [
				'formName' => $this->formName,
				'scope' => $this->scope,
				'_uid' => $this->uid,
				'elements' => $this->fields->getFormViewFields($this),
				'errors' => $this->errors,
				'buttons' => $this->buttons->get()
			]
		];

		if($this->actions){
			$data['EFORM']['actions'] = $this->actions->getForSmarty();
		}
		if(!empty($this->statuses)){
			$data['EFORM']['fieldStatuses'] = $this->statuses;
		}

		$view = new ViewElementClass();
		$view->type = 'form_start';
		$view->data = $data;
		return $view;
	}

	function setAccess($access){
		$this->fields->setAccess($access);
	}
}