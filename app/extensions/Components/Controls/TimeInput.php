<?php
namespace App\Extensions\Components\Controls;

use App\Extensions\Components\IControlView;
use App\Extensions\Helpers\Formaters;

class TimeInput extends BaseTimeInput implements IControlView{

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nette\Forms\Controls\TextInput::__construct()
	 */
	public function __construct(
		$label = null)
	{
		parent::__construct($label);
		$this->setRequired(false);
		$this->controlPrototype->addAttributes([
			'data-format' => 'time',
			'data-mask' => '99:99',
			'autocomplete' => 'off'
		]);
	}

	public function viewFormat(
		$value)
	{
		if(!$value){
			return '';
		}
		return Formaters::time($value);
	}
}