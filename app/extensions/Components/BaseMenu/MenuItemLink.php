<?php

namespace App\Extensions\Components\Menu;

class MenuItemLink{

	private $link;

	public $modul;

	public function __construct($link, $modul){
		$this->link = $link;
		$this->modul = $modul;
	}

	public function __toString(){
		return $this->link;
	}
}