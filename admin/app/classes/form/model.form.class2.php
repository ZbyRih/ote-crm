<?php

class ModelFormClass2 extends AppFormClass2{

	const CREATE = 'create';

	const EDIT = 'edit';

	var $recursionSave = 1;

	/**
	 *
	 * @var ModelClass
	 */
	var $model;

	/**
	 *
	 * @var ModelFormFieldsClass
	 */
	var $fields;

	/**
	 * pokud neni null urcuje podle ktereho sloupce se bude hledat polozka s $recordId
	 * @var String
	 */
	var $recordIdkeyName = NULL;

	public function __construct($type = NULL){
		parent::__construct('form');
	}

	/**
	 * konstruktor
	 * @param ModelCLass $modelObject - objekt modelu
	 * @param ModuleInfoClass $info
	 * @param String $form
	 * @param String $imageType - typ prikladanych obrazku
	 */
	public function initM($modelObject, $info, $form = 'main', $imageType = 'obe'){
		$this->model = $modelObject;

		$fields = $info->rights->GetFields($form);

		return parent::init($info->scope, $fields, $modelObject->name, NULL, $imageType);
	}

	function createFields($fields){
		$this->fields = new ModelFormFieldsClass($fields, $this);
	}

	/**
	 *
	 * @param boolean $viewStay nebude měnit view
	 * @param boolean $bDontRedirect nepřesměrovává
	 * @return void
	 */
	function processForm($viewStay = false, $bDontRedirect = false){
		$this->fillWithData(NULL, true);

		$mode = !$this->scope->isEmptyRecId() ? self::EDIT : self::CREATE;

		if(!$this->buttons->isInit()){
			$this->buttons->createDefault();

			if($mode == self::CREATE){
				$this->buttons->addSubmit(FormButton::CREATE);
			}else if($mode == self::EDIT){
				$this->buttons->addSubmit(FormButton::SAVE);
			}
		}

		if(!OBE_Http::isPostIs('_uid', $this->uid)){
			return null;
		}

		$ret = $this->handleFormSubmit($mode);

		if($mode == self::EDIT){ // kdyz sme v editu
			if(!$ret){ // cancel
				$this->redirectForm($bDontRedirect, true);
				return false;
			}

			// kdyz submit
			if($this->bSave){
				$this->redirectForm($bDontRedirect, true, $this->scope->recordId, (!$viewStay) ? $this->scope->view : NULL);
				return true;
			}
		}else{
			if($ret === true){
				if(!$this->scope->isEmptyRecId()){
					$this->redirectForm($bDontRedirect, true, ($viewStay) ? NULL : $this->scope->recordId, ($viewStay) ? $this->scope->view : self::EDIT);
					return true;
				}
			}else if($ret === false){ // cancel
				$this->redirectForm($bDontRedirect);
				return false;
			}
		}
		return NULL;
	}

	/**
	 * funkce zpracuje formular, odchyti jeho odeslani nebo zruseni, zvaliduje vysledek a ulozi ho
	 * @param string $mode - edit/create
	 * @return Boolean - true pri uspesnem pruchodu az na konec, false pri volbe zruseni
	 */
	function handleFormSubmit($mode = self::CREATE){
		$ret = false;
		try{
			$ret = $this->isSubmit();
			if($ret === NULL){
				$this->fillWithData(NULL, true);
				return true;
			}

			if($ret === false){
				return false;
			}
		}catch(EFormEnd $e){
			return true;
		}

		if($ret === true){
			$fromDB = false;
			if($data = $this->validate()){
				$bSave = false;

				$this->fields->fillWithData($data);

				$data = $this->handleMEntity($this->getData());

				try{
					if($this->interCallBacks->isCallable(self::ON_BEFORE_SAVE)){
						if(!($call_ret = $this->interCallBacks->runCallBackParams(self::ON_BEFORE_SAVE, [
							$data,
							$this
						]))){
							$this->fillWithData($data);
							return false;
						}
						$data = $call_ret;
					}
				}catch(ModelSaveException $e){
					$this->fillWithData($data);
					$this->addErr($e->getErrors());
					return false;
				}

				OBE_App::$db->startTransaction();

				try{
					$fromDB = true;
					if($this->recordIdkeyName === NULL){
						$bSave = $this->model->Save($data, $this->scope->recordId, $this->recursionSave);
					}else{
						$keyName = $data[$this->model->name][$this->recordIdkeyName];
						$bInsert = false;
						if($this->scope->recordId === NULL){
							$bInsert = true;
						}
						$this->model->primaryKey = $this->recordIdkeyName;
						$bSave = $this->model->Save($data, $this->scope->recordId, $this->recursionSave, $bInsert);
						$data[$this->model->name][$this->recordIdkeyName] = $keyName;
					}
				}catch(ModelSaveException $e){
					$this->addErr($e->getErrors());
				}

				if($bSave){
					if($this->recordIdkeyName !== NULL){
						$this->scope->recordId = $data[$this->model->name][$this->recordIdkeyName];
					}else{
						$this->scope->recordId = $this->model->id;
					}

					$mid = $this->scope->recordId;
					if($this->scope->parent){
						$mid = $this->scope->getMasterId();
					}
					$masterScope = $this->scope->getMaster();

					$this->bSave = true;
					if($this->model->_saveWasInsert){
						if($this->interCallBacks->isCallable(self::ON_INSERT_NEW)){
							$data = $this->interCallBacks->runCallBackParams(self::ON_INSERT_NEW, [
								$data,
								$this
							]);
						}
						$masterScope->info->activityLog('Vytvořeno', ($this->scope->parent) ? $this->scope->info->name : 'Vytvořen záznam', $mid);
					}else{
						$masterScope->info->activityLog('Upraveno', ($this->scope->parent) ? $this->scope->info->name : 'Upraven záznam', $mid);
					}

					$this->mode = self::EDIT;
					OBE_App::$db->finishTransaction(true);
					if($this->interCallBacks->isCallable(self::ON_AFTER_SAVE)){
						$this->interCallBacks->runCallBackParams(self::ON_AFTER_SAVE,
							[
								$data,
								$this,
								$this->model->_saveWasInsert
							]);
					}
					$this->fillWithData();
					return true;
				}else{
					if($es = OBE_App::$db->errors->getErrors()){
						$this->addErr('Chyba při ukládání do databáze!');
					}
					$this->addErr($es);
					OBE_App::$db->finishTransaction(false);
				}
			}
			$this->fillWithData($data, false, $fromDB);
		}
		return true;
	}

