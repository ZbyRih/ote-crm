<?php

namespace App\Models\Resources;

class OteXmlFileResource extends DataFileResource{

	public function __construct(
		$year,
		$kod,
		$oteId)
	{
		$strFile = new OteXmlFile($year, $kod, $oteId);
		parent::__construct((string) $strFile);
	}

	public function getFormatedContent()
	{
		$src = $this->getContent();
		$sxml = simplexml_load_string($src);
		$dom = dom_import_simplexml($sxml)->ownerDocument;
		$dom->formatOutput = true;
		return htmlentities($dom->saveXML());
	}
}