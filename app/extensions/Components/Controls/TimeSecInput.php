<?php
namespace App\Extensions\Components\Controls;

use Nette\Forms\Controls\TextInput;
use App\Extensions\Components\IControlView;

class TimeSecInput extends TextInput implements IControlView{

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nette\Forms\Controls\TextInput::__construct()
	 */
	public function __construct($label = null){
		parent::__construct($label);
		$this->setRequired(false);
		$this->controlPrototype->addAttributes([
			'data-format' => 'time-s',
			'data-mask' => '99:99:99',
			'autocomplete' => 'off'
		]);
	}

	public function viewFormat($value){
		if(!$value){
			return '';
		}
		return $value->format('H:i:s');
	}
}