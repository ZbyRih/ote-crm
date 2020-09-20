<?php

class MAddress extends ModelClass{

	var $name = 'Address';

	var $table = 'es_address';

	var $primaryKey = 'address_id';

	var $rows = [
		'address_id',
		'street',
		'cp',
		'co',
		'city',
		'zip'
// 		'byt_cis',
// 		'patro',
// 		'country'
	];

	public static function city(
		$modelItem,
		$key = 'Address')
	{
		$a = $modelItem[$key];
		return $a['city'] . ' ' . $a['zip'];
	}

	public static function cityPost(
		$modelItem,
		$key = 'Address')
	{
		$a = $modelItem[$key];
		return $a['zip'] . (($a['city']) ? ', ' : '') . $a['city'];
	}

	public static function addr(
		$modelItem,
		$key = 'Address')
	{
		$a = $modelItem[$key];
		return self::_addr($a);
	}

	public static function _addr(
		$a)
	{
		return $a['city'] . ', ' . self::_addrUl($a);
	}

	public static function addrUl(
		$modelItem,
		$key = 'Address')
	{
		$a = $modelItem[$key];
		return self::_addrUl($a);
	}

	public static function _addrUl(
		$a)
	{
		return $a['street'] . self::_cp_op($a);
	}

	public static function cp_op(
		$modelItem,
		$key = 'Address')
	{
		$a = $modelItem[$key];
		return self::_cp_op($a);
	}

	public static function _cp_op(
		$a)
	{
		$cpo = $a['cp'];

		if(!empty($a['co'])){
			$cpo = $cpo . ((!empty($cpo)) ? '/' : '') . $a['co'];
		}

		return ((empty($cpo)) ? '' : ' ') . $cpo;
	}
}

class MKonAddress extends MAddress{

	var $name = 'Korespond';
}

class MContactDetails extends ModelClass{

	static $KIND = [
		0 => 'Fyzická osoba',
		1 => 'Právnická osoba'
	];

	static $KIND_SHR = [
		0 => 'FO',
		1 => 'PO'
	];

	var $name = 'ContactDetails';

	var $table = 'es_klient_details';

	var $primaryKey = 'klient_detail_id';

	var $rows = [
		'klient_detail_id',
		'telnumber',
		'tel2',
		'email',
		'firstname',
		'lastname',
		'title',
		'ico',
		'dico',
		'firm_name',
		'korespond_name',
// 		'description',
		'cu',
		'birth_date',
		'kind',
		'organ',
		'zasilat_mailem',
		'cis_smluv',
		'plat_smluv_od',
		'dat_spla_dnu'
	];

	var $defaultVals = [
		'title' => ''
	];

	public function onSaveBefor(
		&$modelItem)
	{
		$this->tel($d[$this->name], 'telnumber');
		$this->tel($d[$this->name], 'tel2');
	}

	public static function konAddr(
		$modelItem,
		$key = 'Address')
	{
		return MAddress::addrUl($modelItem, $key) . "\r\n" . MAddress::cityPost($modelItem, $key);
	}

	public static function name(
		$cd,
		$korepspond = false)
	{
		if($korepspond){
			return trim(
				($cd['kind']) ? ($cd['korespond_name'] ? $cd['korespond_name'] : $cd['firm_name']) : implode(' ',
					[
						$cd['title'],
						$cd['firstname'],
						$cd['lastname']
					]));
		}else{
			return trim(($cd['kind']) ? $cd['firm_name'] : implode(' ', [
				$cd['title'],
				$cd['firstname'],
				$cd['lastname']
			]));
		}
	}

	public static function sname(
		$cd)
	{
		return trim(($cd['kind']) ? $cd['firm_name'] : implode(' ', [
			$cd['firstname'],
			$cd['lastname']
		]));
	}

	public static function firm(
		$cd)
	{
		return $cd['firm_name'];
	}

	public static function identity(
		$cd)
	{
		if($cd['kind']){
			return 'IČ: ' . $cd['ico'] . ' DIČ: ' . $cd['dico'];
		}else{
			return 'Datum narození: ' . OBE_DateTime::convertFromDB($cd['birth_date']);
		}
	}

