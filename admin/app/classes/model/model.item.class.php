<?php

class ModelItemClass{

	/**
	 *
	 * @param Array $modelItem - model (mode[name] => array(...))
	 */
	public function onSaveBefor(&$modelItem){
	}

	/**
	 *
	 * @param Array $modelItem - model (mode[name] => array(...))
	 */
	public function onSaveAfter(&$modelItem){
	}

	/**
	 *
	 * @param Array $modelItem - jedna radka do databaze
	 */
	public function onUpdateBefor(&$modelItem){
	}

	/**
	 *
	 * @param Array $modelItem - jedna radka do databaze
	 */
	public function onUpdateAfter(&$modelItem){
	}

	/**
	 *
	 * @param Array $modelItem - jedna radka do databaze
	 */
	public function onInsertBefor(&$modelItem){
	}

	/**
	 *
	 * @param Array $modelItem - jedna radka do databaze
	 */
	public function onInsertAfter(&$modelItem){
	}

	/**
	 *
	 * @param Integer $id - primary key
	 * @param Array $conditions
	 * @param Boolean $cascade
	 * @return Boolean true if can
	 */
	public function onDelete($id, $conditions, $cascade){
		return true;
	}

	public function onSelect(){
	}

	/**
	 *
	 * @param ModelClass $object
	 */
	public function onCreateAssoc($object){
	}

	/**
	 *
	 * @param array $data
	 * @return array
	 */
	public function modData($data){
	}
}

class ModelSaveException extends OBE_Exception{

	var $name = 'Chyba uložení do databáze';

	var $errors = [];

	public function __construct($message = null, $code = null, $previous = null){
		$this->message = $message;
		$this->errors = [
			$message
		];
	}

	public function addErrors($errors){
		$this->errors = array_merge($this->errors, MArray::AllwaysArray($errors));
	}

	public function getErrors(){
		return $this->errors;
	}
}

class ModelDeleteException extends OBE_Exception{

	var $name = 'Chyba smazání z databáze';

	var $errors = [];

	public function __construct($message = null, $code = null, $previous = null){
		$this->message = $message;
		$this->errors = [
			$message
		];
	}

	public function addErrors($errors){
		$this->errors = array_merge($this->errors, MArray::AllwaysArray($errors));
	}

	public function getErrors(){
		return $this->errors;
	}
}