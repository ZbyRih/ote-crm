<?php

namespace App\Extensions\Components\Tisk;

use Nette\Application\UI\ITemplateFactory;

class TiskHtmlPage implements ITiskPage{

	use TTiskPageNode;

	/** @var string */
	private $html;

	public function __construct()
	{
		$this->createNode();
	}

	/**
	 *
	 * @param string $html
	 */
	public function setHtml(
		$html)
	{
		$this->html = $html;
	}

	public function render()
	{
		echo $this->html;
	}

	public function setTemplateFactory(
		ITemplateFactory $tplFac)
	{
	}
}