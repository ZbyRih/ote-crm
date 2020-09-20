<?php

class MFakSkup extends ModelClass{

	const CIS = 'fakskup';

	var $name = 'FakSkup';
	var $table = 'tx_fak_skup';
	var $primaryKey = 'fak_skup_id';
	var $rows = ['fak_skup_id', 'klient_id', 'fa_klient_id', 'cis', 'nazev', 'owner_id'];

	var $associatedModels = [
		'MContacts' => [
			  'type' => 'belongsTo'
			, 'foreignKey' => 'fa_klient_id'
			, 'associationForeignKey' => 'klient_id'
		]
	];

	public static function createNew($klientId){
		$FakSkup = new MFakSkup();
		$cis = self::getNewCis();
		$fs[$FakSkup->name] = [
			  'klient_id' => $klientId
			, 'cis' => $cis
		];
		$fs['Contacts']['fakturacni'] = 1;
		$FakSkup->Save($fs);
		return $FakSkup->id;
	}

	public static function getNewCis(){
		return Counters::getNext(self::CIS);
	}

	public function onSaveBefor(&$modelItem){
		if(!isset($modelItem['FakSkup']['owner_id']) || $modelItem['FakSkup']['owner_id'] == NULL){
			$modelItem['FakSkup']['owner_id'] = AdminUserClass::$userId;
		}
	}
}

class MFakSkupList extends MFakSkup{
	var $associatedModels = [
		'MContacts' => [
			  'type' => 'belongsTo'
			, 'foreignKey' => 'klient_id'
			, 'associationForeignKey' => 'klient_id'
		]
	];
}