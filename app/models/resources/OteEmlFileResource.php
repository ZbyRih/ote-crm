<?php

namespace App\Models\Resources;

class OteEmlFileResource extends DataFileResource{

	public function __construct(
		$year,
		$kod,
		$msgId)
	{
		$strFile = new OteEmlFile($year, $kod, $msgId);
		parent::__construct((string) $strFile);
	}

	public function getFormatedContent()
	{
		$src = $this->getContent();

		$elm = explode("\r\n\r\n", $this->getContent());

		if(strpos($elm[0], 'Content-Transfer-Encoding: quoted-printable')){
			return $elm[0] . "\r\n\r\n" . quoted_printable_decode($elm[1]);
		}

		if(strpos($elm[0], 'Content-Transfer-Encoding: base64')){
			return $elm[0] . "\r\n\r\n" . base64_decode($elm[1]);
		}

		return $src;
	}
}