<?php

namespace App\Models\Strategies\Ote\GP6;

use App\Models\Strategies\Ote\XmlUtils;

class ReadingCCMStrategy{

	public function __construct()
	{
	}

	public function execute(
		$node)
	{
		$data['type'] = 'C';

		foreach($node->instrumentReadingC->children() as $c){
			$a = current($c->attributes());
			$a['from'] = XmlUtils::dateTimeToString($a['from']);
			$a['to'] = XmlUtils::dateTimeToString($a['to']);

			$data['instruments'][] = $a;
		}

		foreach($node->metersC->children() as $c){
			$a = current($c->attributes());
			$a['from'] = XmlUtils::dateTimeToString($a['from']);
			$a['to'] = XmlUtils::dateTimeToString($a['to']);
			$a['startReading'] = XmlUtils::dateTimeToString($a['startReading']);
			$a['readingEnd'] = XmlUtils::dateTimeToString($a['readingEnd']);

			$meter['atrib'] = $a;
			$cons = [];

			foreach($c->children() as $cc){
				$a = current($cc->attributes());
				$a['from'] = XmlUtils::dateTimeToString($a['from']);
				$a['to'] = XmlUtils::dateTimeToString($a['to']);
				$cons[] = $a;
			}
			$meter['consumptions'] = $cons;

			$data['meters'][] = $meter;
		}

		foreach($node->contractValueC->children() as $c){
			$a = current($c->attributes());
			$a['from'] = XmlUtils::dateTimeToString($a['from']);
			$a['to'] = XmlUtils::dateTimeToString($a['to']);

			$ac = null;
			$ap = null;

			if($c->capacity){
				$ac = current($c->capacity->attributes());
			}

			if($c->payment){
				$ap = current($c->payment->attributes());
			}

			$a['capacity'] = $ac;
			$a['payment'] = $ap;
			$data['contracts'][] = $a;
		}

		return $data;
	}
}