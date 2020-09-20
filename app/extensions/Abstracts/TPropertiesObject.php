<?php

namespace App\Extensions\Abstracts;

use App\Extensions\Utils\ReflectionClass;

class PropertyDontExists extends \Exception{
}

trait TPropertiesObject{

	/** @var [] */
	private $_data;

	/** @var string */
	private $_class;

	/** @var [] */
	private static $struct = [];

	public function __wakeup(){
		$this->initStruct();
	}

	public function init(){
		$this->initStruct();
		$this->initValues();
		return $this;
	}

	public function set($data){
		foreach($data as $k => $v){
			if(array_key_exists($k, static::$struct[$this->_class])){
				$this->$k = $v;
			}
		}
		return $this;
	}

	private function initValues(){
		foreach(static::$struct[$this->_class] as $k => $v){
			$this->_data[$k] = null;
		}
	}

	private function initStruct(){
		$this->_class = get_called_class();
		if(array_key_exists($this->_class, static::$struct)){
			return;
		}
		$props = (new ReflectionClass($this))->getClassProperties();
		static::$struct[$this->_class] = array_flip($props);
	}

	public function __set($name, $value){
		if(!array_key_exists($name, static::$struct[$this->_class])){
			throw new PropertyDontExists('Property `' . $name . '` dont exists in class `' . $this->_class . '`');
		}

		$this->_data[$name] = $value;
	}

	public function __get($name){
		if(!array_key_exists($name, static::$struct[$this->_class])){
			throw new PropertyDontExists('Property `' . $name . '` dont exists in class `' . $this->_class . '`');
		}

		return array_key_exists($name, $this->_data) ? $this->_data[$name] : null;
	}

	public function getProperties(){
		if(!array_key_exists($this->_class, static::$struct)){
			return [];
		}

		return array_keys(static::$struct[$this->_class]);
	}

	public function toArray(){
		return $this->_data;
	}
}