<?php

class ExpOdbViewClass extends SqlView
{

	var $select = [
		'k.klient_id',
		'k.createdate',

		'kd.title',
		'kd.firstname',
		'kd.lastname',
		'kd.telnumber',
		// 'kd.description',

		'kd.tel2',
		'kd.email',
		'kd.ico',
		'kd.dico',
		'kd.firm_name',
		'kd.korespond_name',
		'kd.cu',
		'kd.birth_date',
		'kd.kind',
		'kd.organ',
		'kd.zasilat_mailem',
		'kd.cis_smluv',
		'kd.plat_smluv_od',

		'ak.city',
		'ak.street',
		'ak.cp',
		'ak.co',
		'ak.byt_cis',
		'ak.zip',

		'af.city city2',
		'af.street street2',
		'af.cp cp2',
		'af.co co2',
		'af.byt_cis byt_cis2',
		'af.zip zip2',

		'(SELECT COUNT(tcm.id) FROM tx_cena_mwh AS tcm WHERE YEAR(tcm.od) = {year} AND tcm.klient_id = k.klient_id AND tcm.odber_mist_id IN(SELECT tso.odber_mist_id FROM tx_sml_om AS tso WHERE {year} BETWEEN YEAR(tso.od) AND YEAR(tso.do) AND tso.klient_id = k.klient_id GROUP BY tso.odber_mist_id)) AS c_cmwh',
		'(SELECT COUNT(tso.id) FROM tx_sml_om AS tso WHERE {year} BETWEEN YEAR(tso.od) AND YEAR(tso.do) AND tso.klient_id = k.klient_id) AS c_smlom',
		'(SELECT COUNT(z.zaloha_id) FROM tx_zalohy AS z WHERE {year} BETWEEN YEAR(z.od) AND YEAR(z.do)AND z.klient_id = k.klient_id AND z.odber_mist_id IN(SELECT tso.odber_mist_id FROM tx_sml_om AS tso WHERE {year} BETWEEN YEAR(tso.od) AND YEAR(tso.do) AND tso.klient_id = k.klient_id GROUP BY tso.odber_mist_id)) AS c_zals',
		'(SELECT ou.jmeno FROM user AS ou WHERE ou.id = k.owner_id) AS owner_id'
	];

	var $from = [
		'es_klients AS k',
		'es_klient_details AS kd',
		'es_address AS ak',
		'es_address AS af'
	];

	var $where = [
		'kd.klient_detail_id = k.klient_detail_id',
		'af.address_id = k.korespond_id',
		'ak.address_id = k.address_id',
		'k.active = 1',
		'k.fakturacni = 0',
		'k.deleted = 0',
		'k.disabled = 0'
	];

	var $order = [
		'kd.kind',
		'kd.firstname',
		'kd.lastname',
		'kd.firm_name',
		'kd.ico',
		'kd.dico'
	];
}

class OdberateleExportView extends ExportViewElement
{

	static $allHead = [
		'firstname' => 'Křestní jméno',
		'lastname' => 'Přijmení',
		'title' => 'Oslovení',
		'birth_date' => 'Datum narození',
		'telnumber' => 'Tel. mobil',
		'tel2' => 'Tel. pevná',
		'zasilat_mailem' => 'Zasílat e-mailem',
		'email' => 'E-Mail',
		'kind' => 'Právní forma',
		'organ' => 'Statutární orgán',
		'firm_name' => 'Název firmy',
		'ico' => 'IČO',
		'dico' => 'DIČ',
		'cu' => 'Číslo účtu',
		'cis_smluv' => 'Číslo smlouvy',
		'plat_smluv_od' => 'Platnost smlouvy od',
		// 		, {	"title": "Fakturační adresa (sídlo)' 25, "active":true}
		'street2' => 'Fakturační Ulice',
		'cp2' => 'Č.p.',
		'co2' => 'Č.o.',
		'byt_cis2' => 'Číslo bytu',
		'city2' => 'Město',
		'zip2' => 'PSČ',
		// 		, 'state2' => 'Stát'
		// 		, {	"title": "Korespondenční (pokud je jiná než fakturační)' 25, "active":true}
		'korespond_name' => 'Oslovení',
		'street' => 'Korespondenční Ulice',
		'cp' => 'Č.p.',
		'co' => 'Č.o.',
		'byt_cis' => 'Číslo bytu',
		'city' => 'Město',
		'zip' => 'PSČ',
		// 		, 'active' => 'Aktivní'
		'c_smlom' => 'Počet aktivních připojených om',
		'c_cmwh' => 'Počet aktivních zadaných cen za mwh',
		'c_zals' => 'Počet letošních záloh',
		'owner_id' => 'Pověřená osoba'
	];

	public function __construct($type = NULL)
	{
		parent::__construct();
		$this->setLabels('Export Odběratelů');
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 */
	function init($info, $formObj = null, $csvWriter = null)
	{
		$formObj = ViewsFactory::createForm($info->scope);
		$formObj->buttons->createDefault();
		$formObj->buttons->setName(FormButton::CREATE, 'Exportovat');

		$csvWriter = new CSVWriterClass(self::$allHead);

		parent::init('odberatele.csv', $formObj, $csvWriter);
	}

	/**
	 *
	 * @param AppFormClass2 $formObj
	 */
	public function handleFormSend($formObj)
	{
		$expSqlView = new ExpOdbViewClass();
		$expSqlView->select->paramReplace([
			'{year}' => date('Y')
		]);

		if (AdminUserClass::isOnlyOwn()) {
			$expSqlView->where->AddElements([
				'k.owner_id = ' . AdminUserClass::$userId
			]);
		}
		$sql = $expSqlView->MakeSql();
		file_put_contents(__DIR__ . '/export.sql', $sql);
		return $sql;
	}

	function handleResultLine($item)
	{
		$item['kind'] = MContactDetails::$KIND[$item['kind']];
		return $item;
	}
}
