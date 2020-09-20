<?php

class ImportReportClass{

	var $loadedLines = 0;

	var $processLines = 0;

	var $updateNums = 0;

	var $insertNums = 0;

	var $fullNums = 0;

	var $afterImport = false;
}

class ImportClass extends CsvReaderClass{

	/**
	 * Callback po zapisu do databaze
	 * @var closure
	 */
	var $writeCallBack = NULL;

	/**
	 * Callback pro vlastni řešení primárního klíče
	 * @var closure
	 */
	var $primaryKeyCallBack = NULL;

	/**
	 * Callback po nacteni radek (vracej true pro pokracovani, flase pro preskoceni)
	 * @var closure
	 */
	var $readUserValidCallBack = NULL;

	/* runtime promenne */
	/**
	 * vnitrni promena pro ulozni dat
	 * @var []
	 */
	var $data;

	/**
	 * vnitrni promenna, indexy csv na tabulky
	 * @var array
	 */
	var $indexs2table = [];

	/**
	 * interni promenna, indexy na sloupce
	 * @var array
	 */
	var $index2row = [];

	var $reverseIndexRefs = [	// 		'es_klients' => array('adressinfoid' => array('es_addressinformation' => 'addressid'), 'klientdetailid' => array('es_klientsdetails' => 'klientdetailid'))
	];

	/**
	 * interni promenna pro definovani no empty
	 * @var array
	 */
	var $index2noEmpty = [];

	var $mainTable = NULL;

	var $mainIndex = NULL;

	var $mainTableDef = NULL;

	var $remainTblDefs = NULL;

	/**
	 *
	 * @var importConfigClass
	 */
	var $config = NULL;

	/**
	 *
	 * @var ImportReportClass
	 */
	var $report = NULL;

	/**
	 * konstruktor
	 * @param ImportConfigClass $importConfig
	 * @return ImportClass
	 */
	public function __construct(
		$importConfig)
	{
		$this->report = new ImportReportClass();
		$this->config = $importConfig;

		if(!empty($this->config->rows)){
			$this->compileRows($this->config);
		}
	}

	public function parseCSVFile(
		$fileName,
		$bRecompile = false,
		$test = false)
	{
		if($bRecompile){
			$this->compileRows($this->config);
		}

		OBE_App::$db->StartTransaction();

		$this->report->loadedLines = parent::parseCSVFile($fileName);

		if($this->report->loadedLines == -1){
			OBE_App::$db->FinishTransaction(false);
			return false;
		}else{
			if(!$this->WriteAllToDB($test)){
				return false;
			}
		}
		return true;
	}

	/**
	 *
	 * @param importConfigClass $defRows
	 */
	private function compileRows(
		$config)
	{
		$this->mapRowsAndTables2Index($config->rows);
		$this->createNoEmptyIndexs($config->noEmpty, $config->rows);

		if(!empty($config->tablesIndexRows)){
			$this->mainIndex = reset($config->tablesIndexRows);
			$this->mainTable = key($config->tablesIndexRows);
		}elseif(is_array($config->tables)){
			$this->mainTable = reset($config->tables);
		}else{
			return;
		}

		$this->mainTableDef = $config->newItemsDBDef[$this->mainTable];
		$this->remainTblDefs = $config->newItemsDBDef;

		unset($this->remainTblDefs[$this->mainTable]);
	}

	/**
	 *
	 * @param Array $defRows
	 */
	function mapRowsAndTables2Index(
		$defRows)
	{
		foreach($defRows as $tkey => $tval){ //tables
			foreach($tval as $rkey => $rval){
				if($rval > -1){
					$this->index2table[$rval][] = $tkey;
					$this->index2row[$rval][$tkey] = $rkey;
				}
			}
		}
	}

	function createNoEmptyIndexs(
		$noEmpty,
		$defRows)
	{
		foreach($noEmpty as $table => $rows){
			foreach($rows as $row => $defVal){
				if(is_numeric($row)){
					$this->index2noEmpty[$defRows[$table][$defVal]] = NULL;
				}else{
					$this->index2noEmpty[$defRows[$table][$row]] = $defVal;
				}
			}
		}
	}

