<?php

class SqlElement{
	public $array = [];

	public function __construct($array = []){
		$this->AddElements($array);
	}

	/**
	 *
	 * @param mixed $array - string nebo array
	 * @param string $after - klic za nejz se maji nove elementy pridat
	 * @return void
	 */
	public function AddElements($array = [], $after = NULL){
		if(!empty($array)){
			if(!is_array($array)){
				if(is_object($array)){
					if(is_a($array, 'SqlElement')){
						$array = $array->array;
					}
				}else{
					$array = [$array];
				}
			}
			if($after === NULL){
				$this->array = array_merge($this->array, $array);
				$after = end($this->array);
				return $after;
			}else{
				if(isset($this->array[$after])){
					$old = $this->array;
					$new = [];

					foreach($old as $k => $v){
						if($k === $after){
							break;
						}
						$new[$k] = $v;
					}
					$el = count($new);

					$this->array = array_slice($old, 0, $el + 1, true);
					$this->array = array_merge($this->array, $array);
					$this->array = array_merge($this->array, array_slice($old, $el + 1, count($old) - $el, true));
					end($array);
					return key($array);
				}elseif($after == -1){
					$this->array = array_merge($array, $this->array);
					return key($array);
				}else{
					throw new Exception('Key ' . $after . ' doesn\'t exist');
				}
			}
		}
	}

	public function DelElementByIndex($indexes){
		$indexes = MArray::AllwaysArray($indexes);
		$keys = array_keys($this->array);
		$delKeys = [];
		foreach ($indexes as $index){
			if(isset($keys[$index])){
				$delKeys[] = $keys[$index];
			}
		}
		foreach($delKeys as $delKey){
			unset($this->array[$delKey]);
		}
	}

	public function DelElement($del_key = NULL){
		if($del_key !== NULL){
			foreach($this->array as $key => $val){
				if(is_numeric($key)){
					if($val == $del_key){
						$item = $this->array[$key];
						unset($this->array[$key]);
						return $item;
					}
				}else{
					if($key == $del_key){
						$item = $this->array[$key];
						unset($this->array[$key]);
						return $item;
					}
				}
			}
		}else{
			return array_pop($this->array);
		}
		return NULL;
	}

	public function SetElementByIndex($index, $value = NULL, $key = NULL){
		$keys = array_keys($this->array);
		$values = array_values($this->array);
		if(isset($keys[$index])){
			$values[$index] = $value;
			if($key){
				$keys[$index] = $key;
			}
			$this->array = array_combine($keys, $values);
		}
	}

	public function SetElelemet($set_key, $value){
		foreach($this->array as $key => $val){
			if(is_numeric($key)){
				if($val == $set_key){
					$this->array[$key] = $value;
					break;
				}
			}else{
				if($key == $set_key){
					$this->array[$key] = $value;
					break;
				}
			}
		}
	}

	/**
	 * dropne elementy a prida mixed
	 * @param mixed $addNew
	 * @param String $after
	 */
	public function ResetElements($addNew = [], $after = NULL){
		$old = $this->drop();
		$this->AddElements($addNew, $after);
		return $old;
	}

	/**
	 * nahradi elementy
	 * @param Array $replaces key => replace
	 */
	public function paramReplace($replaces){
		$keys = array_keys($this->array);
		$vals = array_values($this->array);

		foreach($replaces as $findKey => $replace){
			foreach($vals as &$value){
				if(strpos($value, $findKey)){
					$value = str_replace($findKey, $replace, $value);
				}
			}
			foreach($keys as &$key){
				if(strpos($key, $findKey)){
					$key = str_replace($findKey, $replace, $key);
				}
			}
		}
 		$this->array = array_combine($keys, $vals);
	}

	/**
	 * zahodi vsechny elementy a vrati puvodní
	 */
	public function drop(){
		$old = $this->array;
		$this->array = [];
		return $old;
	}

	/**
	 *
	 * @param SqlElement $sqlElement
	 */
	public function join($sqlElement){
		$this->array = array_merge($this->array, $sqlElement->array);
	}
}