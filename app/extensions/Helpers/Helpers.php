<?php

namespace App\Extensions\Helpers;

use Nette\Security\Passwords;

class Helpers{

	public static function passwordHash(
		$pass)
	{
		if($pass){
			return Passwords::hash($pass);
		}
		return null;
	}

	public static function parseFloat(
		$v)
	{
		if($v === ''){
			return 0.0;
		}
		$v = str_replace(' ', '', $v);
		if(strpos($v, '.') !== false && strpos($v, ',') === false){
			return floatval($v);
		}elseif(strpos($v, '.') === false && strpos($v, ',') !== false){
			return floatval(str_replace(',', '.', $v));
		}else{
			return floatval($v);
		}
	}
}