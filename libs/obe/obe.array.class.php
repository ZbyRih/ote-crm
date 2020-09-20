<?php
class OBE_Array implements ArrayAccess, Serializable, Countable, Iterator{
	protected $_data;
	private $_current;

	public function __construct ($input = []){
		$this->setData($input);
	}

	public function getData(){
		return $this->_data;
	}

	public function setData($input){
		$this->_data = $input;
	}

	public function offsetExists($index) {
		return isset($this->_data[$index]);
	}

	public function offsetGet($index){
		if($this->offsetExists($index)){
			return $this->_data[$index];
		}else{
			throw new Exception('Index ' . $index . ' in array ' . get_class($this) . ' not exist');
		}
	}

	public function offsetSet($index, $newval){
		if(is_null($index)){
			$this->_data[] = $newval;
		}else{
			$this->_data[$index] = $newval;
		}
	}

	public function offsetUnset($index){
		unset($this->_data[$index]);
	}

	public function serialize(){
		return serialize($this->_data);
	}

	public function unserialize($serialized){
		$this->_data = unserialize($serialized);
	}

	public function count(){
		return count($this->_data);
	}

	public function current(){
		return $this->_data[$this->_current];
	}

	public function key(){
		return $this->_current;
	}

	public function next(){
		$val = next($this->_data);
		$this->_current = key($this->_data);
		return $val;
	}

	public function rewind(){
		reset($this->_data);
		$this->_current = key($this->_data);
	}

	public function valid(){
		return ($this->_current !== null && $this->_current !== false);
	}

	public function asArray(){
		return $this->_data;
	}

	public function asort(){
		return asort($this->_data);
	}

	public function ksort(){
		return ksort($this->_data);
	}

	public function keys(){
		return array_keys($this->_data);
	}

	public function values(){
		return array_values($this->_data);
	}

	public function combine($keys, $values){
		$this->_data = array_combine($keys, $values);
	}
}