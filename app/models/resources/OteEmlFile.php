<?php

namespace App\Models\Resources;

class OteEmlFile extends DataFile{

	public function __construct(
		$year,
		$kod,
		$msgId)
	{
// 		./app/data/ote/emails/2018/GP6/1578166466.138174.1515428560221.JavaMail.csote-csote.ote-cr.cz.eml
		parent::__construct('ote/emails/' . $year . '/' . strtolower($kod) . '/' . $msgId . '.eml');
	}
}