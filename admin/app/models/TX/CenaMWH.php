<?php


class MCenaMWH extends ModelClass{

	var $name = 'CenaMWH';

	var $table = 'tx_cena_mwh';

	var $primaryKey = 'id';

	var $rows = [
		'id',
		'klient_id',
		'odber_mist_id',
		'cena',
		'od'
	];

	var $defaultVals = [
		'od' => 'NOW()'
	];

	var $associatedModels = [
		'MOdberMist' => [
			'type' => 'hasOne',
			'foreignKey' => 'odber_mist_id',
			'deprecateSave' => true
		]
	];

	public function getByRangeOmId($omId, $from, $to){
		$_MWH = new MCenaMWH();

		$from = ($from instanceof DateTime) ? OBE_DateTime::convertDTToDB($from) : $from;
		$to = ($to instanceof DateTime) ? OBE_DateTime::convertDTToDB(OBE_DateTime::modifyClone($to, '-1 day')) : $to;

		$mwh = $_MWH->FindAll(
			[
				'odber_mist_id' => $omId,
				'AND',
				[

					'!DATE(od) BETWEEN DATE(\'' . $from . '\') AND DATE(\'' . $to . '\')',
					'OR',
					'!DATE(od) <= DATE(\'' . $to . '\')'
				]
			], [], [
				'od' => 'DESC'
			]);

		return $mwh;
	}
}