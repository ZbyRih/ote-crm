<?php
namespace App\Extensions\Utils\Helpers;

class ArrayHash extends \Nette\Utils\ArrayHash{

	public function set($arr){
		foreach($arr as $k => $v){
			$this->$k = $v;
		}
		return $this;
	}

	public function json(){
		return json_encode($this);
	}
}