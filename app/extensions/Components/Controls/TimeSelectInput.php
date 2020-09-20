<?php
namespace App\Extensions\Components\Controls;

use App\Extensions\Components\IControlView;

class TimeSelectInput extends BaseTimeInput implements IControlView{

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nette\Forms\Controls\TextInput::__construct()
	 */
	public function __construct($label = null){
		parent::__construct($label);
		$this->setRequired(false);
		$this->controlPrototype->addAttributes(
			[
				'data-type' => 'select',
				'data-format' => 'time',
				'data-mask' => '99:99',
				'autocomplete' => 'off'
			]);
	}

	public function viewFormat($value){
		return $value;
	}
}