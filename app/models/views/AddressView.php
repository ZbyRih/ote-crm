<?php

namespace App\Models\Views;

use App\Extensions\Utils\Html;
use App\Models\Orm\Address\AddressEntity;

class AddressView{

	public static function addr(
		$a)
	{
		$cpo = $a['cp'];

		if(!empty($a['co'])){
			$cpo = $cpo . (!empty($cpo) ? '/' : '') . $a['co'];
		}

		$cpop = (empty($cpo) ? '' : ' ') . $cpo;

		return $a['city'] . ', ' . $a['street'] . $cpop;
	}

	public static function getHtmlDorucovaci(
		AddressEntity $e)
	{
		$cpco = static::getCpCo($e);

		$l1 = $e->street . (!empty($cpco) ? ' ' : '') . $cpco;
		$l2 = $e->zip . ($e->city ? ', ' : '') . $e->city;

		return Html::el('span')->addText($l1)
			->addHtml(Html::el('br'))
			->addText($l2);
	}

	public static function getHtmlFakturacni(
		AddressEntity $e)
	{
		$cpco = static::getCpCo($e);

		$l1 = $e->street . (!empty($cpco) ? ' ' : '') . $cpco;
		$l2 = $e->city . ' ' . $e->zip;

		return Html::el('span')->addText($l1)
			->addHtml(Html::el('br'))
			->addText($l2);
	}

	public static function getCpCo(
		AddressEntity $e)
	{
		$cpo = $e->cp;

		if(empty($e->co)){
			return $cpo;
		}

		if(!empty($cpo)){
			$cpo .= '/';
		}

		$cpo .= $e->co;

		return $cpo;
	}
}