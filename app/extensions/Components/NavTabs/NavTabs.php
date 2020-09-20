<?php
namespace App\Extensions\Components;

use App\Extensions\Utils\Arrays;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Nette\Application\ApplicationException;

class NavTabsChangeEvent extends Event{

	/** @var string */
	public $selected;
}

class NavTabs extends BaseComponent{

	/** @var EventDispatcher */
	private $dispatcher;

	/** @var [] */
	private $items = [];

	/** @var [] */
	private $disableds = [];

	/** @var string */
	private $selected;

	/** @var boolean */
	private $hide = false;

	/** @var [] fn($this, $id) */
	public $onChange;

	public function __construct(
		EventDispatcher $dispatcher)
	{
		$this->dispatcher = $dispatcher;
	}

	public function setItems(
		array $items)
	{
		$this->items = $items;
		return $this;
	}

	/**
	 *
	 * @param string $tab
	 * @param boolean $throw
	 * @throws ApplicationException
	 * @return \App\Extensions\Components\NavTabs
	 */
	public function setTab(
		$tab,
		$throw = true)
	{
		if($tab === null){
			$this->selected = null;
			return;
		}

		if(array_key_exists($tab, $this->items)){
			$this->selected = $tab;
			return;
		}

		$this->selected = null;

		if($throw){
			throw new \InvalidArgumentException('Tab `' . $tab . '` didnt exists.');
		}
	}

	public function setDisableds(
		array $keys)
	{
		$this->disableds = $keys;
	}

	public function hide()
	{
		$this->hide = true;
	}

	public function show()
	{
		$this->hide = false;
	}

	public function handleChange(
		$id)
	{
		$this->setTab($id);
		$this->onChange($this, $this->selected);

		$event = new NavTabsChangeEvent();
		$event->selected = $this->selected;
		$this->dispatcher->dispatch('nav.change', $event);
	}

	public function render()
	{
		$items = array_map(
			function (
				$i,
				$k){
				return (object) [
					'key' => $k,
					'label' => $i,
					'disabled' => in_array($k, $this->disableds)
				];
			}, $this->items, array_keys($this->items));

		$this->template->setParameters(
			[
				'items' => $items,
				'empty' => !$this->items,
				'visible' => !$this->hide,
				'selected' => $this->resolveSelected(),
				'disabled' => $this->disableds
			]);

		parent::render();
	}

	public function getTab()
	{
		return $this->resolveSelected();
	}

	public function isDisabled(
		$tab = null)
	{
		return in_array($tab !== null ? $tab : $this->selected, $this->disableds);
	}

	private function resolveSelected()
	{
		if($this->selected){
			return $this->selected;
		}
		return Arrays::firstKey($this->items);
	}
}