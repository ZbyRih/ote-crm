<?php

namespace App\Components;

class OldBaseComponent implements IViewElement{

	use \Nette\SmartObject;

	protected function create(
		$info)
	{
	}

	public function getElementView()
	{
		return null;
	}
}