<?php


class MUserRightsCtrl{

	private $rights;

	private $data;

	private $obj = NULL;

	public function __construct($userId = NULL){
		$this->obj = new MUser();
		if($userId){
			$this->setData($this->obj->FindOneById($userId));
			if(!$this->data){
				throw new OBE_Exception('MUserRightsCtrl nenačetl záznam id: ' . $userId);
			}
		}
	}

	public function setData($data){
		$this->data = $data;
		$this->rights = unserialize($this->data['User']['rights']);
	}

	public function getRights(){
		return $this->rights;
	}

	public function setRights($rights){
		$this->rights = $rights;
	}

	/**
	 *
	 * @return array(array('file' => 'access' , 'visible'), ...)
	 */
	public function getModulesAccess(){
		if(isset($this->rights['modules'])){
			return MArray::AllwaysArray($this->rights['modules']);
		}else{
			return [];
		}
	}

	public function setModulesAccess($modulesAccess){
		$this->rights['modules'] = $modulesAccess;
	}

	public function getFieldsAccess(){
		if(isset($this->rights['fields'])){
			return MArray::AllwaysArray($this->rights['fields']);
		}else{
			return [];
		}
	}

	public function setFieldsAccess($fieldsAccess){
		$this->rights['fields'] = $fieldsAccess;
	}

	public function Save(){
		$this->data['User']['rights'] = serialize($this->rights);
		$this->obj->Save($this->data);
	}
}