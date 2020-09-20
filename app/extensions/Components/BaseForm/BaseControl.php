<?php
namespace App\Extensions\Components;

use Nette\Application\UI\Form as NForm;
use Nette\Forms\Rules;
use Nette\Utils\Html;

trait BaseControl{

	public $control;

	/** @var bool */
	protected $disabled = false;

	/** @var array */
	private $errors = [];

	/** @var array user options */
	private $options = [];

	/** @var Rules */
	private $rules;

	/** @var bool|null */
	private $omitted;

	public function BaseControl(){
		$this->control = Html::el('span');
		$this->rules = new Rules($this);
	}

	/**
	 * Is control filled?
	 * @return bool
	 */
	public function isFilled(){
		$value = $this->getValue();
		return $value !== null && $value !== [] && $value !== '';
	}

	/**
	 * Sets whether control value is excluded from $form->getValues() result.
	 * @param bool
	 * @return static
	 */
	public function setOmitted($value = true){
		$this->omitted = (bool) $value;
		return $this;
	}

	/**
	 * Is control value excluded from $form->getValues() result?
	 * @return bool
	 */
	public function isOmitted(){
		return $this->omitted || ($this->isDisabled() && $this->omitted === null);
	}

	/**
	 * Adds error message to the list.
	 * @param string|object
	 * @return void
	 */
	public function addError($message, $translate = true){
		$this->errors[] = $translate ? $this->translate($message) : $message;
	}

	/**
	 *
	 * @return Rules
	 */
	public function getRules(){
		return $this->rules;
	}

	/**
	 * Makes control mandatory.
	 * @param mixed state or error message
	 * @return static
	 */
	public function setRequired($value = true){
		$this->rules->setRequired($value);
		foreach($this->components as $c){
			if($c instanceof Container){
				foreach($c->components as $cc){
					$cc->setRequired($value);
				}
			}else{
				$c->setRequired($value);
			}
		}
		return $this;
	}

	/**
	 * Is control mandatory?
	 * @return bool
	 */
	public function isRequired(){
		return $this->rules->isRequired();
	}

	/**
	 * Performs the server side validation.
	 * @return void
	 */
	public function validate(){
		if($this->isDisabled()){
			return;
		}
		$this->cleanErrors();
		$this->rules->validate();
	}

	/**
	 * Returns errors corresponding to control.
	 * @return string|null
	 */
	public function getError(){
		return $this->errors ? implode(' ', array_unique($this->errors)) : null;
	}

	/**
	 * Returns errors corresponding to control.
	 * @return array
	 */
	public function getErrors(){
		return array_unique($this->errors);
	}

	/**
	 *
	 * @return bool
	 */
	public function hasErrors(){
		return (bool) $this->errors;
	}

	/**
	 *
	 * @return void
	 */
	public function cleanErrors(){
		$this->errors = [];
	}

	/**
	 * Sets user-specific option.
	 * @return static
	 */
	public function setOption($key, $value){
		if($value === null){
			unset($this->options[$key]);
		}else{
			$this->options[$key] = $value;
		}
		return $this;
	}

	/**
	 * Returns user-specific option.
	 * @return mixed
	 */
	public function getOption($key, $default = null){
		return isset($this->options[$key]) ? $this->options[$key] : $default;
	}

	/**
	 * Disables or enables control.
	 * @param bool
	 * @return static
	 */
	public function setDisabled($value = true){
		if($this->disabled = (bool) $value){
			$this->setValue(null);
		}elseif(($form = $this->getForm(false)) && $form->isAnchored() && $form->isSubmitted()){
			$this->loadHttpData();
		}
		return $this;
	}

	/**
	 * Is control disabled?
	 * @return bool
	 */
	public function isDisabled(){
		return $this->disabled === true;
	}

	/**
	 * Returns form.
	 * @param bool
	 * @return BaseForm|null
	 */
	public function getForm($throw = true){
		return $this->lookup(NForm::class, $throw);
	}

	/**
	 * Loads HTTP data.
	 * @return void
	 */
	public function loadHttpData(){
		$this->setValue($this->getHttpData(NForm::DATA_TEXT));
	}

	/**
	 * Loads HTTP data.
	 * @return mixed
	 */
	protected function getHttpData($type, $htmlTail = null){
		return $this->getForm()->getHttpData($type, $this->getHtmlName() . $htmlTail);
	}

	/**
	 * Returns HTML name of control.
	 * @return string
	 */
	public function getHtmlName(){
		return \Nette\Forms\Helpers::generateHtmlName($this->lookupPath(NForm::class));
	}

	/**
	 * Changes control's HTML id.
	 * @param mixed new ID, or false or null
	 * @return static
	 */
	public function setHtmlId($id){
		$this->control->id = $id;
		return $this;
	}

	/**
	 * Returns control's HTML id.
	 * @return mixed
	 */
	public function getHtmlId(){
		if(!isset($this->control->id)){
			$this->control->id = sprintf(\Nette\Forms\Controls\BaseControl::$idMask, $this->lookupPath());
		}
		return $this->control->id;
	}
}