<?php
class PasswordFieldClass extends FormFieldClass{

	private $encode = true;

	/**
	 *
	 * @param FormFieldClass $field
	 */
	function handleAccessPre(){
// 		OBE_Trace::callPoint('set tu null ' . $this->key,3,5);
		if(!empty($this->data['value'])){
			$this->data['value'] = ($this->encode)? OBE_Strings::sha256($this->data['value']) : $this->data['value'];
		}else{
			$this->data['value'] = null;
		}
	}

	/**
	 *
	 * @param FormFieldClass $field
	 */
	function handleAccessPost(){
// 		$this->data['value'] = '';
// 		if(!empty($this->data['value'])){
// 			$this->data['value'] = OBE_Strings::sha256($this->data['value']);
// 		}else{
// 			$this->data['value'] = null;
// 		}
	}

	public function disableEncode(){
		$this->encode = false;
	}
}