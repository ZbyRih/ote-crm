<?php


class FormButton{

	const CREATE = 'create';

	const SAVE = 'save';

	const CANCEL = 'cancel';

	const T_SUBMIT = 'submit';

	const T_CANCEL = 'cancel';

	static $defNames = [
		self::CREATE => 'Vytvořit',
		self::SAVE => 'Uložit',
		self::CANCEL => 'Zrušit'
	];

	static $defSubmit = [
		self::CREATE,
		self::SAVE
	];

	static $defCancel = [
		self::CANCEL
	];

	var $cancel = false;

	var $submit = false;

	var $action;

	var $name;
}

class FormButtonsClass{

	var $items = [];

	var $submit = NULL;

	// akce submitu
	var $cancel = NULL;

	// akce zruseni
	private $disable = false;

	public function __construct(){
		$this->create(MArray::AllwaysArray(func_get_args()));
	}

	public function createDefault(){
		if(!$this->disable){
			$this->create([
				FormButton::CREATE,
				FormButton::CANCEL
			]);
		}
	}

	public function create($actions){
		foreach($actions as $v){
			if(in_array($v, FormButton::$defSubmit)){
				$this->addSubmit($v);
			}else if(in_array($v, FormButton::$defCancel)){
				$this->addCancel($v);
			}else{
				$this->add($v);
			}
		}
	}

	public function isInit(){
		return (!empty($this->items));
	}

	public function setType($action, $type){
		if($type == FormButton::T_SUBMIT){
			$this->submit = $action;
		}else if($type == FormButton::T_CANCEL){
			$this->cancel = $action;
		}
	}

	public function getSubmit(){
		foreach($this->items as $key => $value){
			if(OBE_Http::getPost($key) == $value){
				OBE_Http::dropPost($key);
				return $key;
			}
		}
		return false;
	}

	public function isSubmit($action){
		return ($this->submit === $action);
	}

	public function isCancel($action){
		return ($this->cancel === $action);
	}

	public function addSubmit($action, $name = NULL){
		$this->add($action, $name);
		if($this->submit && $this->submit != $action){
			$this->del($this->submit);
		}
		$this->submit = $action;

		$this->moveToBegining($this->submit);

		return $this;
	}

	public function addCancel($action, $name = NULL){
		$this->add($action, $name);
		if($this->cancel && $this->cancel != $action){
			$this->del($this->cancel);
		}
		$this->cancel = $action;

		return $this;
	}

	public function moveToBegining($action){
		$this->items = [
			$action => $this->items[$action]
		] + $this->items;
	}

	public function add($action, $name){
		$this->items[$action] = ($name) ? $name : FormButton::$defNames[$action];
	}

	public function setName($action, $name){
		if(isset($this->items[$action])){
			$this->items[$action] = $name;
		}
	}

	public function del($action){
		unset($this->items[$action]);
	}

	public function clear(){
		$this->items = [];
		$this->disable = false;

		return $this;
	}

	/**
	 * return array - [action] = title
	 */
	public function get(){
		return $this->items;
	}

	public function disable(){
		$this->disable = true;
	}
}