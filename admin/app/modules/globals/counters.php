<?php

class Counters{

	const COUNTERS = 'counters';

	private static $counters = [
		'fakskup' => 'CISELNIK_FAK_SKUP',
		'fa' => 'CISELNIK_FAKTUR',
		'prijmove_platby' => 'CISELNIK_DOKLAD_PRIJEM'
	];

	private static $cache = [];

	private static $masks = [];

	public static function init(){
		$vd = OBE_AppCore::LoadVar(self::COUNTERS);

		foreach(self::$counters as $key => $set_key){
			self::$masks[$key] = AdminApp::$settings[$set_key];

			if(!isset($vd[$set_key])){
				$vd[$set_key] = self::initVal(self::$masks[$key]);
			}

			self::$cache[$key] = $vd[$set_key];
		}
	}

	private static function initVal($mask){
		if(!$mask){
			return '0';
		}

		if(strpos($mask, 'P')){
			return [];
		}

		$ret = '';
		$lastni = -1;

		for($i = 0; $i < strlen($mask); $i++){
			$c = $mask{$i};
			if(is_numeric($c)){
				$ret .= $c;
			}else if($c == 'Y'){
				if(substr($mask, $i, 4) == 'YYYY'){
					$ret .= date('Y');
				}else{
					$y = date('y');
					$ret .= $y{1};
				}
			}elseif($c == 'N'){
				$ret .= '0';
				$lastni = $i;
			}
		}

		if($lastni != -1){
			$ret{$lastni} = '0';
		}

		return $ret;
	}

	public static function get($key, $pp = null){
		if(!self::$cache){
			self::init();
		}

		if($pp){
			if(isset(self::$cache[$key]) && isset(self::$cache[$key][$pp])){
				return self::$cache[$key][$pp];
			}else{
				throw new OBE_Exception('V číselnících klíč `' . $key . '` pro ' . $pp . ' neexistuje.');
			}
		}else if(isset(self::$cache[$key])){
			return self::$cache[$key];
		}else{
			throw new OBE_Exception('V číselnících klíč `' . $key . '` neexistuje.');
		}
	}

	public static function getPP($key, $val){
		if(!self::$cache){
			self::init();
		}

		if(isset(self::$masks[$key])){
			$m = self::$masks[$key];

			if(preg_match_all('/P+/', $m, $matches)){
				$a = reset($matches);
				$pp = reset($a);
			}

			if($i = strpos($m, 'P')){
				return substr($val, $i, strlen($pp));
			}
		}else{
			throw new OBE_Exception('V maskách klíč `' . $key . '` neexistuje.');
		}
	}

	public static function set($key, $val, $pp = null){
		if(!self::$cache){
			self::init();
		}

		if($pp){
			if(isset(self::$cache[$key]) && isset(self::$cache[$key][$pp])){
				self::$cache[$key][$pp] = $val;
				self::update();
			}else{
				throw new OBE_Exception('V číselnících klíč `' . $key . '` ' . $pp . ' neexistuje.');
			}
		}else{
			if(isset(self::$cache[$key])){
				self::$cache[$key] = $val;
				self::update();
			}else{
				throw new OBE_Exception('V číselnících klíč `' . $key . '` neexistuje.');
			}
		}
	}

	public static function setMask($key, $mask){
		self::$masks[$key] = $mask;
	}

	public static function getNext($key, $pp = null){
		$curr = self::get($key);

		$mask = self::$masks[$key];

		if(strpos($mask, 'P') && $pp){
			$mask = str_replace(str_repeat('P', strlen($pp)), $pp, self::$masks[$key]);
		}

		if($pp && !isset($curr[$pp])){
			$curr = self::initVal($mask);
		}else if($pp){
			$curr = $curr[$pp];
		}

		$curr = self::nextByMask($curr, $mask, $pp);

		if($pp){
			self::$cache[$key][$pp] = $curr;
		}else{
			self::$cache[$key] = $curr;
		}

		self::update();
		return $curr;
	}

	private static function nextByMask($cis, $mask){
		if(!$mask){
			return floatval($cis) + 1;
		}

		$new_year = false;

		if(preg_match_all('/Y+/', $mask, $matches)){

			$a = reset($matches);
			$ym = reset($a);

			$yt = date('y');
			if($ym == 'YYYY'){
				$yt = date('Y');
			}

			$pos = strpos($mask, $ym);

			$yc = substr($cis, $pos, strlen($ym));

			if($yc != $yt){
				$new_year = true;

				$mask = substr($mask, 0, $pos) . $yt . substr($mask, $pos + strlen($yt));
			}else{
				$mask = substr($mask, 0, $pos) . $yc . substr($mask, $pos + strlen($yc));
			}
		}

		if(preg_match_all('/N+/', $mask, $matches)){

			$m = reset($matches);
			$cm = reset($m);
			$pos = strpos($mask, $cm);
			$cc = substr($cis, $pos, strlen($cm));

			if($new_year){
				$cis = substr($mask, 0, $pos) . str_pad('1', strlen($cm), '0', STR_PAD_LEFT) . substr($mask, $pos + strlen($cm));
			}else{
				$cis = substr($mask, 0, $pos) . str_pad(floatval($cc) + 1, strlen($cm), '0', STR_PAD_LEFT) . substr($mask, $pos + strlen($cm));
			}
		}

		return $cis;
	}

	private static function update(){

		$vd = OBE_AppCore::LoadVar(self::COUNTERS);

		foreach(self::$cache as $key => $val){
			$vd[self::$counters[$key]] = $val;
		}

		OBE_AppCore::SaveVar(self::COUNTERS, $vd);
	}
}