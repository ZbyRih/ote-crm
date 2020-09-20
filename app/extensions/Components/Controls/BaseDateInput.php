<?php

namespace App\Extensions\Components\Controls;

use App\Extensions\Components\BaseForm;
use Nette\Forms\Controls\TextInput;
use Carbon\Carbon;

class BaseDateInput extends TextInput{

	const DEFAULT_FORMAT = 'j.n. Y';

	/** @var bool */
	private static $registered = FALSE;

	/** @var string */
	private $format;

	/** @var bool */
	private $strict = FALSE;

	/**
	 *
	 * @param string $format
	 * @param string|NULL $label
	 */
	public function __construct(
		$label = NULL)
	{
		parent::__construct($label);
		$this->format = self::DEFAULT_FORMAT;
	}

	/**
	 *
	 * @return BaseDateInput
	 */
	public function enableStrict()
	{
		$this->strict = TRUE;
		return $this;
	}

	/**
	 *
	 * @return BaseDateInput
	 */
	public function disableStrict()
	{
		$this->strict = FALSE;
		return $this;
	}

	/**
	 *
	 * @param \DateTimeInterface|NULL $value
	 * @return BaseDateInput
	 */
	public function setValue(
		$value = NULL)
	{
		if($value === NULL || $value === ''){
			return parent::setValue(NULL);
		}elseif(is_string($value)){
			try{
				$value = Carbon::createFromFormat('Y-m-d', $value);
			}catch(\InvalidArgumentException $e){
				throw new \Nette\InvalidArgumentException('Value must be string in valid format');
			}
		}elseif(!$value instanceof \DateTimeInterface){
			throw new \Nette\InvalidArgumentException('Value must be DateTimeInterface or NULL');
		}
		return parent::setValue($value->format($this->format));
	}

	/**
	 *
	 * @return \DateTimeImmutable|NULL
	 */
	public function getValue()
	{
		if(!$this->isFilled()){
			return NULL;
		}
		$datetime = \DateTimeImmutable::createFromFormat($this->format, $this->getRawValue());
		if($datetime === FALSE || $datetime->format($this->format) !== $this->getRawValue()){
			return NULL;
		}
		return $datetime->setTime(0, 0, 0);
	}

	/**
	 *
	 * @return mixed
	 */
	public function getRawValue()
	{
		return parent::getValue();
	}

	public function loadHttpData()
	{
		$input = $this->getHttpData(\Nette\Forms\Form::DATA_TEXT);
		if(empty($input)){
			parent::setValue(NULL);
			return;
		}
		$datetime = \DateTimeImmutable::createFromFormat($this->normalizeFormat($this->format), $this->normalizeFormat($input));
		if($datetime !== FALSE && $datetime->format($this->normalizeFormat($this->format)) === $this->normalizeFormat($input)){
			parent::setValue($datetime->format($this->format));
			return;
		}
		parent::setValue('');
	}

	/**
	 *
	 * @return \Nette\Utils\Html
	 */
	public function getControl()
	{
		$control = parent::getControl();
		$control->value($this->getRawValue());
		$control->type('text');
		return $control;
	}

	/**
	 *
	 * @return bool
	 */
	public function isFilled()
	{
		$value = $this->getRawValue();
		return $value !== NULL;
	}

	/**
	 *
	 * @return bool
	 */
	public function validateDate()
	{
		return $this->isDisabled() || !$this->isFilled() || $this->getValue() !== NULL;
	}

	/**
	 *
	 * @param string $input
	 * @return string
	 */
	private function normalizeFormat(
		$input)
	{
		if($this->strict){
			return $input;
		}
		return \Nette\Utils\Strings::replace($input, '~\s+~', '');
	}

	/**
	 *
	 * @param string|bool $message
	 * @return BaseDateInput
	 */
	public function setRequired(
		$message = TRUE)
	{
// 		if($message !== FALSE && !is_string($message)){
// 			throw new \Nette\InvalidArgumentException('Message must be string');
// 		}
		parent::setRequired($message);
		if($message !== FALSE){
			$this->addCondition(BaseForm::FILLED)->addRule(function (){
				return $this->validateDate();
			}, $message);
		}
		return $this;
	}
}