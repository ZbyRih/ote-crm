<?php
namespace App\Models\Values;

use App\Extensions\Abstracts\ValueObject;

class TimeValue extends ValueObject{

	/** @var integer */
	private $time;

	public function __construct($time = 0){
		if(!$time){
			$this->time = 0;
			return;
		}

		if(is_int($time)){
			$this->time = $time;
			return;
		}

		$e = explode(':', $time);
		if(count($e) < 3 || !preg_match('/^\d{2,}\:\d{2,}\:\d{2,}$/', $time)){
			throw new \InvalidArgumentException('Ivalid format for Time, valid is 99:99:99, \'' . $time . '\' given.');
		}

		$this->time = ($e[0] * 3600) + ($e[1] * 60) + $e[2];
	}

	public function __toString(){
		return $this->toHMS();
	}

	public function toSeconds(){
		return $this->time;
	}

	public function toHours(){
		return $this->time / 3600;
	}

	public function toHMS(){
		$h = floor($this->time / 3600);
		$m = floor(($this->time / 60) % 60);
		$s = $this->time % 60;
		return str_pad($h, 2, '0', STR_PAD_LEFT) . ':' . str_pad($m, 2, '0', STR_PAD_LEFT) . ':' . str_pad($s, 2, '0', STR_PAD_LEFT);
	}

	public function toHM(){
		$h = floor($this->time / 3600);
		$m = floor(($this->time / 60) % 60);
		return str_pad($h, 2, '0', STR_PAD_LEFT) . ':' . str_pad($m, 2, '0', STR_PAD_LEFT);
	}

	public function toMS(){
		$m = floor(($this->time / 60) % 60);
		$s = $this->time % 60;
		return str_pad($m, 2, '0', STR_PAD_LEFT) . ':' . str_pad($s, 2, '0', STR_PAD_LEFT);
	}
}