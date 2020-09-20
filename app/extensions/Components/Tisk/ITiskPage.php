<?php

namespace App\Extensions\Components\Tisk;

use Nette\Application\UI\ITemplateFactory;

interface ITiskPage{

	public function getNode();

	public function render();

	public function setTemplateFactory(
		ITemplateFactory $tplFac);
}