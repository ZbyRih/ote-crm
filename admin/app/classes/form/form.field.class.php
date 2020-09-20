<?php

class FormFieldClass{

	const NULLABLE = 1;

	const FLOAT = 2;

	const CURRENCY = 3;

	const NUMBER = 4;

	var $key = NULL;

	var $type = FormUITypes::TEXT;

	var $title = 'Název pole v definici nebyl';

	var $inline = false;

	var $access = FormFieldRights::DELETE;

	var $mask = NULL;

	var $dataType = NULL;

	var $template = NULL;

	var $default = NULL;

	var $valids = NULL;

	var $data = NULL;

	var $spec = NULL;

	var $statuses = [];

	var $hideFieldsOnValue = null;

	var $attributes = [];

	var $bPreSet = false;

	var $info = NULL;

	/**
	 * callback volajici se pred vytvorenim view pole pro smarty
	 * @var Closure
	 */
	var $callBackIn = NULL;

	var $callBackOut = NULL;

	/**
	 *
	 * @var AppFormClass2
	 */
	var $parent = NULL;

	/**
	 *
	 * @param Array $arrayDefinition
	 * @param AppFormClass2 $parent
	 * @param string $infoConstruct info třída
	 */
	function __construct($arrayDefinition = [], $parent = NULL, $infoConstruct = 'FormFieldInfo'){
		$this->parent = $parent;
		$this->data = [
			'value' => NULL
		];

		foreach($arrayDefinition as $k => $v){
			if(property_exists($this, $k)){
				$this->{$k} = $v;
			}
		}

		if(isset($arrayDefinition['value'])){
			$this->data['value'] = $arrayDefinition['value'];
		}

		if($this->default !== NULL && empty($this->data['value'])){
			$this->data = [
				'value' => $this->default
			];
		}

		if(isset($arrayDefinition['num'])){
			$this->dataType = $arrayDefinition['num'];
		}

		$this->info = new $infoConstruct($arrayDefinition, $this);

		$this->setTemplate();
	}

	function __clone(){
		$this->info = clone $this->info;
		$this->info->parent = $this;
	}

	public function setKeyName($key){
		$this->key = $key;
		return $this;
	}

	public function setData($data){
		if(is_array($data)){
			$this->data = $data;
		}else{
			$this->setValue($data);
		}
		return $this;
	}

	public function setValue($value){
		$this->data['value'] = $value;
		return $this;
	}

	public function setAccess($access){
		$this->access = $access;
		$this->setTemplate();
		return $this;
	}

	public function setStatuses($statusArray){
		$this->statuses = $statusArray;
		return $this;
	}

	public function setSpec($spec){
		$this->spec = $spec;
		return $this;
	}

	public function setHide($hideFieldsOnValue){
		foreach($hideFieldsOnValue as $v => &$fs){
			foreach($fs as $k => &$f){
				$f = str_replace('.', '_', $f);
			}
			$fs = (object) $fs;
		}
		$this->hideFieldsOnValue = htmlentities(json_encode((object) $hideFieldsOnValue), ENT_QUOTES, 'UTF-8');
		return $this;
	}

	public function dontFill(){
		$this->bPreSet = true;
		return $this;
	}

	private function setTemplate(){
		$this->template = FormUITypes::makeTemplate($this->type, $this->access);
		return $this;
	}

// 	'no_empty,len:len=16,alnum'
	function getValidateDef(){
		$valids = FormUITypes::getValidators($this->type);

		if(!empty($this->valids)){
			$items = explode(',', $this->valids);

			if(!empty($items)){
				foreach($items as $i){
					$es = explode(':', $i);
					if(count($es) > 1){
						$adds = [];
						$key = array_shift($es);
						foreach($es as $e){
							$a = explode('=', $e);
							$adds[$a[0]] = $a[1];
						}
						$valids[$key] = $adds;
					}else{
						$valids[$es[0]] = NULL;
					}
				}
			}
		}
		return $valids;
	}

