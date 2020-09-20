<?php

namespace App\Models\Strategies\Ote;

use Nette\DI\Container;

class XmlProcessorStrategy{

	public static function from(
		$raw,
		Container $container)
	{
		if(!$xml = @simplexml_load_string($raw)){
			throw new OteXmlException('XML se nepodařilo načíst');
		}

		if($xml->getName() == 'CDSGASPOF'){
			return XmlCDSGasPofStrategy::from($xml, $container);
		}

		throw new OteXmlException('Pro XML ' . $xml->getName() . ' není implemetován parser');
	}
}