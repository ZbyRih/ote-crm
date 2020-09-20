<?php

class ModelFormFieldsClass extends FormFieldsClass{

	var $mapFieldModelRow = [];

	function __construct($fields, $formObj){
		parent::__construct($fields, $formObj);
	}

	function createFields($fields){
		foreach($fields as $key => $field){
			if(!isset($field['key'])){
				$field['key'] = $key;
			}

			$fieldClass = FromUITypesHandlersClass::getClass($field['type']);

			$this->fields[$key] = $newFieldObj = new $fieldClass($field, $this->parentForm, 'ModelFormFieldInfo');

			if($newFieldObj->info->isModel()){
				$this->mapFieldModelRow[$newFieldObj->info->model][$newFieldObj->info->col] = $newFieldObj;
			}

			$this->mapFormFields[$newFieldObj->key] = $key;
			$this->mapKeyNameFields[$newFieldObj->key] = $key;
		}
	}

	/**
	 * naplni pole daty
	 * @param array $data
	 * @param AppFormClass2 $formObj
	 */
	function fillWithData($data = NULL, $default = false){
		foreach($this->fields as $key => $field){
			$field->fillByData($data, $default);
		}
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
	 * @return FormFieldClass - pokud je addtomap = true a rowname = null vraci integer(index pridaneho fieldu)
	 */
	function createFieldM($modelName, $rowName, $uitype, $value, $name, $addToMap = false, $defaultValue = NULL, $inCallBack = NULL, $outCallBack = NULL){
		$fieldClass = FromUITypesHandlersClass::getClass($uitype);

		if($rowName){
			$newFieldObj = new $fieldClass(
				[
					'field' => $modelName . '.' . $rowName,
					'type' => $uitype,
					'value' => $value,
					'title' => $name
				], $this->parentForm, 'ModelFormFieldInfo');

			$newFieldObj->setCallBacks($inCallBack, $outCallBack);
			if($addToMap){
				$this->addFieldToForm($newFieldObj);
				$this->mapFieldModelRow[$newFieldObj->info->model][$newFieldObj->info->col] = $newFieldObj;
			}
		}else{
			$args = func_get_args();
			unset($args[1]);
			$newFieldObj = call_user_func_array([
				'parent',
				'createField'
			], $args);
		}
		return $newFieldObj;
	}

	/**
	 * ziskava data z formulare klic => hodnota
	 * @param array $data
	 * @param AppFormClass2 $formObj
	 * @return array
	 */
	function getData($data = []){
		foreach($this->fields as $field){
			if($field->info->isModel()){
				$field->callOut();
				$field->handleAccessPost();
//				if($field->access > FormFieldRights::VIEW){
				$data[$field->info->model][$field->info->col] = $field->getValue();
				//				}
			}
		}
		return $data;
	}

	function getField($modelName, $rowName = NULL){
		if($rowName){
			if(isset($this->mapFieldModelRow[$modelName]) && isset($this->mapFieldModelRow[$modelName][$rowName])){
				return $this->mapFieldModelRow[$modelName][$rowName];
			}
			return NULL;
		}
		return parent::getField($modelName);
	}
}