	/**
	 * preklada radku csv
	 * @param array $line
	 * @param bool $userValid - umoznuje preskocit radek bez zakladani idecek hned po nacteni
	 * @return []|null
	 */
	function readFromCSV(
		$line,
		&$userValid)
	{
		if($line = parent::readFromCSV($line, $userValid)){

			$item = $this->mapLineToItem($line);

			if(is_callable($this->readUserValidCallBack) && !call_user_func_array($this->readUserValidCallBack, [
				&$item
			])){
				$userValid = false;
				return NULL;
			}

			$newIds = [];

			if($this->mainIndex){
				if($item != NULL && empty($item[$this->mainTable][$this->mainIndex])){
					if($this->primaryKeyCallBack){
						$newIds = call_user_func($this->primaryKeyCallBack, $item, $line);
					}

					if(empty($newIds)){
						$newIds = $this->createDBTables();
					}
				}else{
					$item = $this->initReversIndexes($item);
				}
			}else{
				if($this->primaryKeyCallBack){
					$newIds = call_user_func($this->primaryKeyCallBack, $item, $line);
				}
			}

			if($item != NULL && $item = $this->readCallBack($item, $newIds, $line)){

				if(!empty($newIds)){
					$item = $this->InitializeItemsIds($item, $newIds);
				}

				$this->data[] = $item;
				$this->report->processLines++;
			}
			return $item;
		}
		return NULL;
	}

	function mapLineToItem(
		$line)
	{
		$item = NULL;
		foreach($line as $key => $value){
			$buf = csvReaderClass::strPremakeValue($value);
			if(mb_strlen($buf) < 1){
				$buf = NULL;
			}

			if(isset($this->index2row[$key])){

				foreach($this->index2table[$key] as $table){
					$row = $this->index2row[$key][$table];
					$item[$table][$row] = $buf;
				}

				if($buf === NULL){
					$item = $this->fillItemByNoEmpty($item, $key);
					if($item === NULL){
						return NULL;
					}
				}
			}
		}
		return $item;
	}

	function fillItemByNoEmpty(
		$item,
		$csvIndex)
	{
		if(isset($this->index2noEmpty[$csvIndex])){
			if($this->index2noEmpty[$csvIndex] !== NULL){
				$tables = $this->index2table[$key];

				foreach($tables as $table){
					$row = $this->index2row[$csvIndex][$table];
					$item[$table][$row] = $this->index2noEmpty[$csvIndex];
				}
			}else{
				$this->error_buffer = 'pole ve sloupci `' . $csvIndex . '` nemůže být prázdné';
				return NULL;
			}
		}

		return $item;
	}

	/**
	 * zalozi defaultni zaznamy v tabulkach a vrati nove id
	 * @return Integer - nove id
	 */
	function createDBTables()
	{
		$remainTables = $this->remainTblDefs;

		$newId = [];

		if(isset($this->config->reverseIndexRefs[$this->mainTable])){

			$mainTableDef = $this->mainTableDef;

			foreach($this->config->reverseIndexRefs[$this->mainTable] as $underRowId => $relace){

				list($table, $srcId) = each($relace);

				if(isset($remainTables[$table])){

					$mainTableDef[$underRowId] = $newId[$table] = $this->InsertToTable($remainTables[$table], $table, NULL);

					unset($remainTables[$table]);
				}
			}

			$newId[$this->mainTable] = $this->InsertToTable($mainTableDef, $this->mainTable, 'NULL');
		}else{
			$newId[$this->mainTable] = $this->InsertToTable($this->mainTableDef, $this->mainTable, 'NULL');
		}

		if($newId[$this->mainTable] !== NULL){
			foreach($remainTables as $tab => $defvals){
				$newId[$tab] = $this->InsertToTable($defvals, $tab, $newId[$this->mainTable]);
			}
			$this->report->insertNums++;
		}else{
			$this->addError('Nezdařilo se vytvořit entitu');
		}

		return $newId;
	}

	/**
	 * hledá indexy přes databázi
	 */
	function initReversIndexes(
		$item)
	{
		$indexes = [];
		foreach($this->config->reverseIndexRefs as $table => $_indexes){

			foreach($_indexes as $index => $parentTable){
				$indexes[] = $index;
			}

			$tableIndex = $this->config->tablesIndexRows[$table];

			if($item[$table][$tableIndex]){

				$indxItem = OBE_App::$db->SelectOne(trim($table), $indexes, [
					$tableIndex => $item[$table][$tableIndex]
				]);

				foreach($indxItem as $indexKey => $index){

					$item[$table][$indexKey] = $index;

					if(isset($_indexes[$indexKey])){

						list($revTable, $revIndex) = each($_indexes[$indexKey]);

						reset($_indexes[$indexKey]);

						$item[$revTable][$revIndex] = $index;
					}
				}
			}
		}
		return $item;
	}

