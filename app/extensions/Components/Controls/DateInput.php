<?php
namespace App\Extensions\Components\Controls;

use App\Extensions\Components\IControlView;
use App\Extensions\Helpers\Formaters;

class DateInput extends BaseDateInput implements IControlView{

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nette\Forms\Controls\TextInput::__construct()
	 */
	public function __construct(
		$label = null)
	{
		parent::__construct($label);

		$this->controlPrototype->addAttributes([
			'data-format' => 'date',
			'data-mask' => '99.99. 9999',
			'autocomplete' => 'off'
		]);
	}

	public function viewFormat(
		$value)
	{
		if(!$value){
			return '';
		}
		return Formaters::date($value);
	}
}