	/**
	 * vola se pred getView z getFormViewFields z fields
	 * @param $formObj
	 */
	function callIn(){
		if($this->callBackIn !== NULL){
			call_user_func($this->callBackIn, $this, $this->parent);
		}
	}

	/**
	 * vola se pri volani GetData z fields, pred handleaccesspost, a pred getValue
	 * @param $formObj
	 */
	function callOut(){
		if($this->callBackOut !== NULL){
			call_user_func($this->callBackOut, $this, $this->parent);
		}
	}

	function setCallBacks($callBackIn = NULL, $callbackOut = NULL){
		if($callBackIn !== NULL){
			$this->callBackIn = $callBackIn;
		}
		if($callbackOut !== NULL){
			$this->callBackOut = $callbackOut;
		}
	}

	public function getValue(){
		return $this->correctVal($this->data['value']);
	}

	public function correctVal($val){
		switch($this->dataType){
			case self::NULLABLE: // neco kde kdyz je prazdnej string tak je tam null
				if($val === ''){
					return 'NULL';
				}
				break;
			case self::FLOAT: // float cislo
				if($val === ''){
					if($this->default){
						return OBE_Math::correctFloatNumber($this->default);
					}
					return 'NULL';
				}
				return OBE_Math::correctFloatNumber($val);
				break;
			case self::CURRENCY: // měna
				if($val === ''){
					if($this->default){
						return OBE_Math::correctFloatNumber(OBE_Math::removeCurrency($this->default));
					}
					return 'NULL';
				}
				return OBE_Math::correctFloatNumber(OBE_Math::removeCurrency($val));
				break;
			case self::NUMBER: // cele cislo
				if($val === ''){
					if($this->default){
						return OBE_Math::correctNumber($this->default);
					}
					return 'NULL';
				}
				return OBE_Math::correctNumber($val);
				break;
		}
		return $val;
	}

	public function getView(){
		return [
			'key' => $this->key,
			'type' => $this->type,
			'tpl' => $this->template,
			'title' => $this->title,
			'data' => $this->data,
			'spec' => $this->spec,
			'statuses' => $this->statuses,
			'hide' => $this->hideFieldsOnValue,
			'inline' => $this->inline,
			'access' => $this->access,
			'mask' => $this->mask,
			'num' => $this->dataType,
			'atrib' => $this->attributes
		];
	}

	/**
	 * naplni pole daty pro vytvoreni view, vytahne hodnotu z pole $data a priradi ji $this->data['value']
	 * , a potom vola FORM_CALLBACK_INFILL
	 * , a potom vola handleAccessPre
	 * @param Array $data
	 * @param AppFormClass2 $formObj
	 */
	function fillByData($data, $bDefault = false){
		if(!$this->bPreSet){
			if($bDefault){
				$val = $this->info->extractDataOrDefault($data, $this->default);
			}else{
				$val = $this->info->extractData($data);
			}
			$this->setValue($val);
		}
		if($this->parent){
			$this->parent->interCallBacks->runCallBackParams(AppFormClass2::ON_FILL, [
				$this,
				$data,
				$this->parent
			]);
		}
		$this->handleAccessPre();
	}

	/**
	 * zpracovani pred - necim
	 * @param AppFormClass2 $formObj
	 */
	function handleAccessPre(){
	}

	/**
	 * zpracovani po necem
	 * @param AppFormClass2 $formObj
	 */
	function handleAccessPost(){
	}

	function preValidate(){
	}

	/**
	 *
	 * @param AppFormClass2 $form
	 * @param boolean $toMap
	 */
	function addToForm($form, $toMap = true){
		$form->addFieldToForm($this, $toMap);
		return $this;
	}

	function setDisabled(){
		$this->setAccess(FormFieldRights::VIEW);
		return $this;
	}

	function setType($type){
		$this->dataType = $type;
		return $this;
	}

	function setMask($mask){
		$this->mask = $mask;
		return $this;
	}

	public function addAttribute($key, $value){
		$this->attributes[$key] = $value;
		return $this;
	}
}