<?php


class MSmlOM extends ModelClass{

	var $name = 'SmlOM';

	var $table = 'tx_sml_om';

	var $primaryKey = 'id';

	var $rows = [
		'id',
		'klient_id',
		'odber_mist_id',
		'fak_skup_id',
		'flags_id',
		'typ_sml',
		'category',
		'od',
		'do',
		'vztah',
		'interval',
		'vs',
		'zaloha',
		'cena_mwh'
	];

	var $defaultVals = [
		'od' => 'NOW()',
		'do' => 'NOW()'
	];

	var $associatedModels = [
		'MSmlOMFlags' => [
			'type' => 'belongsTo',
			'foreignKey' => 'flags_id',
			'associationForeignKey' => 'flags_id'
		],
		'MOdberMist' => [
			'type' => 'hasOne',
			'foreignKey' => 'odber_mist_id',
			'deprecateSave' => true
		],
		'MFakSkup' => [
			'type' => 'hasOne',
			'foreignKey' => 'fak_skup_id',
			'associationForeignKey' => 'fak_skup_id',
			'associatedModels' => [],
			'deprecateSave' => true
		]
	];

	static $CATEGORY = [
		'0' => 'DOM',
		'1' => 'MO',
		'2' => 'SO',
		'3' => 'VO'
	];

	static $VZTAH = [
		'0' => 'Majitel',
		'1' => 'Nájemník'
	];

	static $TYP_SML = [
		'0' => 'Prolongace',
		'1' => 'Doba určitá',
		'2' => 'Doba neurčitá'
	];

	public function getOmsForKlient($klientId, $year){
		$SmlOM = new MSmlOM();
		$SmlOM->removeAssociatedModelsByType('MSmlOMFlags');
		$SmlOM->group[] = 'odber_mist_id';

		$smloml = $SmlOM->FindAll([
			'klient_id' => $klientId,
			'!' . $year . ' <= YEAR(do)'
		], [
			'odber_mist_id',
			'CONCAT_WS(\', \', OdberMist.eic, Address.city, Address.street, Address.cp, Address.co) AS adresa'
		]);

		return MArray::MapValToKeyFromMArray($smloml, 'SmlOM', 'odber_mist_id', 'adresa');
	}

	public function getByRangeAndOmId($omId, $dbOd, $dbDo){
		$zal = new MSmlOM();
		$dbOd = 'DATE(\'' . OBE_DateTime::convertDTToDB($dbOd) . '\')';
		$dbDo = 'DATE(\'' . OBE_DateTime::convertDTToDB($dbDo) . '\')';
		if($res = $zal->FindAll([
			'odber_mist_id' => $omId,
			'!((od <= ' . $dbOd . ')',
			'AND',
			'!(do >= ' . $dbDo . '))'
		])){
			return $res;
		}
		return null;
	}
}

class MSmlOMFlags extends ModelClass{

	static $TYP_MER = [
		0 => 'C',
		1 => 'A',
		2 => 'M'
	];

	static $TYP_NET = [
		0 => 'MS',
		1 => 'DL'
	];

	static $DUV_ZAD = [
		'0' => 'změna dodavatele plynu za jiného dodavatele plynu',
		'1' => 'přepis bez ZD',
		'2' => 'změna dodavatele plynu se změnou zákazníka v odběrném místě',
		'3' => 'R1 - připojení nového odběru',
		'4' => 'R2 - připojení po odpojení z důvodu neplacení'
	];

	var $name = 'SmlOMFlags';

	var $table = 'tx_sml_om_flags';

	var $primaryKey = 'flags_id';

	var $rows = [
		'flags_id',
		'var',
		'vyt',
		'tuv',
		'tech',
		'duv_zad',
		'dan',
		'typ_mer',
		'typ_net'
	];
}

class MSmlOMWContact extends MSmlOM{

	var $associatedModels = [
		'MContacts' => [
			'type' => 'hasOne',
			'foreignKey' => 'klient_id'
		]
	];
	// 			, 'associationForeignKey' => 'flags_id'
}