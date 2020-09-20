<?php

class OBE_DynVar{
	const GET = 'get'; //klic vstupu z url
	const POST = 'post'; // klic vstupu z postu
	const SES = 'session'; //klic sesny pro ulozeni

	private $key = null;
	private $req = null;
	private $def = [];
	private $src = [];

	static private $read_order = [self::GET, self::POST, self::SES];
 	static private $fces = [self::GET => 'Get', self::POST => 'Post', self::SES => 'Session'];

	/**
	 * @param string $key
	 * @param array $src
	 * @param mixed $def
	 * @param array $req
	 */
	public function __construct($key, $src = [], $def = null, $req = []){
		$this->key = $key;
		$this->src = $src;
		$this->def = $def;
		$this->req = $req;
	}

	public function init($arr){
		if(isset($arr['order'])){
			$this->src = $arr['order'];
		}
		if(isset($arr['def'])){
			$this->def = $arr['def'];
		}
		if(isset($arr['req'])){
			$this->req = $arr['req'];
		}
		if(isset($arr['key'])){
			$this->key = $arr['key'];
		}
		return $this;
	}

	public function setDef($def = null){
		$this->def = $def;
		return $this;
	}

	public function setReq($req = null){
		$this->req = $req;
		return $this;
	}

	public function get(){
		$val = null;
		$all = [];
		$srcs = (empty($this->src))? array_keys(self::$fces) : $this->src;

		foreach ($srcs as $fce){
			$val = $this->{'get' . self::$fces[$fce]}();
			if($val !== null){
				break;
			}
		}

		if($val !== null && !empty($this->req)){
			if(!in_array($val, $this->req)){
				$val = reset($this->req);
			}
		}

		if($val === null){
			if($this->def){
				$val = $this->def;
			}else if(!empty($this->req)){
				$val = reset($this->req);
			}
		}

		return $val;
	}

	private function getGet(){
		return OBE_Http::getGet($this->key);
	}

	private function getPost(){
		return OBE_Http::getPost($this->key);
	}

	private function getSession(){
		return OBE_Session::read($this->key);
	}
}