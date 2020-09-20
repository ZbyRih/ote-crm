<?php

namespace App\Extensions\Components\Tisk;

use Nette\Application\UI\ITemplateFactory;
use Nette\Application\UI\ITemplate;
use App\Extensions\Utils\Html;
use Nette\SmartObject;

/**
 *
 * @property-read ITemplate $template
 */
class TiskComponent implements ITemplate{

	use SmartObject;

	/** @var [] */
	private $params = [
		'title' => '',
		'format' => 'A4',
		'back' => ''
	];

	/** @var ITemplateFactory */
	private $facTpl;

	/** @var ITemplate */
	private $template;

	/** @var [] */
	private $pages = [];

	/** @var [] */
	private $buttons = [];

	public function __construct(
		ITemplateFactory $tplFac)
	{
		$this->facTpl = $tplFac;
		$this->template = $tplFac->createTemplate();
	}

	public function getTemplate()
	{
		return $this->template;
	}

	public function getFile()
	{
		$this->template->getFile();
	}

	public function getTitle()
	{
		return $this->params['title'];
	}

	public function getFormat()
	{
		return $this->params['format'];
	}

	public function setFile(
		$file)
	{
		$this->template->setFile($file);
	}

	public function setTitle(
		$title)
	{
		$this->params['title'] = $title;
		return $this;
	}

	public function setFormat(
		$format)
	{
		$this->params['format'] = $format;
		return $this;
	}

	public function setBack(
		$back)
	{
		$this->params['back'] = $back;
		return $this;
	}

	public function addPage(
		ITiskPage $page)
	{
		$this->pages[] = $page;
		$page->setTemplateFactory($this->facTpl);
		$page->onAttached($this);
	}

	public function addButtons(
		Html $button)
	{
		$this->buttons[] = $button;
	}

	public function render()
	{
		$this->prepareTemplate();
		$this->template->render();
	}

	public function __toString()
	{
		$this->prepareTemplate();
		return (string) $this->template;
	}

	public function prepareTemplate()
	{
		if(!$this->template->getFile()){
			$this->template->setFile(__DIR__ . '/default.latte');
		}

		$this->template->pages = $this->pages;
		$this->template->buttons = $this->buttons;
		$this->template->setParameters($this->params);
	}
}