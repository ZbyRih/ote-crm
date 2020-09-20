<?php

class OTEGasDataPofGP6{

	/**
	 * @param \SimpleXMLElement $xml
	 */
	public function process(
		$xml,
		$oteId)
	{
		$r = true;
		$a = $xml->attributes();

		foreach($xml as $node){

			if($node->getName() == 'invoice'){
				if(isset($node->head) && isset($node->body)){
					$odbmist = new MOdberMist();

					$h = $node->head;
					$b = $node->body;

					if($OM = $odbmist->FindOne([
						'OdberMist.eic' => (string) $h->subjects['opm']
					])){
						$omId = $OM[$odbmist->name]['odber_mist_id'];

						$bData = $this->getBody($b);
						$hData = $this->getHead($h, $oteId, $omId, $bData['type']);

						if($bData && $hData){
							return [
								$hData,
								[
									'GP6Body' => [
										'ote_id' => $oteId,
										'odber_mist_id' => $omId,
										'data' => serialize($bData)
									]
								]
							];
						}
					}
				}
			}
		}
		return null;
	}

	/**
	 * @param \SimpleXMLElement $h
	 */
	protected function getHead(
		$h,
		$oteId,
		$omId,
		$type)
	{
		$gp6Head = new GP6Head();

		$a = current($h->attributes());

		return [
			'GP6Head' => [
				'ote_id' => $oteId,
				'odber_mist_id' => $omId, // big int 20
				'type' => $type,

				'pofId' => (isset($a['pofId']) ? $a['pofId'] : null), // 15
				'version' => (isset($a['version']) ? $a['version'] : null), // 15
				'priceTotal' => $a['priceTotal'], // float 14,2
				'priceTotalDph' => (isset($a['priceTotalDph']) ? $a['priceTotalDph'] : null), // float 14,2
				'from' => XML_Utils::date($a['periodFrom']), //
				'to' => XML_Utils::date($a['periodTo']), //
				'cancelled' => (isset($a['cancelled']) ? true : false), // bool
				'yearReCalculatedValue' => (isset($a['yearReCalculatedValue']) ? $a['yearReCalculatedValue'] : null), // 16,2

				'attributes_segment' => (string) $h->attributes['segment'], // INV, COR, EXT, CAN, EOC, HST
				'attributes_number' => (isset($h->attributes['number']) ? (string) $h->attributes['number'] : null), // 15
				'attributes_anumber' => (isset($h->attributes['anumber']) ? (string) $h->attributes['anumber'] : null), // 25
				'attributes_corReason' => (isset($h->attributes['corReason']) ? (string) $h->attributes['corReason'] : null), // 2
				'attributes_complId' => (isset($h->attributes['complId']) ? (string) $h->attributes['complId'] : null), // 25
				'attributes_SCNumber' => (isset($h->attributes['SCNumber']) ? (string) $h->attributes['SCNumber'] : null), // 15

				'subjects_opm' => (string) $h->subjects['opm'] // 27ZG300Z0227838S
			]
		];
	}

	/**
	 * @param \SimpleXMLElement $b
	 * @param integer $headId
	 */
	protected function getBody(
		$b)
	{
		$sxml = $b->saveXML();

		if(isset($b->instrumentReadingC)){
			return OTEGasPofReadingCCM::getReading($b);
		}else if(isset($b->instrumentReading)){
			return OTEGasPofReadingAB::getReading($b);
		}
	}

	/**
	 * @param array $body
	 */
	public static function cliOutBody(
		$body)
	{
		$ha = reset($body['meters'])['atrib'];
		$fc = reset($body['meters'])['consumptions'];

		$ta = new Console_Table();
		$ta->setHeaders(array_keys($ha));

		$tc = new Console_Table();
		$tc->setHeaders(array_keys(reset($fc)));

		foreach($body['meters'] as $m){
			$ta->addRow($m['atrib']);

			foreach($m['consumptions'] as $a){
				$tc->addRow($a);
			}
		}

		OBE_Cli::writeBr('# meters - atrib:');
		echo $ta->getTable();

		OBE_Cli::writeBr('# meters - consumptions:');
		echo $tc->getTable();

		$fi = reset($body['instruments']);

		$ti = new Console_Table();
		$ti->setHeaders(array_keys($fi));
		foreach($body['instruments'] as $a){
			$ti->addRow($a);
		}
		OBE_Cli::writeBr('# instruments:');
		echo $ti->getTable();

		$fo = reset($body['contracts']);
		$to = new Console_Table();
		$to->setHeaders(array_keys($fo));
		foreach($body['contracts'] as $a){
			$to->addRow($a);
		}
		OBE_Cli::writeBr('# contracts:');
		echo $to->getTable();
	}
}