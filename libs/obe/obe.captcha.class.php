<?php
class captchaClass{
	public static function getkey (){
		return self::generateKey(6, '0123456789ABCDEF');
	}

	public static function generateKey($length, $pattern){
		$key = '';
		$valLim = strlen($pattern) - 1;
		srand(((int)((double)microtime()*1000003)));
		for($i = 0; $i < $length; $i++){
			$r = rand(0, $valLim);
			$key .= $pattern{$r};
		}
		return $key;
	}

	public static function validatekey ($keyin, $keyout){
		$inp = ["0","7","A","B","C","D","E","F"];
		$out = ["3","1","2","4","8","6","5","9"];
		$key = str_replace($inp,$out,strtoupper(substr(md5("keygenerator" . $keyin . "kg"),7,3)));
		return $key==$keyout;
	}
}