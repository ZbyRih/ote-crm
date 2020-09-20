<?php

namespace App\Extensions\Components;

class Breadcrumbs{

	/** @var [] */
	public $title = [];

	public function __construct($home){
		$this->title[] = $home;
	}

	public function addTitle($str){
		if(is_array($str)){
			$this->title = array_merge($this->title, $str);
		}else{
			$this->title[] = $str;
		}
	}

	public function getTitle(){
		return implode(' | ', array_reverse($this->title));
	}
}