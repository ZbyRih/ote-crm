<?php

namespace App\Components;

use App\Extensions\Components\BaseComponent;

class YearControl extends BaseComponent{

	/** @var array */
	private $items;

	/** @var string */
	private $current;

	/** @var [] */
	public $onChange = [];

	public function __construct()
	{
	}

	/**
	 *
	 * @param array $years
	 */
	public function setItems(
		$items)
	{
		$this->items = array_values($items);
	}

	/**
	 *
	 * @param string $current
	 */
	public function setCurrent(
		$current)
	{
		$this->current = $current;
	}

	public function render()
	{
		$c = array_search($this->current, $this->items);

		if(array_key_exists($c - 1, $this->items)){
			$this->template->prev = $this->items[$c - 1];
		}else{
			$this->template->prev = null;
		}

		if(array_key_exists($c + 1, $this->items)){
			$this->template->next = $this->items[$c + 1];
		}else{
			$this->template->next = null;
		}

		$this->template->current = $this->current;

		parent::render();
	}

	public function handleSelect(
		$current)
	{
		$this->current = $current;
		$this->onChange($this->current);
	}
}