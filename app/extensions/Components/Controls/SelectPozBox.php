<?php
namespace App\Extensions\Components\Controls;

use Nette\Forms\Controls\SelectBox;

class SelectPozBox extends SelectBox{

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nette\Forms\Controls\SelectBox::__construct()
	 */
	public function __construct($label = null, array $items = null){
		parent::__construct($label, $items);

		$this->controlPrototype->addAttributes([
			'data-show-poz' => 'true'
		]);
	}
}