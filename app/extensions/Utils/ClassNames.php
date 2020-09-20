<?php
namespace App\Extensions\Utils\Helpers;

class ClassNames{

	/** @var boolean[] */
	private $classes = [];

	public function __construct(array $classes){
		$this->classes = $classes;
	}

	public static function fromString($classes){
		return new ClassNames(array_fill_keys(explode(' ', $classes), true));
	}

	public function add(array $classes){
		$this->classes = $this->classes + $classes;
		return $this;
	}

	public function set($class, $val){
		$this->classes[$class] = $val;
		return $this;
	}

	public function has($class){
		return $this->classes[$class];
	}

	public function __toString(){
		return implode(' ', array_keys(array_filter($this->classes, function ($v){
			return $v;
		})));
	}
}