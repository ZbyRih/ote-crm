<?php

class MultiFormClass extends AppFormClass2{

	var $multiKeys = [];

	var $originalKeys = [];

	var $prefixMap = [];

	/**
	 * prefixed keys to original keys
	 * @var array
	 */
	var $prefixFieldKeyMap = [];

	var $org_fields = null;

	function getOneSequence($prefix){
		$newFields = clone $this->fields;
		$this->prefixFieldKeyMap[$prefix] = $newFields->prefixFieldsKeyName($prefix);
		$this->prefixMap[$prefix] = $newFields;
		$fields = $newFields->getFormViewFields($this);
		return [
			'elements' => $fields
		];
	}

	function processForm($nameSubmit = null, $nameCancel = null, $keys){
		$this->buttons->clear();
		if($nameSubmit){
			$this->buttons->addSubmit(FormButton::T_SUBMIT, $nameSubmit);
		}
		if($nameCancel){
			$this->buttons->addCancel(FormButton::T_CANCEL, $nameCancel);
		}

		$sret = null;

		if($ret = $this->buttons->isSubmit($this->buttons->getSubmit())){
			if($ret){
				$ret = false;
				$org_fields = $this->fields;
				foreach($keys as $id){
					if(isset($this->prefixMap[$id])){
						$this->fields = $this->prefixMap[$id];

						if($data = $this->validate()){
							$this->fields->fillWithData($data);

							$ret |= true;
							if($this->interCallBacks->isCallable(AppFormClass2::ON_BEFORE_SAVE)){
								$ret |= $this->interCallBacks->runCallBackParams(AppFormClass2::ON_BEFORE_SAVE, [
									$this->getData($id),
									$id,
									$this
								]);
							}
						}
					}
				}
				$this->fields = $org_fields;
			}
		}

		if(!empty($this->errors)){
			return false;
		}

		return $ret;
	}

	/**
	 * ziskava data z formulare klic => hodnota
	 * @return array
	 */
	function getData($data = null){
		$id = $data;
		$data = [];

		foreach($this->fields->fields as $field){
			$field->callOut($this);
			$field->handleAccessPost();

			$data[$this->prefixFieldKeyMap[$id][$field->key]] = $field->getValue();
		}

		return $data;
	}

	function getButtons(){
		$data = [
			'EFORM' => [
				'buttons' => $this->buttons->get(),
				'errors' => $this->errors
			]
		];

		$view = new ViewElementClass();
		$view->type = 'form_end';
		$view->data = $data;
		return $view;
	}
}