<?php

class OBE_Timer{
	public $start;
	public $end;
	public $max;

	private $timers;
	private $lasts;

	public function init(){
		$this->max = ini_get('max_execution_time');
		$this->start = microtime(true);
		$this->end = null;
		return $this;
	}

	public function finish(){
		$this->end = microtime(true);
		return $this;
	}

	public function elapsed(){
		return ((($this->end)? $this->end: microtime(true)) - $this->start);
	}

	public function elapsedf(){
		return number_format(((($this->end)? $this->end: microtime(true)) - $this->start), 4, '.', '');
	}

	public function have($off = 0){
		return (bool)((OBE_Log::$timer->elapsed() + $off) < $this->max);
	}

	public function timer($key = 'base'){
		if(isset($this->timers[$key])){
			return number_format(microtime(true) - $this->timers[$key], 4, '.', '');
		}else{
			$this->lasts[$key] = $this->timers[$key] = microtime(true);
		}
		return $this;
	}

	public function last($key = 'base'){
		if(isset($this->last[$key])){
			return number_format(microtime(true) - $this->lasts[$key], 4, '.', '');
		}else{
			$this->lasts[$key] = microtime(true);
		}
	}
}