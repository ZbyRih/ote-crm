<?php
namespace App\Extensions\Components\Controls;

use App\Extensions\Components\BaseForm;
use Nette\Forms\Controls\TextInput;

class CheckHideBox extends TextInput{

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nette\Forms\Controls\TextInput::__construct()
	 */
	public function __construct($label = null, $id = null){
		parent::__construct($label);
		$this->addCondition(BaseForm::EQUAL, true)->toggle($id);
	}
}