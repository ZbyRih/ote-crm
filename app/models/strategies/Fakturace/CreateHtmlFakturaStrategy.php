<?php

namespace App\Models\Strategies\Fakturace;

use App\Models\Orm\Faktury\FakturaEntity;

class CreateHtmlFakturaStrategy{

	/**
	 * @param FakturaEntity $fa
	 * @return string
	 */
	public function get(
		FakturaEntity $fa)
	{
		$html = $fa->html;

		if($fa->storno && !strpos($html, '<div id="faktura-storno">')){
			$storno = file_get_contents(APP_DIR . '/config/assets/storno.html');

			$html = str_replace('<div id="faktura">', '<div id="faktura">' . $storno, $html);
		}

		return $html;
	}
}