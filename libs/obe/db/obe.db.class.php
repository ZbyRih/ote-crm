<?php

class OBE_DB extends OBE_Sql{

	var $callBackFunc = '';

	/**
	 * nastavit callback na dalsi volani fetch fci
	 * @param closure $callBackFunc - callback fce
	 */
	function setCallBack(
		$callBackFunc)
	{
		$this->callBackFunc = $callBackFunc;
	}

	/**
	 * zruseni callback fce
	 */
	function dropCallBack()
	{
		$this->callBackFunc = NULL;
	}

	/**
	 *
	 * @param Array/String $tables
	 * @param Array/String $columns
	 * @param Array/String $conditions
	 * @return String
	 */
	function Select(
		$tables,
		$columns,
		$conditions = NULL)
	{
		$sql = $this->createSelect($tables, $columns, $conditions);
		return $this->FetchArray($sql);
	}

	/**
	 *
	 * @param Array/String $tables
	 * @param Array/String $columns
	 * @param Array/String $conditions
	 * @return String
	 */
	function SelectOne(
		$tables,
		$columns,
		$conditions = NULL)
	{
		$sql = $this->createSelect($tables, $columns, $conditions);
		return $this->FetchSingleArray($sql);
	}

	/**
	 *
	 * @param Array/String $tables
	 * @param Array/String $columns
	 * @param Array/String $conditions
	 * @return String
	 */
	private function createSelect(
		$tables,
		$columns,
		$conditions = NULL)
	{
		$conds = self::compileConditions($conditions);
		if(empty($conds)){
			$conds = 'TRUE';
		}
		return 'SELECT DISTINCT ' . self::compileColumns($columns) . ' FROM ' . self::compileTables($tables) . ' WHERE ' . $conds;
	}

	/**
	 * Funkce provede predany sql dotaz a jeho vysledek ulozi a vrati v indoxem poli
	 * , pokud sql dotaz selze nebo je vysledek prazdny vrati NULL
	 * @param String $sql - sql dotaz
	 * @param Boolean $mode - true(fetch row)/false(fetch assoc)
	 * @param Boolean $debug - ladici semafor
	 * @return mixed - data(2level array) or null
	 */
	function FetchArray(
		$sSql,
		$mode = false,
		$debug = false)
	{
		$res = $this->query($sSql, $debug);
		if($res){
			$ret_data = [];
			if($this->callBackFunc){
				while($data = $this->fetch_array($res, $mode)){
					$ret_data[] = call_user_func($this->callBackFunc, $data);
				}
			}else{
				while($data = $this->fetch_array($res, $mode)){
					$ret_data[] = $data;
				}
			}
			$this->dropCallBack();
			if(!empty($ret_data)){
				return $this->getAsObject($ret_data);
			}
		}
		return NULL;
	}

	/**
	 * Funkce provede predany sql dotaz a jeho vysledek ulozi a vrati v indoxem poli
	 * , pokud sql dotaz selze nebo je vysledek prazdny vrati NULL
	 * @param string $sql - sql dotaz
	 * @param closure $callbackFce - funkce pres kterou se prezenou data pred vlozenim do pole
	 * @param boolean $mode - true(fetch row)/false(fetch assoc)
	 * @param boolean $debug - ladici semafor
	 * @return mixed - data(2level array) or null
	 */
	function FetchArrayCB(
		$sSql,
		$callbackFce,
		$mode = false,
		$debug = false)
	{
		$oldCallback = $this->callBackFunc;
		$this->callBackFunc = $callbackFce;
		$data = $this->FetchArray($sSql, $mode, $debug);
		$this->callBackFunc = $oldCallback;
		return $this->getAsObject($data);
	}

	/**
	 * vratí jeden radek z vysledku jako asociativni pole, zbytek bude zahozen
	 * @param String $sql - sql dotaz
	 * @param Boolean $mode - true(fetch row)/false(fetch assoc)
	 * @param Boolean $debug - ladici semafor
	 * @return mixed - data(1level array) or null
	 */
	function FetchSingleArray(
		$sSql,
		$mode = false,
		$debug = false)
	{
		$res = $this->query($sSql, $debug);
		if($res){
			if($data = $this->fetch_array($res, $mode)){
				return $this->getAsObject($data);
			}
		}
		return NULL;
	}

	/**
	 * vratí jeden radek z vysledku jako object, zbytek bude zahozen
	 * @param string $sql - sql dotaz
	 * @param boolean $debug - ladici semafor
	 * @return mixed - data(1level array) or null
	 */
	function FetchSingleObject(
		$sSql,
		$debug = false)
	{
		$res = $this->query($sSql, $debug);
		if($res){
			if($data = $this->fetch_object($res)){
				return $this->getAsObject($data);
			}
		}
		return NULL;
	}

