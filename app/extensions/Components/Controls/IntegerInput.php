<?php

namespace App\Extensions\Components\Controls;

use Nette\Forms\Form;
use Nette\Forms\Controls\TextInput;

class IntegerInput extends TextInput{

	public function __construct(
		$label = null,
		$maxLength = null)
	{
		parent::__construct($label, $maxLength);
		$this->setNullable();
		$this->setRequired(false);
		$this->addRule(Form::INTEGER);
	}
}