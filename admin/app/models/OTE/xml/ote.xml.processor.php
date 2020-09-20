<?php


class OTEXmlProcessor{

	/**
	 *
	 * @param SimpleXMLElement $xml
	 */
	public function process($xml, $oteId){
		if($xml->getName() == 'CDSGASPOF'){
			$pof = new OTEGasDataPof();
			return $pof->process($xml, $oteId);
		}
	}
}

class OTEGasDataPof{

	/**
	 *
	 * @param SimpleXMLElement $xml
	 */
	public function process($xml, $oteId){
		if(isset($xml['message-code'])){
			$code = $xml['message-code'];
			$class = 'OTEGasDataPof' . $code;

			if(class_exists($class)){
				$gp6Head = new GP6Head();
				$gp6Body = new GP6Body();

				$pof = new $class();

				if($r = $pof->process($xml, $oteId)){

					list($h, $b) = $r;

					$gp6Head->Save($h);

					$b[$gp6Body->name]['head_id'] = $h[$gp6Head->name]['id'];

					$gp6Body->Save($b);

					return true;
				}
			}
		}
		return false;
	}
}