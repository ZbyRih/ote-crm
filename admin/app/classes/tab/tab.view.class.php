<?php

class TabViewClass extends ViewElementClass{

	public $value = NULL;

	public $title = NULL;

	public $items = [];

	public $badges = [];

	public $inputKey = 'tab';

	private $mustSelect = true;

	private $bHandle = false;

	/**
	 *
	 * @var CallBackControlClass
	 */
	private $callBacks = [];

	public function __construct($type = NULL){
		parent::__construct('tab');
	}

	/**
	 *
	 * @param ModuleInfoClass $moduleInfo
	 * @param String $title
	 */
	public function init($moduleInfo, $title, $mustSelect = true, $inputKey = null){
		$this->title = $title;
		$this->mustSelect = $mustSelect;
		$this->inputKey = ($inputKey) ? $inputKey : $moduleInfo->id . '_' . ModuleInfoClass::$mode{0} . '_' . $this->uID;
	}

	public function setMulti($array, $parent = NULL){
		$lastKey = NULL;
		foreach($array as $key => $item){
			if(is_numeric($key) && $lastKey){
				if(!$this->callBacks){
					$this->callBacks = new CallBackControlClass($parent);
				}
				$this->callBacks->addCallBack($lastKey, $item);
			}else{
				$this->items[$key] = $item;
				$lastKey = $key;
			}
		}
	}

	public function setItems($items){
		$this->items = $items;
		return $this;
	}

	public function addItems($items){
		$this->items = array_merge($this->items, $items);
		return $this;
	}

	public function setCallBacks($callbacks, $parent = NULL){
		$this->callBacks = new CallBackControlClass($parent, $callbacks);
		return $this;
	}

	public function addCallBacks($callbacks){
		$callbacks = MArray::AllwaysArray($callbacks);
		foreach($callbacks as $key => $callBack){
			$this->callBacks->addCallBack($key, $callBack);
		}
	}

	public function handleValue(){
		$val = NULL;
		$val = OBE_Http::getGet($this->inputKey);

		if($val || $val === '0'){
			$this->value = $val;
		}elseif(OBE_Session::exists($this->inputKey)){
			$this->value = $val = OBE_Session::read($this->inputKey);
		}

		if(!isset($this->items[$val]) && !empty($this->items)){
			$val = $this->setDefault();
		}

		if($val == NULL && $this->mustSelect && !empty($this->items)){
			$this->setDefault();
		}
		$this->bHandle = true;
		return $val;
	}

	public function reset(){
		$this->setDefault();
		OBE_Session::write($this->inputKey, $this->value);
	}

	private function setDefault(){
		reset($this->items);
		return $this->value = key($this->items);
	}

	public function handleCallBacks(){
		if($val = $this->getVal()){
			$params = func_get_args();
			return $this->callBacks->runCallBackParams($val, $params);
		}
		return NULL;
	}

	/**
	 * (non-PHPdoc)
	 * @see ViewElementClass::getElementView()
	 */
	public function getElementView(){
		OBE_Session::write($this->inputKey, $this->getVal());
		return $this;
	}

	public function getVal(){
		if(!$this->bHandle){
			return $this->handleValue();
		}
		return $this->value;
	}

	public function setBadge($tab, $val, $type = 'danger'){
		$this->badges[$tab] = [
			'v' => $val,
			't' => $type
		];
	}

	public function trace(){
		__dump($this->items);
		__dump([
			$this->inputKey,
			OBE_Session::exists($this->inputKey),
			OBE_Session::read($this->inputKey)
		]);
		$this->callBacks->trace();
	}
}