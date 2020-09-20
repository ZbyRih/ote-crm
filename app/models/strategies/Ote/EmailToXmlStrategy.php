<?php

namespace App\Models\Strategies\Ote;

use App\Extensions\Utils\Helpers\ArrayHash;

/**
 * @property string $oteId
 * @property string $oteKod
 * @property string $raw
 * @property \SimpleXMLElement|NULL $xml
 */
class OteXmlDTO extends ArrayHash{
}

class NeniValidniXml extends \Exception{
}

class EmailToXmlStrategy{

	public function convert(
		$raw,
		$subject)
	{
		$xmlDto = new OteXmlDTO();
		$xmlDto->raw = $raw;

		if(!$xml = simplexml_load_string($raw)){
			throw new NeniValidniXml();
		}

		$xmlDto->xml = $xml;

		if(isset($xml['id'])){
			$xmlDto->oteId = (string) $xml['id'];
		}

		if(isset($xml['message-code'])){
			$xmlDto->oteKod = (string) $xml['message-code'];
		}

		if($xmlDto->oteId && $xmlDto->oteKod){
			return $xmlDto;
		}

		$matchs = [];

		if(!preg_match('/([A-Z0-9]{3,}) \- /', $subject, $matchs)){
			return null;
		}

		$xmlDto->oteKod = $matchs[1];

		if(!isset($xml->Identification)){
			return null;
		}

		if(!isset($xml->Identification['v'])){
			return null;
		}

		$xmlDto->oteId = (string) $xml->Identification['v'];
		return $xmlDto;
	}
}