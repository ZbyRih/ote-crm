<?php

class ExpOdbMistViewClass extends SqlView
{

	var $select = [
		'om.com',
		'om.eic',
		'om.popis',
		'om.dist_id',
		'om.deprecated',
		'om.owner_id',
		'om.createdate',
		'om.address_id',

		'a.city',
		'a.street',
		'a.cp',
		'a.co',
		'a.byt_cis',
		'a.zip',
		'(SELECT ou.jmeno FROM user AS ou WHERE ou.id = om.owner_id) AS owner_jmeno'
	];

	var $from = [
		'tx_odber_mist AS om',
		'es_address AS a'
	];

	var $where = [
		'om.deprecated = 0',
		'a.address_id = om.address_id'
	];

	var $order = [
		'om.com'
	];
}

class OdbMistExportView extends ExportViewElement
{

	static $allHead = [
		'eic' => 'EIC kód',
		'com' => 'Číslo odběrného místa',
		'dist_id' => 'Distributor',
		'popis' => 'Popis',
		'street' => 'Ulice',
		'cp' => 'Č.p.',
		'co' => 'Č.o.',
		'byt_cis' => 'Číslo bytu',
		'city' => 'Město',
		'zip' => 'PSČ',
		'owner_jmeno' => 'Pověřená osoba',
		'owner_id' => 'Pověřená osoba (id)'
	];

	public function __construct($type = NULL)
	{
		parent::__construct();
		$this->setLabels('Export Odběrných míst');
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

		parent::init('oder_mist.csv', $formObj, $csvWriter);
	}

	/**
	 *
	 * @param AppFormClass2 $formObj
	 */
	public function handleFormSend($formObj)
	{
		$expSqlView = new ExpOdbMistViewClass();

		if (AdminUserClass::isOnlyOwn()) {
			$expSqlView->where->AddElements([
				'om.owner_id = ' . AdminUserClass::$userId
			]);
		}

		return $expSqlView->MakeSql();
	}

	function handleResultLine($item)
	{
		$item['dist_id'] = MOdberMist::$DIST[$item['dist_id']];
		return $item;
	}
}