	/**
	 * provede sql dotaz a vytvori asociativni pole kde klice jsou data ze sloupce
	 * definovaneho promennou $assoc
	 * @param string $sql - sql dotaz
	 * @param string $assoc - sloupec pro klice polozek pole
	 * @param boolean $debug - vypsat dotaz a pripadne chyby?
	 * @return array - bud pole klic=>zaznam z db (2level array), nebo NULL
	 */
	function FetchAssoc(
		$sSql,
		$assoc,
		$debug = false)
	{
		$res = $this->query($sSql, $debug);
		if($res){
			$ret_data = [];
			while($data = $this->fetch_array($res)){
				if(!isset($ret_data[$data[$assoc]])){
					$ret_data[$data[$assoc]] = $data;
				}else{
					$ret_data[$data[$assoc]][] = $data;
				}
			}
			return $this->getAsObject($ret_data);
		}
		return NULL;
	}

	/**
	 * vlozi sql dotazem x radku a vrati posledni vytvorene id
	 * @param string $sSql - sql dotaz
	 * @param boolean $bDebug - vypsat sql dotaz?
	 * @return int - posledni vlozene id nebo NULL
	 */
	function InsertOne(
		$sSql,
		$bDebug = false)
	{
		if($this->query($sSql, $bDebug)){
			return $this->getLastInsertId();
		}
		return NULL;
	}

	/**
	 *
	 * @param String $table
	 * @param Array $values - row => value
	 * @param Array $keys - pak values nemusi byt
	 * @return Boolean
	 */
	function Insert(
		$table,
		$values,
		$keys = NULL)
	{
		$sql = 'INSERT INTO `' . $table . '` ';
		if(!empty($values)){
			if($keys){
				$sql .= '(`' . implode('`,`', $keys) . '`)VALUES';
				$datas = [];
				foreach($values as $item){
					$item = array_map([
						'OBE_Sql',
						'prepareValues'
					], $item);
					$datas[] = '(' . implode(',', $item) . ')';
				}
				$sql .= implode(',', $datas);
			}else{
				$rows = array_keys($values);
				$values = array_map([
					'OBE_Sql',
					'prepareValues'
				], $values, $rows);

				OBE_Sql::clearRowNames($rows);

				$sql .= '(`' . implode('`,`', $rows) . '`)VALUES(' . implode(',', $values) . ')';
			}
		}else{
			$sql .= '()VALUES()';
		}
		return $this->query($sql);
	}

	/**
	 * Updatne data hodnotama z $values na zaznamy na ktere sedi $conditions
	 * @param String $table
	 * @param Array $values
	 * @param Array $conditions - mozno i string
	 * @return Boolean
	 */
	function Update(
		$table,
		$values,
		$conditions = [])
	{
		if(!empty($values)){
			$rows = array_keys($values);
			$values = array_map([
				'OBE_Sql',
				'prepareValues'
			], $values, $rows);
			$values = array_combine($rows, $values);
			$conditions = OBE_Sql::compileConditions($conditions);
			$sql = 'UPDATE `' . $table . '` SET ';
			foreach($values as $row_name => $value){
				if(!is_scalar($value)){
					OBE_Trace::dump($value);
					throw new OBE_Exception('value is not scalar');
				}
				if(!is_numeric($row_name)){
					$sql .= '`' . $row_name . '` = ' . $value . ', ';
				}else{
					$sql .= $value . ', ';
				}
			}
			$sql = rtrim($sql, ', ');
			if(!empty($conditions)){
				$sql .= ' WHERE ' . $conditions;
			}
			return $this->query($sql);
		}
		/* tohle se mi nejak nezda */
		return true;
	}

	function Delete(
		$fromTable,
		$conditions = [])
	{
		$condstr = OBE_Sql::compileConditions($conditions);
		if(empty($conditions)){
			$condstr = 'TRUE';
		}
		return $this->query('DELETE FROM ' . $fromTable . ' WHERE ' . $condstr);
	}

	function Count(
		$count,
		$from,
		$conditions = [])
	{
		$condstr = OBE_Sql::compileConditions($conditions);
		if(empty($conditions)){
			$condstr = 'TRUE';
		}
		return $this->FetchSingleColumn('SELECT count(' . $count . ') as num FROM ' . $from . ' WHERE ' . $condstr, 'num');
	}

	function FetchSingleColumn(
		$sSql,
		$rowName = NULL,
		$bDebug = false)
	{
		if($res = $this->query($sSql, $bDebug)){
			if($row = $this->fetch_array($res)){
				if(is_null($rowName)){
					return reset($row);
				}else{
					return $row[$rowName];
				}
			}
		}
		return NULL;
	}

	/**
	 * Vrátí maximalní hodnotu na jedno sloupci z jedné tabulky dle podmínek
	 * @param String $table
	 * @param String $maxRow
	 * @param Array $conditions
	 */
	function getMaxOnRow(
		$table,
		$maxRow,
		$conditions = [])
	{
		$condstr = OBE_Sql::compileConditions($conditions);
		if(empty($conditions)){
			$condstr = 'TRUE';
		}
		return $this->FetchSingleColumn('SELECT MAX(' . $maxRow . ') as num FROM ' . $table . ' WHERE ' . $condstr, 'num');
	}

	function truncate(
		$table)
	{
		$this->query('TRUNCATE TABLE ' . $table);
	}

	function disableLog()
	{
	}

	function enableLog()
	{
	}

	/**
	 *
	 * @param OBE_Array $data
	 */
	private function getAsObject(
		$data)
	{
		return $data;
	}
}