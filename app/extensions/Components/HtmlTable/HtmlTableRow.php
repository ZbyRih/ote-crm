<?php

namespace App\Extensions\Components;

use App\Extensions\Utils\Html;

class HtmlTableRow extends Html{

	private $cells;

	public function __construct()
	{
		$this->setName('tr');
	}

	public function addCell(
		$label)
	{
		$this->cells[] = $c = Html::el('td')->setText($label);
		return $c;
	}

	public function compile(
		$heads)
	{
		$h = reset($heads);
		foreach($this->cells as $c){
			$c->attrs = $h->attrs;
			$this->addHtml($c);
			$h = next($heads);
		}
		return $this;
	}
}