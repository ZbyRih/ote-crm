<?php

namespace App\Extensions\Components\Controls;

use App\Extensions\Components\BaseForm;
use App\Extensions\Components\IControlView;
use App\Extensions\Helpers\Formaters;
use Nette\Forms\Controls\TextInput;

class MetersInput extends TextInput implements IControlView{

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nette\Forms\Controls\TextInput::__construct()
	 */
	public function __construct(
		$label = null,
		$maxLength = null)
	{
		parent::__construct($label, $maxLength);
		$this->setNullable();
		$this->setRequired(false);
		$this->addRule(BaseForm::INTEGER);
	}

	public function viewFormat(
		$value)
	{
		return Formaters::num($value, 0);
	}
}