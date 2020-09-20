<?php

namespace App\Components;

use App\Extensions\Components\BaseComponent;

class PreformatView extends BaseComponent{

	const TYPE_PRE = 'pre';

	const TYPE_HTML = 'html';

	/** @var string */
	private $type = self::TYPE_PRE;

	/** @var string */
	private $title;

	/** @var string */
	private $content;

	public function __construct()
	{
	}

	/**
	 *
	 * @param string $type
	 */
	public function setType(
		$type)
	{
		$this->type = $type;
	}

	/**
	 *
	 * @param string $title
	 */
	public function setTitle(
		$title)
	{
		$this->title = $title;
	}

	/**
	 *
	 * @param string $content
	 */
	public function setContent(
		$content)
	{
		$this->content = $content;
	}

	public function render()
	{
		$this->template->type = $this->type;
		$this->template->title = $this->title;
		$this->template->content = $this->content;

		parent::render();
	}
}