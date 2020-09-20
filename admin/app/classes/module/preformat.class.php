<?php

class PreformatClass extends ViewElementClass{

	var $name = null;
	var $data = null;

	var $format = 'pre';

	/**
	 * @param string $type
	 */
	public function __construct($type = null){
		parent::__construct('preformat');
	}

	public function set($name, $data){
		$this->name = $name;
		$this->data = $data;
	}

	/**
	 * {@inheritDoc}
	 * @see ViewElementClass::getElementView()
	 */
	public function getElementView(){
		return $this;
	}

	/**
	 * @param string $format - pre|html
	 */
	public function setFormat($format){
		$this->format = $format;
	}
}