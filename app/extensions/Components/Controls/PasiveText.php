<?php

namespace App\Extensions\Components\Controls;

use App\Extensions\Components\BaseForm;
use Nette\Forms\IControl;
use Nette\Utils\Html;

/**
 * Base class that implements the basic functionality common to form controls.
 * @property mixed $value
 * @property bool $disabled
 * @property bool $omitted
 * @property-read Html $control
 * @property-read Html $label
 */
class PasiveText extends \Nette\ComponentModel\Component implements IControl{

	/** @var string */
	public static $idMask = 'frm-%s';

	protected $value;

	protected $label;

	/** @var Html  control element template */
	protected $control;

	public $caption;

	/** @var \Nette\Localization\ITranslator|bool */
	private $translator = true;

	public function __construct($caption){
		$this->label = Html::el('label');
		$this->caption = $caption;
		$this->control = Html::el('label');
	}

	public function getControl(){
		return Html::el('label')->addText($this->value);
	}

	/**
	 * Generates label's HTML element.
	 * @param string|object
	 * @return Html|string
	 */
	public function getLabel($caption = NULL){
		$label = clone $this->label;
		$label->for = $this->getHtmlId();
		$label->setText($this->translate($caption === NULL ? $this->caption : $caption));
		return $label;
	}

	public function setValue($value){
		$this->value = $value;
	}

	public function getValue(){
		return null;
	}

	/**
	 * Loads HTTP data.
	 * @return void
	 */
	public function loadHttpData(){
	}

	/**
	 * Is control mandatory?
	 * @return bool
	 */
	public function isRequired(){
		return false;
	}

	public function isOmitted(){
		return true;
	}

	public function isDisabled(){
		return false;
	}

	/**
	 *
	 * @return bool
	 */
	public function hasErrors(){
		return false;
	}

	public function getErrors(){
		return [];
	}

	/**
	 *
	 * @return void
	 */
	public function validate(){
	}

	public function setOption($key, $value){
	}

	public function getOption($key){
		return null;
	}

	/**
	 * Returns control's HTML id.
	 * @return mixed
	 */
	public function getHtmlId(){
		if(!isset($this->control->id)){
			$this->control->id = sprintf(self::$idMask, $this->lookupPath());
		}
		return $this->control->id;
	}

	/**
	 * Returns form.
	 * @param bool
	 * @return BaseForm|NULL
	 */
	public function getForm($throw = TRUE){
		return $this->lookup(BaseForm::class, $throw);
	}

	/**
	 * Returns translated string.
	 * @param mixed
	 * @param int plural count
	 * @return mixed
	 */
	public function translate($value, $count = NULL){
		if($translator = $this->getTranslator()){
			$tmp = is_array($value) ? [
				&$value
			] : [
				[
					&$value
				]
			];
			foreach($tmp[0] as &$v){
				if($v != NULL && !$v instanceof Html){ // intentionally ==
					$v = $translator->translate($v, $count);
				}
			}
		}
		return $value;
	}

	/**
	 * Returns translate adapter.
	 * @return \Nette\Localization\ITranslator|NULL
	 */
	public function getTranslator(){
		if($this->translator === true){
			return $this->getForm(false) ? $this->getForm()->getTranslator() : null;
		}
		return $this->translator;
	}

	public function setDisabled($disabled = false){
	}
}