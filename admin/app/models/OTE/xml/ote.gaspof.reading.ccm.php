<?php

class OTEGasPofReadingCCM{

	public static function getReading($b){

		$data['type'] = 'C';

		foreach($b->instrumentReadingC->children() as $c){
			$a = current($c->attributes());
			$a['from'] = XML_Utils::date($a['from']);
			$a['to'] = XML_Utils::date($a['to']);

			$data['instruments'][] = $a;
		}

		foreach($b->metersC->children() as $c){
			$a = current($c->attributes());
			$a['from'] = XML_Utils::date($a['from']);
			$a['to'] = XML_Utils::date($a['to']);
			$a['startReading'] = XML_Utils::date($a['startReading']);
			$a['readingEnd'] = XML_Utils::date($a['readingEnd']);

			$meter['atrib'] = $a;
			$cons = [];

			foreach($c->children() as $cc){
				$a = current($cc->attributes());
				$a['from'] = XML_Utils::date($a['from']);
				$a['to'] = XML_Utils::date($a['to']);
				$cons[] = $a;
			}
			$meter['consumptions'] = $cons;

			$data['meters'][] = $meter;
		}

		foreach($b->contractValueC->children() as $c){
			$a = current($c->attributes());
			$a['from'] = XML_Utils::date($a['from']);
			$a['to'] = XML_Utils::date($a['to']);

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