<?php

namespace App\Extensions\Components\Renderer;

use Nette\Forms\IFormRenderer;

class SpecGroupColWithRenderer implements IFormRendererExtension{

	private $width;

	public function __construct($width){
		$this->width = $width;
	}

	public function execute(IFormRenderer $renderer){
		$renderer->wrappers['group']['container'] = 'div class=col-md-' . $this->width;
	}
}