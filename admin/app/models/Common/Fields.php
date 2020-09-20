<?php

class MFields extends AdminJsonClass{

	private static $instance = null;

	public function __construct($input = []){
		if(self::$instance){
			throw new ErrorException('MFields instance již existuje');
		}

		self::$instance = $this;

		$this->decode('fields');

		$fields = $this->_data;
		$fields = MArray::MapItemToKey($fields['modules'], 'module');

		parent::__construct($fields);
	}

	public static function getInstance(){
		if(!self::$instance){
			new MFields();
		}
		return self::$instance;
	}

	public function getModuleMainForm($module){
		return $this->getModuleForm($module, 'main');
	}

	public function getModuleForm($module, $form){
		if(isset($this[$module]) && isset($this[$module]['forms'][$form])){
			return MArray::FilterMArray($this[$module]['forms'][$form], 'active', true);
		}
		return [];
	}
}

class MUserRights extends OBE_Array{

	public function __construct($input = []){
		parent::__construct(AdminUserClass::$logedUser['rights']);
	}

	public function getModuleRightsAccess($module){
		$this[$module]['access'];
	}

	public function setModule($module, $access){
		$this[$module]['access'] = $access;
	}

	public function getFieldsAccess($module, $active = true){
		if($active){
			return MArray::FilterMArray($this[$module]['fileds'], 'visible', 1);
		}else{
			MArray::FilterMArray($this[$module]['fileds']);
		}
	}

	public function setFields($module, $fields){
		$this[$module]['fileds'] = $fields;
	}
}