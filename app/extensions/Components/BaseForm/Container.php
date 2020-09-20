<?php

namespace App\Extensions\Components;

class Container extends \Nette\Forms\Container{

	use TContainerControls;

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nette\Forms\Container::addContainer()
	 */
	public function addContainer($name){
		$control = new self();
		$control->currentGroup = $this->currentGroup;
		if($this->currentGroup !== NULL){
			$this->currentGroup->add($control);
		}
		return $this[$name] = $control;
	}
}