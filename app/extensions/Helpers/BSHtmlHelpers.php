<?php

namespace App\Extensions\Helpers;

use App\Extensions\Utils\Html;

class BSHtmlHelpers{

	public static function checkBox($val){
		if($val){
			return Html::el('i')->class('fa fa-check-square-o');
		}else{
			return Html::el('i')->class('fa fa-square-o');
		}
	}

	public static function button($text, $class, $link, $ico = null, $el = 'a'){
		$btn = Html::el($el)->class('btn btn-' . $class);

		if($ico){
			$btn->addHtml(Html::el('i')->class('fa fa-' . $ico))
				->addHtml('&nbsp;');
		}

		$btn->addText($text)->setAttribute('href', $link);

		return $btn;
	}

	public static function label($label, $color = null){
		$sp = Html::el('span')->class('label label-info')->setText($label);

		if($color){
			$sp->addAttributes([
				'style' => 'background-color: #' . $color . ';'
			]);
		}

		return $sp;
	}

	public static function badge($label, $color = null){
		$sp = Html::el('span')->class('badge badge-info')->setText($label);

		if($color){
			$sp->addAttributes([
				'style' => 'background-color: #' . $color . ';'
			]);
		}

		return $sp;
	}
}