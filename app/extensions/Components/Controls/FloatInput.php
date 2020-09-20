<?php
namespace App\Extensions\Components\Controls;

use App\Extensions\Components\BaseForm;
use Nette\Forms\Controls\TextInput;

class FloatInput extends TextInput{

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nette\Forms\Controls\TextInput::__construct()
	 */
	public function __construct($label = null, $maxLength = null){
		parent::__construct($label, $maxLength);
		$this->addRule(BaseForm::FLOAT)->controlPrototype->addAttributes([
			'data-format' => 'autonum',
			'data-type' => 'float'
		]);
		$this->setRequired(false);
	}
}