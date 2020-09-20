<?php

namespace App\Components;

use App\Extensions\Components\BaseComponent;
use App\Models\Enums\InfoEnums;

class InfoReport extends BaseComponent{

	/** @var string */
	private $header = 'Není co zobrazit';

	/** @var [] */
	private $items = [];

	public function __construct()
	{
	}

	/**
	 * @param string $header
	 */
	public function setHeader(
		$header)
	{
		$this->header = $header;
	}

	/**
	 * @param [] $items
	 */
	public function setItems(
		$items)
	{
		$this->items = $items;
	}

	/**
	 * {@inheritdoc}
	 * @see \App\Extensions\Components\BaseComponent::attached()
	 */
	protected function attached(
		$presenter)
	{
		parent::attached($presenter);
		$this->template->setParameters([
			'classes' => InfoEnums::$MSG_CLASSES,
			'header' => $this->header,
			'items' => $this->items
		]);
	}
}