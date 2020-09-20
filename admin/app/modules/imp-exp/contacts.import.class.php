<?php

class contactsImportConfig extends importConfigClass{
	var $tables = ['es_klients', 'es_klientsdetails', 'es_addressinformation'];

	var $rows = [
		  'es_klients' => ['klientid' => 0, 'sendnews' => 16, 'groupid' => 17, 'email' => 5, 'tags' => 18, 'password' => 19]
		, 'es_klientsdetails' => ['firstname' => 2, 'lastname' => 3, 'titule' => 1, 'telnumber' => 4, 'description' => 15, 'klientdetailid' => -1]
		, 'es_addressinformation' => ['mailing_city' => 6, 'mailing_street' => 7, 'mailing_zip' => 8, 'bill_city' => 9, 'bill_street' => 10, 'bill_zip' => 11, 'ico' => 13, 'dico' => 14, 'firm_name' => 12, 'addressid' => -1]
	];

	var $newItemsDBDef = [
		  'es_klients' => [0 => 'klientid', 'active' => '1', 'sendnews' => '1',/* 'short_reg' => '0',*/ 'klientdetailid' => NULL, 'adressinfoid' => NULL, 'unsubscribecode' => NULL, 'createdate' => 'NOW()']
		, 'es_addressinformation' => ['addressid' => NULL]
		, 'es_klientsdetails' => ['klientdetailid' => NULL]
	];

	var $tablesIndexRows = [
		  'es_klients' => 'klientid'
		, 'es_addressinformation' => 'addressid'
		, 'es_klientsdetails' => 'klientdetailid'
	];

	var $reverseIndexRefs = [
		'es_klients' => ['adressinfoid' => ['es_addressinformation' => 'addressid'], 'klientdetailid' => ['es_klientsdetails' => 'klientdetailid']]
	];
}


class contactsImportClass extends ImportViewElement{

	function __construct(){
		$config = new contactsImportConfig();

		parent::__construct('csv_file', $config);

		$this->setReadLineCallBack([$this, 'readLineCallBack']);
	}

	function readLineCallBack($item, $newIds){
		if(!isset($item['es_klients']['klientid'])){
			if(!isset($item['es_klients']['sendnews'])){
				$item['es_klients']['sendnews'] = 1;
			}
			if($item['es_klients']['sendnews'] == 1){
				$item['es_klients']['unsubscribecode'] = OBE_Strings::sha256($item['es_klients']['email']);
			}
		}

		if(isset($item['es_klients']['password']) && !empty($item['es_klients']['password'])){
			$item['es_klients']['password'] = OBE_Strings::sha256($item['es_klients']['password']);
		}else{
			unset($item['es_klients']['password']);
		}

		if(isset($item['es_klients']['tags']) && !empty($item['es_klients']['tags'])){

			OBE_App::$db->Delete('es_klient2tag', 'klientid = ' . $item['es_klients']['klientid']);

			if($tags = explode(',', $item['es_klients']['tags'])){
				$values = [];

				foreach($tags as $tagId){
					$values[] = [
						  'klientid' => $item['es_klients']['klientid']
						, 'tagid' => $tagId
					];
				}
				OBE_App::$db->Insert('es_klient2tag', $values, ['klientid', 'tagid']);
			}

			unset($item['es_klients']['tags']);
		}
		return $item;
	}
}