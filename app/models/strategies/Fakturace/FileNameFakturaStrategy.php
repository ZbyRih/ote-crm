<?php

namespace App\Models\Strategies\Fakturace;

class FileNameFakturaStrategy{

	public function get(
		$dir,
		FileInfoFaktura $fif)
	{
		return $fif->klient . '_' . $fif->from->format('Y_md') . '_' . $fif->cislo . '.pdf';
	}
}