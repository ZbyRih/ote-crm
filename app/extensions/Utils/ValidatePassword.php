<?php

namespace App\Extensions\Utils;

use Nette\Application\UI\Form;

class ValidationPasswordError extends \Exception{
}

class ValidatePassword{

	/** @var string */
	private $field1Key;

	/** @var string */
	private $field1Title;

	/** @var string */
	private $field2Key;

	/** @var string */
	private $field2Title;

	/** @var [] */
	private $vals;

	/** @var Form */
	private $form;

	/** @var boolean */
	private $weakPass = false;

	/** @var int */
	private $strength = 8;

	/** @var boolean */
	private $require = true;

	public function __construct()
	{
	}

	/**
	 *
	 * @param string $key
	 * @param string $title
	 */
	public function setField1(
		$key,
		$title)
	{
		$this->field1Key = $key;
		$this->field1Title = $title;
	}

	/**
	 *
	 * @param string $key
	 * @param string $title
	 */
	public function setField2(
		$key,
		$title)
	{
		$this->field2Key = $key;
		$this->field2Title = $title;
	}

	/**
	 *
	 * @param [] $vals
	 */
	public function setVals(
		$vals)
	{
		$this->vals = $vals;
	}

	/**
	 *
	 * @param \Nette\Application\UI\Form $form
	 */
	public function setForm(
		Form $form)
	{
		$this->form = $form;
	}

	/**
	 *
	 * @param boolean $weakPass
	 */
	public function setWeakPass(
		$weakPass)
	{
		$this->weakPass = $weakPass;
	}

	/**
	 *
	 * @param number $strength
	 */
	public function setStrength(
		$strength)
	{
		$this->strength = $strength;
	}

	/**
	 *
	 * @param boolean $require
	 */
	public function setRequire(
		$require)
	{
		$this->require = $require;
	}

	public function execute()
	{
		$p1 = $this->vals[$this->field1Key];
		$p2 = $this->vals[$this->field2Key];

		if(empty($p1) && empty($p2)){
			if(!$this->require){
				return;
			}
			$this->form->addError('`' . $this->field1Title . '` a `' . $this->field2Title . '` musí být vyplněny');
			return;
			// 			throw new ValidationPasswordError('`' . $this->field1Title . '` a `' . $this->field2Title . '` musí být vyplněny');
		}

		if(empty($p1) || empty($p2)){
			$this->form->addError('`' . $this->field1Title . '` a `' . $this->field2Title . '` musí být vyplněny');
			return;
			// 			throw new ValidationPasswordError('`' . $this->field1Title . '` a `' . $this->field2Title . '` musí být vyplněny');
		}

		if($p1 != $p2){
			$this->form->addError('`' . $this->field1Title . '` a `' . $this->field2Title . '` se musí shodovat');
			return;
			// 			throw new ValidationPasswordError('`' . $this->field1Title . '` a `' . $this->field2Title . '` se musí shodovat');
		}

		if($this->weakPass){
			return;
		}

		$pass = $p1;

		if(strlen($pass) < $this->strength){
			$this->form->addError('Heslo musí mít minimálně ' . $this->strength . ' znaků');
		}

		if(!preg_match("#[0-9]+#", $pass)){
			$this->form->addError("Heslo musí obsahovat číslo");
		}

		if(!preg_match("#[A-Z]+#", $pass)){
			$this->form->addError("Heslo musí obsahovat velké písmeno");
		}
	}
}