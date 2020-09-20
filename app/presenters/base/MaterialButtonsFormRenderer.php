<?php

namespace App\Presenters;

use App\Extensions\Components\Renderer\IFormButtonsRendererExtension;
use Nette\Utils\Html;

class MaterialButtonsFormRenderer implements IFormButtonsRendererExtension{

	public function stylePrimaryButton(Html $btn){
		$btn->addClass('btn btn-flat btn-primary ink-reaction');
	}

	public function styleDefaultButton(Html $btn){
		$btn->addClass('btn btn-flat btn-default ink-reaction');
	}

	public function styleSaveButton(Html $btn, $caption){
		$btn->setName('button')->setHtml('<i class="fa fa-save"></i>&nbsp;' . $caption);
	}

	public function styleCancelButton(Html $btn, $caption){
		$btn->addClass('btn-accent-dark');
		$btn->setName('button')->setHtml('<i class="md md-cancel"></i>&nbsp;' . $caption);
	}
}