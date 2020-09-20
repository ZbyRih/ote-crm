<?php

class FormFieldsClass{

	/** @var FormFieldClass[] */
	var $fields = null;

	/**
	 * mapuje interne vsechna pole
	 * @var Array
	 */
	var $mapKeyNameFields;

	/**
	 * mapuje externe pole pro formular
	 * @var Array
	 */
	var $mapFormFields;

	/**
	 *
	 * @var AppFormClass2
	 */
	var $parentForm = null;

	function __construct($fields, $formObj){
		$this->parentForm = $formObj;
		if(!empty($fields)){
			$this->createFields($fields, $formObj);
		}
	}

	function createFields($fields){
		foreach($fields as $key => $field){
			if(!isset($field['key'])){
				$field['key'] = $key;
			}

			$fieldClass = FromUITypesHandlersClass::getClass($field['type']);
			$newFieldObj = new $fieldClass($field, $this->parentForm);
			$this->fields[$key] = $newFieldObj;

			$this->mapFormFields[$newFieldObj->key] = $key;
			$this->mapKeyNameFields[$newFieldObj->key] = $key;
		}
	}

	/**
	 * naplni pole daty
	 * @param array $data
	 */
	function fillWithData($data, $default = false){
		if($this->fields){
			foreach($this->fields as $field){
				$field->fillByData($data, $default);
			}
		}
	}

	/**
	 * vraci pole poli pro prezentacni vrstvu
	 * vola na kazdem poli callIn callback, nasledne getView
	 */
	function getFormViewFields(){
		$viewFields = [];
		if(is_array($this->fields)){
			foreach($this->fields as $field){
				$field->callIn();
				$viewFields[] = $field->getView();
			}
		}
		return $viewFields;
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
		$fieldClass = FromUITypesHandlersClass::getClass($uitype);
		$field = new $fieldClass([
			'key' => $keyName,
			'type' => $uitype,
			'value' => $value,
			'title' => $name
		], $this->parentForm);
		$field->setCallBacks($inCallback, $outCallback);
		if($addToMap){
			return $this->addFieldToForm($field);
		}
		return $field;
	}

	/**
	 * prida externe vytvorene pole do formulare
	 * @param FormFieldClass $field
	 * @return Integer - klic nove pridaneho pole
	 */
	function addFieldToForm($field, $bAddToMap = true){
		$this->fields[] = $field;
		end($this->fields);
		$key = key($this->fields);
		$this->mapKeyNameFields[$field->key] = $key;
		if($bAddToMap){
			$this->mapFormFields[$field->key] = $key;
		}
		return $key;
	}

	function removeField($fieldKey){
		if(isset($this->mapKeyNameFields[$fieldKey])){
			$key = $this->mapKeyNameFields[$fieldKey];
			unset($this->mapKeyNameFields[$fieldKey]);
			unset($this->mapFormFields[$fieldKey]);
			unset($this->fields[$key]);
		}
	}

	function setFieldData($keyName, $data){
		if($this->isFieldExists($keyName)){
			$key = $this->mapFormFields[$keyName];
			$this->fields[$key]->setData($data);
			return true;
		}else{
			OBE_Trace::dump('kurva jak to?');
			exit();
			throw new Exception('FormFieldsClass::setFieldData - Neexistujici pole ' . $keyName);
		}
		return false;
	}

	function getFieldValue($keyName){
		$fieldData = $this->getFieldData($keyName);
		return $fieldData['value'];
	}

	function getFieldData($keyName){
		if($this->isFieldExists($keyName)){
			return $this->fields[$this->mapFormFields[$keyName]]->data;
		}else{
			throw new OBE_Exception('Neexistujici pole "' . $keyName . '"');
		}
		return null;
	}

	function getField($keyName){
		if($this->isFieldExists($keyName)){
			return $this->fields[$this->mapFormFields[$keyName]];
		}else{
			throw new OBE_Exception('Neexistujici pole "' . $keyName . '"');
		}
		return null;
	}

	function isFieldExists($keyName){
		if(isset($this->mapFormFields[$keyName])){
			return true;
		}
		return false;
	}

	function setUnMappedFieldData($keyName, $data){
		if(isset($this->mapKeyNameFields[$keyName])){
			$key = $this->mapKeyNameFields[$keyName];
			$this->fields[$key]->setData($data);
		}
	}

	function isUnMappedFieldExists($keyName){
		if(isset($this->mapKeyNameFields[$keyName])){
			return true;
		}
		return false;
	}

	function getUnMappedField($keyName){
		if(isset($this->mapKeyNameFields[$keyName])){
			return $this->fields[$this->mapKeyNameFields[$keyName]];
		}
	}

	function getUnMappedFieldData($keyName){
		if(isset($this->mapKeyNameFields[$keyName])){
			return $this->fields[$this->mapKeyNameFields[$keyName]]->data;
		}
	}

	function getValidationDefinitions(){
		$formDefine = [];
		if(!empty($this->mapFormFields)){
			foreach($this->mapFormFields as $key => $index){
				$field = $this->fields[$index];
				$field->preValidate();
				$formDefine[$key] = $field->getValidateDef();
			}
		}
		return $formDefine;
	}

	/**
	 * ziskava data z formulare klic => hodnota
	 * @return array
	 */
	function getData($data = []){
		if(!empty($this->fields)){
			foreach($this->fields as $field){
				$field->callOut();
				$field->handleAccessPost();
// 				if($field->access > FormFieldRights::VIEW){
				$data[$field->key] = $field->getValue();
				// 				}
			}
		}
		return $data;
	}

	function setFieldNewKeyName($oldKeyName, $newKeyName){
		if(isset($this->mapKeyNameFields[$oldKeyName])){
			$index = $this->mapKeyNameFields[$oldKeyName];
			$field = $this->fields[$index];

			$field->setKeyName($newKeyName);

			unset($this->mapKeyNameFields[$oldKeyName]);
			$this->mapKeyNameFields[$newKeyName] = $index;

			if(isset($this->mapFormFields[$oldKeyName])){
				unset($this->mapFormFields[$oldKeyName]);
				$this->mapFormFields[$newKeyName] = $index;
			}
		}
	}

	function setStatuses($fieldKey, $statusArray){
		if($this->isFieldExists($fieldKey)){
			$this->fields[$fieldKey]->setStatuses($statusArray);
		}
	}

	function prefixFieldsKeyName($prefix){
		$pref2orgmap = [];
		foreach($this->fields as $field){
			$newKey = $prefix . '_' . $field->key;
			$pref2orgmap[$newKey] = $field->key;
			$this->setFieldNewKeyName($field->key, $newKey);
		}
		return $pref2orgmap;
	}

	function __clone(){
		foreach($this->fields as &$field){
			$field = clone $field;
		}
	}

	function clearFields(){
		foreach($this->fields as $field){
			$field->setValue(null);
		}
	}

	function setAccess($access){
		if(is_array($this->fields)){
			foreach($this->fields as $field){
				$field->setAccess($access);
			}
		}
	}

	function dump(){
		$fields = [];
		foreach($this->fields as $f){
			$fields[$f->key] = $f->title . ' [' . $f->type . ']';
		}
		OBE_Trace::dump($fields);
	}
}