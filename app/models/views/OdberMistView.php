<?php

namespace App\Models\Views;

class OdberMistView{

	public static function identity(
		$c,
		$com = true)
	{
		return $c['eic'] . ($com ? (', ' . $c['com']) : '') . ' - ' . AddressView::addr($c);
	}
}