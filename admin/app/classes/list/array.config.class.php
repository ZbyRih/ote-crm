<?php


class ArrayConfigClass{

	function __construct(){
	}

	function configByArray($configArray = NULL){

		$configArray = MArray::AllwaysArray($configArray);
		foreach($configArray as $varName => $varValue){
			if(property_exists($this, $varName)){
				$this->{$varName} = $varValue;
			}else{
				dd(get_object_vars($this));
				throw new OBE_Exception('Property \'' . $varName . '\' at ' . get_class($this) . ' doesn`t exist!');
			}
		}
	}

	function setConfig($configItem, $value){
		if(property_exists($this, $configItem)){
			$oldValue = $this->{$configItem};
			$this->{$configItem} = $value;
			return $oldValue;
		}

		dd(get_object_vars($this));
		throw new OBE_Exception('Property \'' . $configItem . '\' at ' . get_class($this) . ' doesn`t exist!');
	}
}
