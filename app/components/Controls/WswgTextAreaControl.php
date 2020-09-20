<?php

namespace App\Components\Controls;

use Nette\Forms\Controls\TextArea;

class WswgTextAreaControl extends TextArea{

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nette\Forms\Controls\TextArea::__construct()
	 */
	public function __construct(
		$label = null)
	{
		parent::__construct($label);
		$this->setAttribute('data-format', 'wswg');
	}
}