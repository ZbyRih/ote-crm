<?php

class ExpZalohyViewClass extends SqlView{
	var $select = [

		  'z.vs'
		, 'SUM(z.vyse) vyse'
		, 'COUNT(z.zaloha_id) pocet'
		, 'SUM(z.uhrazeno) uhrazeno'
		, 'SUM(z.preplatek) preplatek'
		, 'MIN(z.od) od'
		, 'MAX(z.do) do'

		, 'om.com'
		, 'om.eic'

		, 'a.street'
		, 'a.cp'
		, 'a.co'
		, 'a.city'
		, 'a.zip'
		, 'a.byt_cis'
		, 'a.country'
	];

	var $from = [
		  'tx_zalohy AS z'
		, 'tx_odber_mist AS om'
		, 'es_address AS a'
	];

	var $where = [
	      'om.odber_mist_id = z.odber_mist_id'
		, 'a.address_id = om.address_id'
	];

	var $groupby = [
		  'om.com'
		, 'z.vs'
	];

	var $orderby = [
		  'om.com'
		, 'z.vs'
	];
}

class ZalohyExportView extends ExportViewElement{
	static $allHead = [
		  'com' => 'Číslo odběrného místa'
		, 'eic' => 'EIC kód'
		, 'addr' => 'Adresa'
		, 'vs' => 'Var. Symbol'
		, 'vyse' => 'Suma záloh'
		, 'pocet' => 'Počet záloh'
		, 'uhrazeno' => 'Uhrazeno'
		, 'preplatek' => 'Přeplaceno'
		, 'od' => 'Od'
		, 'do' => 'Do'
		, 'color' => 'Stav'
	];

	public function __construct($type = NULL){
		parent::__construct();
		$this->setLabels('Export Záloh');
	}

	/**
	 * @param ModuleInfoClass $info
	 */
	function init($info){
		$formObj = ViewsFactory::createForm($info->scope);
		$formObj->buttons->createDefault();
		$formObj->buttons->setName(FormButton::CREATE, 'Exportovat');

		$field = $formObj->createField('year', FormUITypes::DROP_DOWN, date('Y'), 'Rok');

		$csvWriter = new CSVWriterClass(self::$allHead);

		$years = MArray::MapValToKey(OBE_App::$db->FetchArray('SELECT DISTINCT YEAR(z.od) AS od FROM tx_zalohy AS z'), 'od', 'od');

		$field->setList($years);

		$formObj->addFieldToForm($field, true);

		parent::init('zalohy.csv', $formObj, $csvWriter);
	}

	/**
	 * @param AppFormClass2 $formObj
	 */
	public function handleFormSend($formObj){
		$expSqlView = new ExpZalohyViewClass();

		$year = $formObj->getFieldValue('year');

		$expSqlView->select->AddElements([
			'IF(SUM(z.uhrazeno) >= SUM(z.vyse), 2, IF((SELECT DISTINCT TRUE FROM tx_zalohy AS z2 WHERE om.odber_mist_id = z2.odber_mist_id AND YEAR(z2.od) = ' . $year . ' AND z2.uhr = 0 AND LAST_DAY(z2.od) <= NOW()), 1, 0)) AS color'
		]);

		$expSqlView->where->AddElements(['(YEAR(z.od) = ' . $year . ' OR YEAR(z.do) = ' . $year . ')']);

		if(AdminUserClass::isOnlyOwn()){
			$expSqlView->where->AddElements(['om.owner_id = ' . AdminUserClass::$userId]);
		}

		return $expSqlView->MakeSql();
	}

	function handleResultLine($item){
		switch($item['color']){
			case 0:
				$item['color'] = '';
				break;
			case 1:
				$item['color'] = 'Po splatnosti';
				break;
			case 2:
				$item['color'] = 'Uhrazeno';
				break;
		}
		$item['addr'] = MAddress::_addr($item);
		return $item;
	}
}