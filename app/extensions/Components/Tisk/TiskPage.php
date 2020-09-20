<?php

namespace App\Extensions\Components\Tisk;

use App\Extensions\Components\TTemplateDefaultResolver;
use App\Extensions\Exceptions\UndefinedProperty;
use Nette\Application\UI\ITemplateFactory;
use Nette\Application\UI\ITemplate;

/**
 *
 * @property ITemplate $template
 */
class TiskPage implements ITiskPage{
	use TTiskPageNode;

	use TTemplateDefaultResolver;

	/** @var ITemplateFactory */
	private $facTpl;

	/** @var ITemplate */
	private $template;

	public function __construct()
	{
		$this->createNode();
	}

	public function setTemplateFactory(
		ITemplateFactory $tplFac)
	{
		$this->facTpl = $tplFac;
	}

	public function getTemplate()
	{
		if($this->template){
			return $this->template;
		}

		if(!$this->facTpl){
			throw new UndefinedProperty('template factory');
		}

		$this->template = $this->facTpl->createTemplate();

		$this->template->setFile($this->getTemplateDefaultFile());

		return $this->template;
	}

	public function render()
	{
		$this->template->render();
	}
}