	/**
	 * vlozi do polozky sloupce s id
	 * @param Array $item
	 * @param Integer $id
	 */
	function InitializeItemsIds(
		$item,
		$ids)
	{
		foreach($this->config->tablesIndexRows as $table => $indexRow){
			if(!isset($item[$table][$indexRow]) || empty($item[$table][$indexRow])){
				$item[$table][$indexRow] = $ids[$table];
			}
		}
		return $item;
	}

	/**
	 * insert zaznamu do databaze
	 * @param Array $def - defaultni hodnoty
	 * @param String $table - nazev tabulky
	 * @param Integer $id
	 * @return Integer - last inserted id
	 */
	function InsertToTable(
		$def,
		$table,
		$id)
	{
		if(isset($def[0])){
			$def[$def[0]] = $id;
			unset($def[0]);
		}
		OBE_App::$db->Insert(trim($table), $def);
		return OBE_App::$db->getLastInsertId();
	}

	/**
	 * zapsat vsechny polozky v bufferu do databaze
	 * @return boolean
	 */
	function WriteAllToDB(
		$test = false)
	{
		$ret = false;
		$i = 0;
		if(is_array($this->data)){
			$ret = true;
			foreach($this->data as $item){
				$ret &= $r = $this->WriteToDB($item);
				if($r){
					$i++;
				}
				$this->report->updateNums++;
			}
		}
		if($ret && !$test){
			OBE_App::$db->FinishTransaction();
		}else{
			$this->error_buffer = "Zápis do databáze se nezdařil";

			OBE_App::$db->FinishTransaction(false);
		}
		$this->report->fullNums = $i;
		return $ret;
	}

	/**
	 * zapise jednu polozku do databaze
	 * @param array $item
	 * @return boolean
	 */
	function WriteToDB(
		$item = NULL)
	{
		$ret = true;
		if($item !== NULL && !empty($this->config->tables)){
			foreach($this->config->tables as $tableName){
				if(isset($item[$tableName])){
					if($r = $this->WriteToDBTable($tableName, $item[$tableName], $nid)){
						$this->writeCallBack($nid, $tableName, $item);
					}
					$ret &= $r;
				}
			}
		}
		return $ret;
	}

	/**
	 * zapise data jedne tabulky do databaze
	 * @param String $tableName
	 * @param Array $tableData
	 * @param Integer &$updId
	 * @return bool
	 */
	function WriteToDBTable(
		$tableName,
		$tableData,
		&$updId)
	{
		if(isset($this->config->tablesIndexRows[$tableName])){
			$key_name = $this->config->tablesIndexRows[$tableName];
			if(isset($tableData[$key_name])){
				$key_id = $tableData[$key_name];
				unset($tableData[$key_name]);
			}else{
				$this->addError($tableName . ' se nezdaril update');
				return false;
			}
			$updId = $key_id;
			return OBE_App::$db->Update(trim($tableName), $tableData, [
				$key_name . ' = ' . $key_id
			]);
		}
		$this->addError($tableName . ' se nezdaril update');
		return false;
	}

	function getStatus()
	{
		return [
			'messages' => $Import->errors_on_line,
			'aft_imp' => true,
			'nums' => $Import->cat_import_count,
			'update_num' => $Import->ok_update_num
		];
	}

	function GetArrayForSmarty(
		$status,
		$loadedLines = -1,
		$processLines = -1)
	{
		if(is_bool($status)){
			return [
				'messages' => $this->errors_on_line,
				'nums' => $this->report->processLines,
				'update_num' => $this->report->updateNums,
				'inserted_lines' => $this->report->insertNums,
				'aft_imp' => true,
				'loaded_lines' => $this->report->loadedLines,
				'status' => $status
			];
		}else{
			return [
				'message' => $this->error_buffer,
				'aft_imp' => true
			];
		}
	}

	function writeCallBack(
		$id,
		$table,
		$item)
	{
		if($this->writeCallBack){
			call_user_func($this->writeCallBack, $id, $table, $item);
		}
		return true;
	}
}