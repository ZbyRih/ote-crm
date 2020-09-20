<?php

namespace App\Extensions\Utils;

use App\Extensions\Helpers\Helpers;
use Traversable;

class Arrays extends \Nette\Utils\Arrays{

	public static function filter(
		$a,
		$k)
	{
		return array_intersect_key((array) $a, array_fill_keys($k, 1));
	}

	public static function diff(
		$a,
		$k)
	{
		return array_diff_key((array) $a, array_fill_keys($k, 1));
	}

	public static function repl(
		$a1,
		$a2,
		$keys)
	{
		return array_replace($a1, array_intersect_key($a2, array_flip($keys)));
	}

	public static function remove(
		$key,
		$arr)
	{
		if(!array_key_exists($key, $arr)){
			return null;
		}
		$removed = $arr[$key];
		unset($arr[$key]);
		return $removed;
	}

	public static function shiftNull(
		$null,
		$a)
	{
		return ([
			null => $null
		] + $a);
	}

	public static function exists(
		$a,
		$k)
	{
		if(array_key_exists($k, $a)){
			return true;
		}else{
			foreach($a as $s){
				if(is_array($s) && array_key_exists($k, $s)){
					return true;
				}
			}
		}
		return false;
	}

	public static function isEq(
		$a1,
		$a2,
		$keys)
	{
		foreach($keys as $k){
			if(!array_key_exists($k, $a1) || !array_key_exists($k, $a2) || $a1[$k] != $a2[$k]){
				return false;
			}
		}

		return true;
	}

	public static function isEmptyRetDef(
		$a,
		$def)
	{
		if(empty($a)){
			return $def;
		}
		return $a;
	}

	public static function isKNotExistsRetFirstKey(
		$k,
		$a)
	{
		$a = self::kvFromV($a);
		if(!array_key_exists($k, $a)){
			return key($a);
		}
		return $a;
	}

	public static function first(
		$a)
	{
		reset($a);
		return current($a);
	}

	public static function firstNotNull(
		$a)
	{
		foreach($a as $k => $v){
			if($v !== null){
				return $v;
			}
		}
		return null;
	}

	public static function firstKey(
		$a)
	{
		reset($a);
		return key($a);
	}

	public static function lastKey(
		$a)
	{
		end($a);
		return key($a);
	}

	public static function nextKey(
		$k,
		$a)
	{
		$keys = array_keys($a);
		$_k = current($keys);
		while($_k != $k && $_k !== false){
			$_k = next($keys);
		}

		return next($keys);
	}

	public static function keysAfter(
		$k,
		$a)
	{
		$ks = array_keys($a);
		$k = array_search($k, $ks);

		return collection($ks)->filter(function (
			$v,
			$_k) use (
		$k){
			return $_k > $k;
		})->toArray();
	}

	public static function kvFromA(
		$a)
	{
		if(is_array($a)){
			return array_combine($a, $a);
		}
		return [];
	}

	public static function nullEmptyStrings(
		$a)
	{
		foreach($a as $k => $v){
			if($v === ''){
				$a[$k] = null;
			}
		}
		return $a;
	}

	public static function toArray(
		$v)
	{
		return is_array($v) ? $v : [
			$v
		];
	}

	public static function toFloats(
		$a)
	{
		$r = [];
		foreach($a as $k => $f){
			$r[$k] = Helpers::parseFloat($f);
		}
		return $r;
	}

	public static function toArrayTrav(
		$a)
	{
		return (array) array_map(function (
			&$e){
			if($e instanceof Traversable || is_array($e)){
				$e = self::toArrayTrav($e);
			}
			return $e;
		}, (array) $a);
	}
}