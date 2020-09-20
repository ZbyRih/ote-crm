<?php

namespace App\Extensions\Components\Controls;

class MultiSelectBox extends \Nette\Forms\Controls\MultiSelectBox{

	/**
	 * Returns selected values.
	 * @return array
	 */
	public function getSelectedItems(){
		if($this->checkAllowedValues){
			return array_intersect_key($this->items, array_flip($this->value));
		}else{
			return $this->value;
		}
	}

	/**
	 * Returns selected keys.
	 * @return array
	 */
	public function getValue(){
		if($this->checkAllowedValues){
			return array_values(array_intersect($this->value, array_keys($this->items)));
		}else{
			return $this->value;
		}
	}
}