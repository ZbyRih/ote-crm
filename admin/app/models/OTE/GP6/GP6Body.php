<?php


class GP6Body extends ModelClass{

	var $name = 'GP6Body';

	var $primaryKey = 'id';

	var $table = 'tx_ote_invoice_body';

	var $rows = [
		'id',
		'ote_id',
		'odber_mist_id',
		'head_id',
		'data'
	];

	public function transform($data){
		$b = unserialize($data[$this->name]['body']);
		$instrument = $b['instrument'];
		$contracts = $b['contracts'];
		$meters = $b['meters'];

		$cons = [];

		foreach($meters as $m){
			foreach($m['consumptions'] as $c){
				$cons[] = $c + [
					'meterId' => $m['meterId'],
					'readingType' => $m['readingType']
				];
			}
		}

		foreach($cons as $c){

		}
	}
}