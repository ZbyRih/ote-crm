<?php

namespace App\Extensions\Components\Controls;

use App\Extensions\Components\BaseForm;
use Nette\Forms\Controls\TextInput;

class BaseTimeInput extends TextInput{

	const DEFAULT_FORMAT = 'H:i';

	const SECONDS_FORMAT = 'H:i:s';

	const DEFAULT_PATERN = '/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/';

	const SECONDS_PATERN = '/^([01]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/';

	/** @var bool */
	private static $registered = FALSE;

	/** @var string */
	private $format;

	/** @var string */
	private $patern;

	/** @var bool */
	private $strict = FALSE;

	/**
	 *
	 * @param string $format
	 * @param string|NULL $label
	 */
	public function __construct($label = NULL, $seconds = false){
		parent::__construct($label);
		$this->format = ($seconds) ? self::SECONDS_FORMAT : self::DEFAULT_FORMAT;
		$this->patern = ($seconds) ? self::SECONDS_PATERN : self::DEFAULT_PATERN;
	}

	/**
	 *
	 * @return BaseTimeInput
	 */
	public function enableStrict(){
		$this->strict = TRUE;
		return $this;
	}

	/**
	 *
	 * @return BaseTimeInput
	 */
	public function disableStrict(){
		$this->strict = FALSE;
		return $this;
	}

	/**
	 *
	 * @param \DateTimeInterface|string|int|float|NULL $value
	 * @return BaseTimeInput
	 */
	public function setValue($value = NULL){
		if($value === NULL || $value === ''){
			return parent::setValue(NULL);
		}elseif(is_string($value) && preg_match($this->patern, $value)){
			return parent::setValue($value);
		}elseif(is_float($value) || is_int($value)){
			return parent::setValue((new \DateTime('@0'))->modify('+' . $value . ' seconds')->format($this->format));
		}elseif($value instanceof \DateTimeInterface){
			return parent::setValue($value->format($this->format));
		}

		throw new \Nette\InvalidArgumentException('Value must be 00:00 format or DateTimeInterface or NULL in ' . $this->name);
	}

	/**
	 *
	 * @return \DateTimeImmutable|NULL
	 */
	public function getValue(){
		if(!$this->isFilled()){
			return NULL;
		}
		$datetime = $this->getRawValue();
		if($datetime === FALSE || !preg_match($this->patern, $datetime)){
			return NULL;
		}
		return $datetime;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getRawValue(){
		return parent::getValue();
	}

	public function loadHttpData(){
		$input = $this->getHttpData(BaseForm::DATA_TEXT);
		if(empty($input)){
			parent::setValue(NULL);
			return;
		}
		if($input !== FALSE && preg_match($this->patern, $input)){
			parent::setValue($input);
			return;
		}
		parent::setValue('');
	}

	/**
	 *
	 * @return \Nette\Utils\Html
	 */
	public function getControl(){
		$control = parent::getControl();
		$control->value($this->getRawValue());
		$control->type('text');
		return $control;
	}

	/**
	 *
	 * @return bool
	 */
	public function isFilled(){
		$value = $this->getRawValue();
		return $value !== NULL;
	}

	/**
	 *
	 * @return bool
	 */
	public function validateDate(){
		return $this->isDisabled() || !$this->isFilled() || $this->getValue() !== NULL;
	}

	/**
	 *
	 * @param string|bool $message
	 * @return BaseTimeInput
	 */
	public function setRequired($message = TRUE){
		if($message !== FALSE && !is_string($message)){
			throw new \Nette\InvalidArgumentException('Message must be string');
		}
		parent::setRequired($message);
		if($message !== FALSE){
			$this->addCondition(BaseForm::FILLED)->addRule(function (BaseTimeInput $control){
				return $this->validateDate();
			}, $message);
		}
		return $this;
	}
}