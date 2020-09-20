<?php

namespace App\Extensions\Helpers;

class SessionSection{

	public static function extract(\Nette\Http\SessionSection $section, $variables){
		$ret = [];
		foreach($variables as $v){
			$ret[$v] = $section->$v;
		}
		return $ret;
	}
}