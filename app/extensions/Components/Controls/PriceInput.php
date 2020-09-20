<?php

namespace App\Extensions\Components\Controls;

use App\Extensions\Components\IControlView;
use App\Extensions\Helpers\Formaters;

class PriceInput extends FloatInput implements IControlView{

	/**
	 *
	 * {@inheritdoc}
	 * @see \App\Extensions\Components\Controls\FloatInput::__construct()
	 */
	public function __construct(
		$label = null,
		$maxLength = null)
	{
		parent::__construct($label, $maxLength);
		// 		$this->addFilter(function ($value){
		// 			return (float) str_replace(' ', '', $value);
		// 		});
	}

	public function viewFormat(
		$value)
	{
		return Formaters::price($value);
	}
}