<?php

namespace App\Extensions\App;

use App\Extensions\Utils\Strings;

class LatteFilters{

	public static function common($filter, $value){
		if(method_exists(__CLASS__, $filter)){
			$args = func_get_args();
			array_shift($args);
			$ret = call_user_func_array([
				__CLASS__,
				$filter
			], $args);
			if($ret === null){
				return '';
			}
			return $ret;
		}
		throw new \Exception('Neexistující filter: \'' . $filter . '\'');
	}

	public static function uno($s, $null){
		return ($s) ? $s : $null;
	}

	public static function bin($s, $a, $b){
		return ($s) ? $a : $b;
	}

	public static function nparam($p, $param, $null = null){
		return ($p) ? $p[$param] : $null;
	}

	public static function nempty($s, $p){
		return !(empty($s)) ? $s : $p;
	}

	public static function nisset($s, $p){
		return !(isset($s)) ? $s : $p;
	}

	public static function price_curr($c, $dec = 2, $space = '&nbsp;'){
		return number_format($c, $dec, ',', $space) . $space . 'Kč';
	}

	public static function price($c, $dec = 2, $space = '&nbsp;'){
		return number_format($c, $dec, ',', $space);
	}

	public static function dformat($c, $format = 'j.n. Y'){
		if($c){
			return $c->format($format);
		}
		return null;
	}

	public static function html_ent($obsah){
		return htmlentities($obsah, ENT_QUOTES, 'UTF-8');
	}

	public static function round($v, $d = 2){
		return round($v, $d);
	}

	public static function hlnondef($v, $def){
		return ($v != $def) ? '<b class="text-primary">' . $v . '</b>' : $v;
	}

	public static function ibytes($kibytes, $precision = 2){
		static $units = array(
			'B',
			'KiB',
			'MiB',
			'GiB',
			'TiB',
			'PiB'
		);
		$unit = '';
		$kibytes = round($kibytes);
		foreach($units as $unit){
			if(abs($kibytes) < 1024 || $unit === end($units)){
				break;
			}
			$kibytes = $kibytes / 1024;
		}
		return round($kibytes, $precision) . ' ' . $unit;
	}

	public static function firstLower($s){
		return Strings::firstLower($s);
	}
}