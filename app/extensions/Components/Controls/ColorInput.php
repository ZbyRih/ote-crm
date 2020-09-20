<?php
namespace App\Extensions\Components\Controls;

use Nette\Forms\Controls\TextInput;

class ColorInput extends TextInput{

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nette\Forms\Controls\TextInput::__construct()
	 */
	public function __construct($label = null){
		parent::__construct($label);

		$this->controlPrototype->addAttributes([
			'data-format' => 'color',
			'data-mask' => '#999999'
		]);
	}
}