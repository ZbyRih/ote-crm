<?php

namespace App\Extensions\Components;

trait TBaseGridPresenterExtension{

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nette\Application\UI\Control::flashMessage()
	 */
	public function flashMessage($message, $type = 'info'){
		$this->presenter->flashMessage($message, $type);
	}

	public function isAllowed($privilege){
		return $this->presenter->isAllowed($privilege);
	}
}