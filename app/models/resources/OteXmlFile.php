<?php

namespace App\Models\Resources;

class OteXmlFile extends DataFile{

	public function __construct(
		$year,
		$kod,
		$oteId)
	{
		parent::__construct('ote/ote-xml/' . $year . '/' . strtolower($kod) . '/' . $oteId . '.xml');
	}
}