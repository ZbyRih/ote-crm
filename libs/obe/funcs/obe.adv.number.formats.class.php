<?php

class OBE_AdvNumberFormat{

	const Numbers_Const_lenght = 10;
	const priceDelim = ',';

	static function FormatIDNumber($objId, $targetLenght = self::Numbers_Const_lenght){
		return OBE_Strings::mb_str_pad($objId, $targetLenght, '0', STR_PAD_LEFT);
	}

	static function pricePrepad($priceStr, $targetLenght = self::Numbers_Const_lenght){
		return OBE_Strings::mb_str_pad($priceStr, $targetLenght, ' ', STR_PAD_LEFT);
	}

	static function pad($str, $targetLenght = self::Numbers_Const_lenght){
		return OBE_Strings::mb_str_pad($str, $targetLenght, ' ');
	}

	static function priceFormat($string, $decimals = 4){
		if($decimals === NULL){
			$decimals = OBE_AppCore::getAppConf('price-decimals');
		}
		$price = number_format($string, $decimals, self::priceDelim, ' ');

		if($decimals > 0){
			$price = preg_replace("/(0{1," . $decimals . "})$/", '', $price);
		}
		if(strpos($price, self::priceDelim) < 0){
			$price .= self::priceDelim . OBE_AppCore::getAppConf('price-without-decs');
		}elseif(substr($price, -1) == self::priceDelim){
			$price .= OBE_AppCore::getAppConf('price-without-decs');
		}
		return $price;
	}

	static function formatPrepadPrice($price){
		return self::pricePrepad(self::priceFormat($price, NULL));
	}

	static function formatNumWithYear($num, $year, $targetLenght = self::Numbers_Const_lenght){
		$yl = mb_strlen($year);
		$padnum = OBE_Strings::mb_str_pad($num, $targetLenght - $yl, '0', STR_PAD_LEFT);
		return $year . $padnum;
	}
}