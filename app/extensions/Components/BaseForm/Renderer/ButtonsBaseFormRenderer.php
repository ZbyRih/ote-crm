<?php

namespace App\Extensions\Components\Renderer;

use Nette\Utils\Html;

class ButtonsBaseFormRenderer implements IFormButtonsRendererExtension{

	public function stylePrimaryButton(Html $btn){
		$btn->addClass('btn btn-primary');
	}

	public function styleCancelButton(Html $btn, $caption){
		$btn->setName('button')->setHtml('<i class="fa fa-close"></i>&nbsp;' . $caption);
	}

	public function styleDefaultButton(Html $btn){
		$btn->addClass('btn btn-default');
	}

	public function styleSaveButton(Html $btn, $caption){
		$btn->setName('button')->setHtml('<i class="fa fa-save"></i>&nbsp;' . $caption);
	}
}