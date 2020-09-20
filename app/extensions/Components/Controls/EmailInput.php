<?php

namespace App\Extensions\Components\Controls;

use App\Extensions\Components\IControlView;
use App\Extensions\Utils\Html;
use Nette\Forms\Controls\TextInput;

class EmailInput extends TextInput implements IControlView{

	public function viewFormat($value){
		if($value){
			return '';
		}

		return Html::el('a')->addAttributes([
			'href' => 'mailto:' . $value
		])->addText($value);
	}
}