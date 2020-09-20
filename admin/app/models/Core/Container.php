<?php
namespace App\Core;

class Container{

	private $data = [];

	public function __construct(){
	}

	public function has(){
		return !empty($this->data) ? false : true;
	}

	public function get(){
		return $this->data;
	}

	public function add($add){
		$this->data[] = $add;
	}
}