<?php
class SelectContentFieldClass extends FormFieldClass{

	public $crmType = NULL;
	public $crmId = NULL;
	public $entityId = NULL;

	public $selectModuleId = NULL;

	function initSelect($recordId, $name, $type = NULL, $descLev1 = ''){
		$this->setValue([
			  'desc' => $descLev1
			, 'id' => $recordId
			, 'name' => $name
			, 'type' => $type
			, 'cont' => [
				  'moduleid' => NULL
				, 'formodule' => $this->parent->scope->module
				, 'desc' => ''
				, 'callback' => NULL
			]
		]);
		return $this;
	}

	function initSelect2($selModuleId, $descLev2 = '', $jsCallback = NULL){
		$this->data['value']['cont'] = [
			  'moduleid' => AdminApp::$modules->getModuleName($selModuleId)
			, 'formodule' => $this->parent->scope->module
			, 'desc' => $descLev2
			, 'callback' => $jsCallback
		];
		$this->selectModuleId = AdminApp::$modules->getModuleName($selModuleId);
		return $this;
	}

	function setFromModule($moduleID){
		$this->data['value']['cont']['formodule'] = NULL;
		$this->data['value']['cont']['frommodule'] = AdminApp::$modules->getModuleName($moduleID);
	}

	/**
	 *
	 * @param FormFieldClass $field
	 */
	function handleAccessPre(){
		$this->data['request_link'] = '?' . k_ajax . '=select&' . k_module . '=' . $this->selectModuleId . '&';
		if(isset($this->data['value']['cont']['formodule'])){
			$this->data['request_link'] .= k_formodule . '=' . $this->data['value']['cont']['formodule'];
		}else if(isset($this->data['value']['cont']['frommodule'])){
			$this->data['request_link'] .= 'frommodule=' . $this->data['value']['cont']['frommodule'];
		}
	}

	/**
	 *
	 * @param FormFieldClass $field
	 */
	function handleAccessPost(){

	}

	function callOut(){
		$this->handlePostAdditionalData();
		parent::callOut();
	}

	function handlePostAdditionalData(){
		$typeKey = $this->key . '_type';
		$crmId = NULL;
		$crmType = NULL;

		if(OBE_Http::issetPost($this->key) && !OBE_Http::emptyPost($typeKey)){
			$crmType = OBE_Http::getPost($typeKey);
			$crmId = OBE_Http::getPost($this->key);
		}

		if($this->parent->scope->isSetRecId() && $crmType !== NULL){

			$entityId = NULL;

			if($crmType == MODULES::MENU){

				$crmType = 'm';

			}elseif($crmType != 'a'){
				if(is_numeric($crmType)){
					$modul = AdminApp::$modules->getModuleById($crmType);
					$crmType = $modul['crmch'];
				}else{
					$modul = AdminApp::$modules->getModuleByChar($crmType);
				}
				if($modul){

					if($crmId === ''){
						$crmId = NULL;
					}

					if($modul['haveentity']){
						$entityId = $crmId;
						$crmId = NULL;
					}
				}else{
					return;
				}
			}

			$this->crmType = $crmType;
			$this->crmId = $crmId;
			$this->entityId = $entityId;
		}
	}
}