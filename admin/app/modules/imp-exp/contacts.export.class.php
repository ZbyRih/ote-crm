<?php

class ExpContactViewClass extends SqlView{
	var $select = [
		  'k.klientid'
		, 'k.active'
		, 'k.sendnews'
		, 'k.groupid'
  		, 'k.email'
		, 'GROUP_CONCAT(kt.tagid SEPARATOR \',\') AS tags'
// 		, 'k.password'

  		, 'kd.titule'
		, 'kd.firstname'
		, 'kd.lastname'
  		, 'kd.telnumber'
		, 'kd.description'

		, 'ai.mailing_city'
		, 'ai.mailing_street'
		, 'ai.mailing_zip'
		, 'ai.bill_city'
		, 'ai.bill_street'
		, 'ai.bill_zip'
		, 'ai.ico'
		, 'ai.dico'
		, 'ai.firm_name'
	];

	var $from = [
		  'es_klients AS k'
		, 'es_klient2tag AS kt ON (k.klientid = kt.klientid)' => 'LEFT'
		, 'es_klientsdetails AS kd'
		, 'es_addressinformation AS ai'
	];

	var $where = [
		  'k.klientdetailid = kd.klientdetailid'
		, 'k.adressinfoid = ai.addressid'
// 		, 'k.short_reg = 0'
		, 'k.active = 1'
	];

	var $groupby = [
		'k.klientid'
	];
}

class ContactExportView extends ExportViewElement{
	static $allHead = [
		  'klientid' => 'Contact ID'
		, 'titule' => 'Oslovení'
		, 'firstname' => 'Jméno'
		, 'lastname' => 'Přijmení'
		, 'telnumber' => 'Telefon'
		, 'email' => 'e-mail'
		, 'mailing_city' => 'Doručovací - Město'
		, 'mailing_street' => 'D. - Ulice'
		, 'mailing_zip' => 'D. - P.S.C.'
		, 'bill_city' => 'Fakturační - Město'
		, 'bill_street' => 'F. - Ulice'
		, 'bill_zip' => 'F. - P.S.C.'
		, 'firm_name' => 'Název firmy'
		, 'ico' => 'IČO'
		, 'dico' => 'DIČ'
		, 'description' => 'Poznámka'
		, 'sendnews' => 'Zasílání novinek'
 		, 'groupid' => 'Zařazení kontaktu'
 		, 'tags' => 'Tagy'
		, 'password' => 'Heslo'
	];

	public function __construct($moduleObj){
		$this->setLabels('Export kontaktů');

		$formObj  = ViewsFactory::createForm($moduleObj->scope);
		$csvWriter = new CSVWriterClass(self::$allHead);

		parent::__construct('kontakty.csv', $formObj, $csvWriter);
	}

	/**
	 *
	 * @param AppFormClass2 $formObj
	 */
	public function handleFormSend($formObj){
		$expSqlView = new ExpContactViewClass();

		return $expSqlView->MakeSql();
	}

	function handleResultLine($item){
		$item['password'] = '';
		return $item;
	}
}