	public static function isKonAddrEmpty(
		$addr)
	{
		$k = trim(str_replace([
			"\r\n",
			' '
		], [
			'',
			''
		], $addr));
		if(empty($k)){
			return true;
		}

		return false;
	}

	private function tel(
		&$d,
		$key)
	{
		if(isset($d[$key]) && !empty($d[$key])){
			if($d[$key] == '+420 '){
				$d[$key] = NULL;
			}else if(substr($d[$key], 0, 5) != '+420 '){
				$d[$key] = '+420 ' . $d[$key];
			}
		}
	}
}

class MContactFlags extends ModelClass{

	var $name = 'ContactFlags';

	var $table = 'es_klients_flags';

	var $primaryKey = 'klients_flags_id';

	var $rows = [
		'klients_flags_id',
// 		'wholeseler',
// 		'sendnews',
// 		'short_reg',
// 		'wholeseler_interest',
		'have_mail'
	];

	var $defaultVals = [
// 		'wholeseler' => 0,
// 		'sendnews' => 0,
// 		'short_reg' => 0,
// 		'wholeseler_interest' => 0,
		'have_mail' => 0
	];
}

class MContacts extends ModelClass{

	var $name = 'Contacts';

	var $table = 'es_klients';

	var $primaryKey = 'klient_id';

	var $rows = [
		'klient_id',
		'createdate',
		'deleted',
		'active',
		'disabled',
// 		'groupid',
// 		'password',
// 		'email',
		'klient_detail_id',
		'address_id',
		'korespond_id',
// 		'regcode',
// 		'discount',
// 		'tags',
		'owner_id',
		'created_by',
		'fakturacni'
	];

	var $defaultVals = [
		'createdate' => 'NOW()',
		'deleted' => 0,
		'active' => 1,
		'fakturacni' => 0
	];

	var $associatedModels = [
		'MContactDetails' => [
			'type' => 'belongsTo',
			'foreignKey' => 'klient_detail_id',
			'associationForeignKey' => 'klient_detail_id'
		],
		'MContactFlags' => [
			'type' => 'hasOne',
			'foreignKey' => 'klients_flags_id',
			'associationForeignKey' => 'klient_id'
		],
		'MAddress' => [
			'type' => 'belongsTo',
			'foreignKey' => 'address_id',
			'associationForeignKey' => 'address_id'
		],
		'MUser' => [
			'type' => 'belongsTo',
			'foreignKey' => 'owner_id',
			'associationForeignKey' => 'id',
			'deprecateSave' => true,
			'rows' => [
				'id',
				'jmeno'
			]
		]
	];

	public function onSaveBefor(
		&$modelItem)
	{
		if(!isset($modelItem['Contacts']['owner_id']) || $modelItem['Contacts']['owner_id'] == NULL){
			$modelItem['Contacts']['owner_id'] = AdminUserClass::$userId;
		}
		if(isset($modelItem['Contacts']['password']) && $modelItem['Contacts']['password'] === NULL){
			unset($modelItem['Contacts']['password']);
		}
	}
}

class MOdberatel extends MContacts{

	public function __construct(
		$bInitialize = true)
	{
		$this->associatedModels['MKonAddress'] = [
			'type' => 'hasOne',
			'ownpk' => true,
			'associationForeignKey' => 'korespond_id'
		];
		parent::__construct($bInitialize);
	}
}

class MContactTagRel extends ModelClass{

	var $name = 'ContactTagRel';

	var $table = 'es_klient2tag';

	var $primaryKey = 'id';

	var $rows = [
		'id',
		'klient_id',
		'tag_id'
	];

	var $associatedModels = [];
}

class MContactRelTags extends MContactTagRel{

	var $name = 'ContactRelTags';

	var $associatedModels = [
		'MEntityTag' => [
			'type' => 'belongsTo',
			'foreignKey' => 'tag_id',
			'associationForeignKey' => 'entitytagid'
		]
	];
}