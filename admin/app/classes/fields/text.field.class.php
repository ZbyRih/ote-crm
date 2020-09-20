<?php
class TextFieldClass extends FormFieldClass{

	/**
	 */
	function handleAccessPre(){
		if(!empty($this->data['value'])){
		}
	}

	/**
	 */
	function handleAccessPost(){
		if(!empty($this->data['value'])){
			$this->data['value'] = mb_ereg_replace('"', '&quot;', $this->data['value']);
		}
	}
}