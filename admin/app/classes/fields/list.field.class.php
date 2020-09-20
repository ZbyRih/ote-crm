<?php
class ListFieldClass extends FormFieldClass{

	const NEVYBRANO = ' - nevybráno - ';

	var $list;

	var $select = null;

	var $paralel = null;

	var $formSend = false;

	function __construct($arrayDefinition = [], $parent = null, $infoConstruct = 'FormFieldInfo'){
		parent::__construct($arrayDefinition, $parent, $infoConstruct);
		if(isset($arrayDefinition['list'])){
			$this->setList($arrayDefinition['list']);
		}
	}

	function setList($list, $selected = null){
		$this->list = $list;
		if($selected){
			$this->select = $selected;
		}
		$this->handleAccessPre();
		return $this;
	}

	/**
	 *
	 * @param FormFieldClass $field
	 * @param array $items
	 * @return ListFieldClass
	 */
	function setParalel($field, $items){
		$this->paralel[$field->key] = $items;
		$this->handleAccessPre();
		return $this;
	}

	/**
	 *
	 * @param ModelClass/string $model
	 * @param string $val
	 * @param string $name
	 * @param boolean $nullItem
	 * @param string $selected
	 */
	function setListByModel($model, $key, $val, $nullItem = true, $selected = null, $cond = [], $order = []){
		if(is_string($model)){
			$model = new $model();
		}

		$model->order = $order;
		$items = $model->FindAll($cond);
		$model->associatedModels = [];

		$items = MArray::MapValToKeyFromMArray($items, $model->name, $key, $val);
		if($nullItem){
			$items = [
				'null' => self::NEVYBRANO
			] + $items;
		}

		$this->list = $items;
		if($selected){
			$this->select = $selected;
		}
		$this->handleAccessPre();
		return $this;
	}

	public function setFormSend($state = true){
		$this->formSend = $state;
		$this->handleAccessPre();
		return $this;
	}

	/**
	 * odchytava type pro zobrazeni vyberu pomoci drop downu
	 * @param FormFieldClass $field
	 */
	function handleAccessPre(){
		$this->data['list'] = $this->list;
		$this->data['form-send'] = $this->formSend;
		if($this->select){
			$this->data['value'] = $this->select;
		}
		if(!empty($this->paralel)){
			$this->data['rFields'] = array_keys($this->paralel);
			$this->data['rItems'] = $this->paralel;
		}else{
			$this->data['rFields'] = null;
			$this->data['rItems'] = null;
		}
	}

	/**
	 *
	 * @param FormFieldClass $field
	 */
	function handleAccessPost(){
		if(isset($this->data['value']) && $this->data['value'] === 'null'){
			$this->data['value'] = null;
		}
	}
}