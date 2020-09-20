<?php
class CheckboxFieldClass extends FormFieldClass{
	var $enableFields = [];

	function preValidate(){
		$fldname = $this->key;
		if(OBE_Http::issetPost($fldname)){
			OBE_Http::setPost($fldname, '1');
		}else{
			OBE_Http::setPost($fldname, '0');
		}
	}

	public function bindToFields($fields){
		$this->enableFields = $fields;
	}
}