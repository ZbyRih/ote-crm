<?php

namespace App\Extensions\Components\Controls;

use App\Extensions\Components\IControlView;
use App\Extensions\Helpers\Formaters;
use Nette\Forms\Controls\TextInput;

class RPInput extends TextInput implements IControlView{

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
		$this->setAttribute('data-input-mask', '(*** ){1,}');
		$this->setAttribute('data-input-mask-opts', json_encode([
			'placeholder' => ''
		]));
		$this->setRequired(false);
		$this->addFilter(function (
			$value){
			return str_replace([
				' ',
				'_'
			], [
				'',
				''
			], $value);
		});
	}

	public function viewFormat(
		$value)
	{
		return Formaters::rp($value);
	}
}