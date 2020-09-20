<?php

namespace App\Extensions\Helpers;

use App\Extensions\Utils\Html;

class Formaters{

	public static function num($val, $decs = 2){
		return number_format($val, $decs, ',', '.');
	}

	public static function meters($val){
		return number_format($val, 0, ',', '.');
	}

	public static function price($float, $decs = 2, $pad = 'CZK'){
		return number_format($float, $decs, ',', '.') . ($pad ? ' ' . $pad : '');
	}

	public static function tel($str){
		$str = trim($tel = $str, '+');

		$e = Html::arrToSpan(str_split($str, 3));

		if($tel !== $str){
			$e->insert(0, '+');
		}

		return $e;
	}

	public static function date(\DateTimeInterface $date){
		return $date->format('d. m. Y');
	}

	public static function time(\DateTimeInterface $date){
		return $date->format('H:i');
	}

	public static function dateTime(\DateTimeInterface $date){
		return $date->format('d. m. Y H:i');
	}

	/**
	 * Čislo OP
	 */
	public static function op($str){
		if(!$str){
			return '';
		}

		return Html::arrToSpan(str_split($str, 3));
	}

	/**
	 * Rodné čislo
	 */
	public static function rc($str){
		if(!$str){
			return '';
		}

		return Html::arrToSpan([
			substr($str, 0, 6),
			substr($str, 6, 4)
		]);
	}

	/**
	 * čislo ŘP
	 */
	public static function rp($str){
		if(!$str){
			return '';
		}

		return Html::arrToSpan([
			substr($str, 0, 2),
			substr($str, 2, 3),
			substr($str, 5, 3)
		]);
	}

	/**
	 * auto RZ
	 */
	public static function rz($str, $tag = 'span'){
		$a = substr($str, 0, 3);
		$b = substr($str, 3, 4);

		if(!$tag){
			return $a . ' ' . $b;
		}

		return Html::el($tag)->addText($a)
			->addHtml('&nbsp;')
			->addText($b);
	}

	public static function ic($str){
		return Html::arrToSpan(str_split($str, 3));
	}

	public static function dic($str){
		if(!$str){
			return '';
		}

		if(strtolower(substr($str, 0, 2)) == 'cz'){
			$str = substr($str, 2);
		}
		return Html::arrToSpan(str_split($str, 3))->insert(0, 'CZ')->insert(1, '&nbsp;');
	}
}