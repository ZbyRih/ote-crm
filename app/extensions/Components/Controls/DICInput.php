<?php

namespace App\Extensions\Components\Controls;

use Nette\Forms\Controls\TextInput;
use App\Extensions\Components\IControlView;
use App\Extensions\Helpers\Formaters;

class DICInput extends TextInput implements IControlView{

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
		$this->setAttribute('data-input-mask', 'CZ 999 999 99');
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
		return Formaters::dic($value);
	}
}