<?php

namespace App\Extensions\Components\Renderer;

use Nette\Forms\Rendering\DefaultFormRenderer;

class BaseFormRenderer extends DefaultFormRenderer{

	public function __construct(){
		$this->wrappers['controls']['container'] = NULL;
		$this->wrappers['pair']['container'] = 'div class=form-group';
		$this->wrappers['pair']['.error'] = 'has-error';
		$this->wrappers['control']['container'] = 'div class=col-sm-9';
		$this->wrappers['label']['container'] = 'div class="col-sm-3 control-label"';
		$this->wrappers['control']['description'] = 'span class=help-block';
		$this->wrappers['control']['errorcontainer'] = 'span class=help-block';

		$this->wrappers['error']['container'] = 'ul class="list-group"';
		$this->wrappers['error']['item'] = 'li class="list-group-item list-group-item-warning"';
	}
}