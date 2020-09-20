<?php

class ModuleFieldsRightsClass extends ActionsModulListRightsClass{

	var $fieldsRights;
	/**
	 *
	 * @var ModuleInfoClass
	 */
	var $info = NULL;

	/**
	 * @param ModuleInfoClass $info
	 */
	function __construct($info = NULL){
		$this->info = $info;
		if($info === NULL){
			parent::__construct();
		}else{
			parent::__construct($info->access);
		}
	}

	function GetFields($form = 'main'){
		$ModuleFileds = MFields::getInstance();

		$module = $this->info->scope->module;

		if($fields = $ModuleFileds->getModuleForm($module, $form)){
			$access = AdminUserClass::getFieldsAccess($module);

			if($access && $fileds){
				$out = [];
				foreach($fields as $field){
					if(isset($access[$field['field']])){
						$field['access'] = $access[$field['field']]['access'];
						if($access[$field['field']]['visible']){
							$out[] = $field;
						}
					}else if(!isset($field['access'])){
						$field['access'] = FormFieldRights::DELETE;
						$out[] = $field;
					}
				}
				$fields = $out;
			}else{
				if($fields){
					$fields = MArray::setSubItemsIfNot($fields, 'access', FormFieldRights::DELETE);
				}
			}

			if($this->access === NULL){
				if($this->info->id){
					$selectedModule = AdminApp::$modules->getModuleById($this->info->id);
				}else{
					$selectedModule = AdminApp::$modules->getModuleById();
				}

				$this->access = $selectedModule[MModule::ACCESS];
			}

			return $this->PrepareFieldsRights($fields, $this->access);
		}
		return [];
	}

	function PrepareFieldsRights($fieldRights, $moduleRights){
		if(!empty($fieldRights)){
			foreach($fieldRights as $key => &$field){
				if($field[MModule::ACCESS] == FormFieldRights::DISABLE){
					unset($fieldRights[$key]);
				}else{
					if($field[MModule::ACCESS] > $moduleRights){
						$field[MModule::ACCESS] = $moduleRights;
					}
				}
			}
		}
		return $fieldRights;
	}

	function setFieldsRights($rights, $fields = []){
		if(empty($fields)){
			foreach($this->fieldsRights as &$field){
				$field['access'] = $rights;
			}
		}else{
			if(!is_array($fields)){
				$fields = [$fields];
			}
			foreach($fields as $field){
				list($model, $row) = ModelHelper::GetModelAndRow($field);
				foreach($this->fieldsRights as &$fr){
					if($fr['modelname'] == $model && $row == $fr['rowname']){
						$fr['access'] = $rights;
					}
				}
			}
		}
	}
}