<?php

namespace App\Extensions\Utils;

class Strings extends \Nette\Utils\Strings{

	public static function splitToHtml(
		$s,
		$chunk)
	{
		if($s){
			$a = str_split($s, $chunk);
			return Html::arrToSpan($a);
		}
		return '';
	}

	public static function beginWith(
		$haystack,
		$needle)
	{
		return substr($haystack, 0, strlen($needle)) === $needle;
	}
}