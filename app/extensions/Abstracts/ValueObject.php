<?php
namespace App\Extensions\Abstracts;

abstract class ValueObject{

	abstract public function __toString();

	public function __get($name){
		throw new \RuntimeException('ValueObject didnt allow read/write of property`s');
	}

	public function __set($name, $value){
		throw new \RuntimeException('ValueObject didnt allow read/write of property`s');
	}
}