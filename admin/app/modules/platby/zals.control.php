<?php


class ZalsControl{

	var $zals = [];

	var $last = false;

	var $presne = true;

	public function __construct($zals){
		$this->zals = $zals;
		$this->prepare();
	}

	public function prepare(){
		$preplatky = 0;
		while($z = current($this->zals)){
			if($z['uhr'] != 0){
				$preplatky += $z['preplatek'];
				if($z['preplatek'] > 0){
					$z['preplatek'] = 0;
				}
				$this->zals[$this->currKey()] = $z;
				$this->next();
			}else{
				$zbytek = $this->uhr($preplatky);
				if($zbytek > 0){
					$this->zals[$this->currKey()]['preplatek'] += $zbytek;
				}
				break;
			}
		}
	}

	public function uhr($kolik, $kdy = null){
		while($kolik != 0 && ($o = current($this->zals))){

			$kolik += $o['preplatek'];
			$o['preplatek'] = 0;

			if($o['uhr'] != 1){
				if($kolik > ($o['vyse'] - $o['uhrazeno'])){
					$this->presne = false;
					$kolik -= ($o['vyse'] - $o['uhrazeno']);
					$o['uhrazeno'] = $o['vyse'];
					$o['uhr'] = 1;
					$o['dne'] = $kdy;
					$this->zals[$this->currKey()] = $o;
					$this->next();
				}else if($kolik == ($o['vyse'] - $o['uhrazeno'])){
					$o['uhrazeno'] = $o['vyse'];
					$o['uhr'] = 1;
					$o['dne'] = $kdy;
					$kolik = 0;
					$this->zals[$this->currKey()] = $o;
					$this->next();
				}else if($kolik < ($o['vyse'] - $o['uhrazeno'])){
					$this->presne = false;
					$o['uhrazeno'] += $kolik;
					$kolik = 0;
					$this->zals[$this->currKey()] = $o;
				}
			}
		}

		return $kolik;
	}

	public function next(){
		if(!next($this->zals)){
			$this->last = true;
		}
	}

	public function current(){
		if($this->last){
			return end($this->zals);
		}else{
			return current($this->zals);
		}
	}

	public function currKey(){
		if($this->last){
			end($this->zals);
		}
		return key($this->zals);
	}

	public function setPreplatek($preplatek){
		$this->zals[$this->currKey()]['preplatek'] = $preplatek;
	}

	public function isLastUhr(){
		if($this->last){
			$z = end($this->zals);
			if($z['uhr']){
				return true;
			}
		}
		return false;
	}
}