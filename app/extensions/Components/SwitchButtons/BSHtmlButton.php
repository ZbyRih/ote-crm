<?php

namespace App\Extensions\Components;

use App\Extensions\Utils\Html;
use App\Extensions\Utils\Helpers\ClassNames;

class BSHtmlButton extends Html{

	/** @var string */
	private $link;

	/** @var ClassNames */
	private $classes;

	public function __construct(
		$title,
		$classes,
		$link = null,
		$ico = null,
		$el = 'a')
	{
		$this->link = $link;

		$classes = explode(' ', $classes);
		$this->classes = new ClassNames([
			'btn' => true
		] + array_combine($classes, array_fill(0, count($classes), true)));

		if($ico){
			$i = Html::el('i')->class($ico);
			$this->addHtml($i)->addHtml('&nbsp;');
		}

		$this->setName($el);
		$this->addText($title);
	}

	public function setActive(
		$active = true)
	{
		$this->classes->set('active', $active);
		return $this;
	}

	public function setClass(
		$class,
		$on = true)
	{
		$this->classes->set($class, $on);
		return $this;
	}

	public function setLink(
		$link)
	{
		$this->link = $link;
		return $this;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nette\Utils\Html::render()
	 */
	public function render(
		$indent = null)
	{
		if($this->link){
			$this->setAttribute('href', $this->link);
		}
		$this->setAttribute('class', $this->classes->__toString());
		return parent::render();
	}
}