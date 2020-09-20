<?php

class OBE_Math{

	public static function Percent($val, $percent){
		return $val * ((100 - $percent) / 100);
	}

	public static function trimcoef($number){
		//		return $number;
		return number_format($number, 4);
	}

	public static function priceWDphToPriceDphFree($priceWithDph, $dph){
		return ($priceWithDph - self::dphFromPriceWithDph($priceWithDph, $dph));
	}

	public static function priceDphFreeToPriceWDph($priceDphFree, $dph){
		return ($priceDphFree + self::dphFromPriceDphFree($priceDphFree, $dph));
	}

	public static function dphFromPriceWithDph($priceWithDph, $dph){
		return ($priceWithDph * /*self::trimcoef(*/((float) $dph / (100.00000 + (float) $dph)))/*)*/;
	}

	public static function dphFromPriceDphFree($priceWithoutDph, $dph){
		$some = $priceWithoutDph * /*self::trimcoef(*/(float) $dph / 100.00000/*)*/;
		return $some;
	}

	public static function getPriceWDphFromCrmPrice($price, $dph){
		if(isset(EnviromentConfig::$global['crmPPriceWDPH']) && EnviromentConfig::$global['crmPPriceWDPH']){
			return $price;
		}else{
			return self::priceDphFreeToPriceWDph($price, $dph);
		}
	}

	public static function getPriceDphFreeFromCrmPrice($price, $dph){
		if(isset(EnviromentConfig::$global['crmPPriceWDPH']) && EnviromentConfig::$global['crmPPriceWDPH']){
			return self::priceWDphToPriceDphFree($price, $dph);
		}else{
			return $price;
		}
	}

	public static function non_numeric($item){
		return (!is_numeric($item));
	}

	public static function correctFloatNumber($float){
		if(strpos($float, '-') !== false){
			$float = '-' . str_replace('-', '', $float);
		}

		if(preg_match('/^ *(\d+ ?,? ?)* ?\.{1} ?(\d*)? *$/', $float)){
			return str_replace([
				',',
				' '
			], [
				'',
				''
			], $float);
		}elseif(preg_match('/^ *(\d+ ?\.? ?)* ?,{1} ?(\d*)? *$/', $float)){
			return str_replace([
				'.',
				' ',
				','
			], [
				'',
				'',
				'.'
			], $float);
		}elseif(substr_count($float, ',') > 1){
			return str_replace([
				',',
				' '
			], [
				'',
				''
			], $float);
		}
		return str_replace([
			',',
			' '
		], [
			'.',
			''
		], $float);
	}

	public static function correctNumber($number){
		return str_replace(' ', '', $number);
	}

	public static function removeCurrency($val){
		return str_ireplace([
			' Kč',
			'Kč'
		], [
			'',
			''
		], $val);
	}

	public static function getFormatIniLimits($aM){
		$aM = strtolower($aM);
		$iAM = (int) $aM;
		if(stripos($aM, 'g')){
			$iAM = $iAM * 1073741824;
		}elseif(stripos($aM, 'm')){
			$iAM = $iAM * 1048576;
		}elseif(stripos($aM, 'k')){
			$iAM = $iAM * 1024;
		}
		return $iAM;
	}

	public static function getFormatedBytes($bytes, $descs = 0, $delim = ''){
		$fm = [
			'',
			'Ki',
			'Mi',
			'Gi'
		];
		$it = 0;
		while($bytes > 1024){
			$bytes = $bytes / 1024;
			$it++;
		}
		return (($descs) ? number_format($bytes, $descs) : $bytes) . $delim . $fm[$it];
	}
}