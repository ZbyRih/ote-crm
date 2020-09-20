<?php

class ModulSession extends SArray{

	private $key;

	public function __construct($sessionKey){
		$this->key = $sessionKey;
		parent::__construct(OBE_Session::read($sessionKey));
	}

	public function __destruct(){
		$this->flush();
	}

	public function flush(){
		OBE_Session::write($this->key, $this->data);
	}
}