<?php

namespace App\Extensions\Components\Tisk;

use App\Extensions\Utils\Html;
use Nette\SmartObject;

trait TTiskPageNode{
	use SmartObject;

	/** @var Html */
	private $node;

	/** @var [] */
	public $onAttached;

	protected function createNode()
	{
		$this->node = Html::el('section')->class('sheet padding-10mm');
	}

	public function addClass(
		$class)
	{
		$this->node->appendAttribute('class', $class);
	}

	public function getNode()
	{
		return $this->node;
	}
}