	function redirectForm($bDontRedirect = false, $callback = false, $recordId = NULL, $view = NULL){
		if($callback){
			$this->interCallBacks->runCallBackParams(self::ON_BEFORE_REDIR, [
				$recordId,
				$this
			]);
		}
		if(empty($this->errors) && !$bDontRedirect){
			if(!$view){
				$this->scope->unsetView();
			}
			$this->scope->resetViewByRedirect($recordId, $view);
		}
	}

	/**
	 * naplni formular daty
	 * @param array $data
	 * @param boolean $defaults
	 */
	function fillWithData($data = NULL, $defaults = false, $fromDB = false){
		if($data === NULL && !$this->scope->isEmptyRecId()){
			if($this->recordIdkeyName !== NULL){
				$data = $this->model->FindOneBy($this->recordIdkeyName, $this->scope->recordId);
			}else{
				$data = $this->model->FindOneById($this->scope->recordId);
			}
			$fromDB = true;
		}else{
			if(isset($data[$this->model->name]) && isset($data[$this->model->name][$this->model->primaryKey])){
				$this->scope->recordid = $data[$this->model->name][$this->model->primaryKey];
			}
		}
		parent::fillWithData($data, $defaults, $fromDB);
	}

	/**
	 * Vytvori pole pro fci dle zadanych parametru
	 * @param String $keyName - nazev klice
	 * @param Integer $uitype - typ pole
	 * @param Mixed $value - hodnota
	 * @param String $name - nazev na front pred pole
	 * @param Boolean $addToMap - pridat do mapy ???
	 * @param Closure $inCallBack - callback fce pred vracenim GetFrom
	 * @param Closure $outCallback - callback fce po proccess form
	 * @return FormFieldClass
	 */
	function createFieldM($modelName, $rowName, $uitype, $value, $name, $addToMap = false, $defaultValue = NULL, $inCallBack = NULL, $outCallback = NULL){
		return $this->fields->createFieldM($modelName, $rowName, $uitype, $value, $name, $addToMap, $defaultValue, $inCallBack, $outCallback);
	}

	/**
	 * ziskava data z formulare ve formatu modelu
	 * @return array
	 */
	function getData($data = null){
		if(empty($data)){
			if($this->scope->recordId !== NULL){
				$data = $this->model->FindOneById($this->scope->recordId);
			}else{
				$data = null;
			}
		}
		return $this->fields->getData($data, $this);
	}

	/**
	 *
	 * @param String $modelName
	 * @param String $rowName
	 * @return FormFieldClass
	 * @see AppFormClass2::getField()
	 */
	function getField($modelName, $rowName = NULL){
		return $this->fields->getField($modelName, $rowName);
	}

	/**
	 * nastavi spravne hodnoty u modelu entity v predanych datech
	 * @param array $data
	 * @return array
	 */
	function handleMEntity($data){
		if(isset($this->model->associatedModels[MEntity::HANDLER])){
			MEntity::preSet($data, AdminApp::$modules->getModuleIdByName($this->scope->module));
		}
		return $data;
	}

	function setRecursionSave($recursionDepth){
		$this->recursionSave = $recursionDepth;
	}

	function setRecordIdKeyName($recordIdKeyName){
		$this->recordIdkeyName = $recordIdKeyName;
	}

	function isSaved(){
		return $this->bSave;
	}
}