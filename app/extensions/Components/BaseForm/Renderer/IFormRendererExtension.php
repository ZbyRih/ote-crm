<?php

namespace App\Extensions\Components\Renderer;

use Nette\Forms\IFormRenderer;

interface IFormRendererExtension{

	public function execute(IFormRenderer $renderer);
}