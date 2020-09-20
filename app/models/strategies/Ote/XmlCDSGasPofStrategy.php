<?php

namespace App\Models\Strategies\Ote;

use Nette\DI\Container;

class XmlCDSGasPofStrategy{

	public static function from(
		$xml,
		Container $container)
	{
		if(!isset($xml['message-code'])){
			throw new OteXmlException('XML neobsahuje message-code attribut.');
		}

		$code = $xml['message-code'];
		$className = '\\App\\Models\\Strategies\\Ote\\I' . $code . 'MainStrategy';

		$service = $container->getByType($className, false);

		if(!$service){
			throw new OteXmlException('Pro kód ' . $code . ' není implemetován parser');
		}

		$parser = $service->create();
		return $parser->execute($xml);
	}
}