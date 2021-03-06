<?php

class OTEGasPofReadingAB{

	public static function getReading($b){
		$data['type'] = 'A';

		$ins = collection($b->instrumentReading->children())->map(
			function ($v){
				$a = current($v->attributes());
				return [
					'qty' => $a['quantity'],
					'day' => strtotime($a['day'])
				];
			})->buffered();

		$from = date('Y-m-d\TH:i:sP', $ins->min('day')['day']);
		$to = date('Y-m-d\TH:i:sP', $ins->max('day')['day']);
		$qty = $ins->sumOf('qty');

		$data['instruments'][] = [
			'from' => $from,
			'to' => $to,
			'distributionSum' => number_format($qty, 2, '.', '')
		] + current($b->instrumentReading->attributes());

		/**
		 *
		 * @var SimpleXMLElement
		 */
		foreach($b->meters->children() as $c){
			$a = current($c->attributes());

			$a['startReading'] = $a['from'] = XML_Utils::date($a['from']);
			$a['readingEnd'] = $a['to'] = XML_Utils::date($a['to']);
			$a['readingType'] = '02';

			$meter['atrib'] = $a;

			// flueGasHeat="10.6649"
			// gasDay="2018-01-01T06:00:00+01:00"
			// reductionConsumption="276"
			// sumGas="2943.48"

			$cons = [];
			foreach($c->children() as $cc){
				$a = current($cc->attributes());
				$cons[] = [
					'flueGasHead' => $a['flueGasHeat'],
					'distribVolume' => $a['sumGas'] / 1000,
					'consumption' => $a['reductionConsumption'],
					'factor' => number_format($a['sumGas'] / $a['flueGasHeat'] / $a['reductionConsumption'], 4, '.', ''),
					'from' => XML_Utils::date($a['gasDay']),
					'to' => XML_Utils::date($a['gasDay'])
				];
			}

			$meter['consumptions'] = $cons;

			$data['meters'][] = $meter;
		}

		foreach($b->contractValue->children() as $c){
			$a = current($c->attributes());

// 			percentage-effect="8.33333" -- nenni vsude
//			tyhle jsou vsude
// 			effect="0.0833333"
// 			price="232117.93"
// 			size="1300.00"

			if($c->getName() == 'oversize'){
				$a['size'] = $a['maxSize'];
				$a['effect'] = $a['monthFactor'];
				$a['price'] = $a['unitPrice'];
			}

			if(isset($a['relevanceFrom'])){
				$a['from'] = XML_Utils::date($a['relevanceFrom']);
				$a['to'] = XML_Utils::date($a['relevanceTo']);
			}else{
				$a['from'] = $from;
				$a['to'] = $to;
			}

			$a['capacity'] = $a;
			$a['payment'] = null;

			$data['contracts'][] = $a;
		}

		return $data;
	}
}