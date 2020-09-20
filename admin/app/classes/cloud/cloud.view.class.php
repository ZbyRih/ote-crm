<?php

class CloudViewClass extends ViewElementClass{

	const actionKey = k_action;
	const recordKey = k_record;

	var $delActionCallBack = NULL;
	/**
	 *
	 * @var CallBackControlClass
	 */
	var $interCallBacks = NULL;
	/**
	 *
	 * @var String
	 */
	var $countRow = NULL;
	/**
	 *
	 * @var ListFieldConfig
	 */
	var $itemName = NULL;
	/**
	 *
	 * @var ModelClass
	 */
	var $model = NULL;

	public $list = NULL;
	public $del = ListAction::DELETE;

	public function __construct($type = NULL){
		parent::__construct('cloud');
	}
	/**
	 *
	 * @param ModelClass $model
	 * @param String $countRow
	 * @param String $itemName
	 */
	public function init($model, $countRow, $itemName){
		$this->model = $model;
		$this->countRow = new ListFieldConfig($model->name, $countRow);
		$this->itemName = new ListFieldConfig($model->name, $itemName);
		return $this;
	}

	/**
	 *
	 * @param Array $callBacks
	 * @param AppModuleClass $moduleApp
	 */
	function handleAction($callBacks, $moduleApp){
		$callBacks = new CallBackControlClass($this, $callBacks);
		if(OBE_Http::issetGet(self::recordKey)){ // TODO: predelat na dynamick
			if($callBacks->catchByGet(self::actionKey, [OBE_Http::getGet(self::recordKey)])){
				$moduleApp->scope->resetViewByRedirect();
			}
		}
	}

	function createCloud(){
		$list = [];
		if($items = $this->model->FindAll()){
			foreach($items as $index => $item){
				$list[$item[$this->model->name][$this->model->primaryKey]] = [
					  'name' => $item[$this->itemName->model][$this->itemName->row]
					, 'count' => $item[$this->countRow->model][$this->countRow->row]
				];
			}
		}
		$this->list = $list;
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see ViewElementClass::getElementView()
	 */
	public function getElementView(){
		return $this->createCloud();
	}
}