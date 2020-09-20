<?php

namespace App\Extensions\Components\Renderer;

use Nette\Utils\Html;

interface IFormButtonsRendererExtension{

	public function stylePrimaryButton(Html $btn);

	public function styleDefaultButton(Html $btn);

	public function styleSaveButton(Html $btn, $caption);

	public function styleCancelButton(Html $btn, $caption);
}