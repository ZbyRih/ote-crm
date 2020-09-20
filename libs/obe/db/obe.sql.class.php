<?php

class OBE_Sql extends OBE_MySql{

	public static $keyWords = [
		'NOW()',
		'NULL'
	];

	private $inTransaction = 0;

	public function startTransaction()
	{
		if($this->inTransaction == 0){
			$this->query('START TRANSACTION');
		}
		$this->inTransaction++;
	}

	public function finishTransaction(
		$commit = true)
	{
		$this->inTransaction--;
		if($this->inTransaction == 0){
			if($commit){
				$this->query('COMMIT');
			}else{
				$this->query('ROLLBACK');
			}
		}
	}

	public function stopCheckFK()
	{
		$this->query('SET FOREIGN_KEY_CHECKS = 0');
	}

	public function startCheckFK()
	{
		$this->query('SET FOREIGN_KEY_CHECKS = 1');
	}

	public static function compileRows(
		$rows,
		$tableAlias = NULL)
	{
		if($tableAlias !== NULL){
			$rows = self::addAliases($rows, $tableAlias);
		}elseif(!is_array($rows)){
			$rows = [
				$rows
			];
		}
		return implode(', ', $rows);
	}

	public static function compileColumns(
		$columns)
	{
		$columns_fin = [];
		if(!empty($columns)){
			if(!is_array($columns)){
				$columns = [
					$columns
				];
			}
			foreach($columns as $key => $column){
				if(is_array($column)){
					if(!is_numeric($key)){
						$column = self::addAliases($column, $key);
					}
					$columns_fin[] = implode(', ', $column);
				}else{
					$columns_fin[] = $column;
				}
			}
		}
		if(empty($columns_fin)){
			return '*';
		}
		return implode(', ', $columns_fin);
	}

	public static function addAliases(
		$rows,
		$alias)
	{
		$alias .= '.';
		if(!empty($rows)){
			if(!is_array($rows)){
				$rows = [
					$rows
				];
			}
			foreach($rows as &$row){
				if(!strpos($row, '.')){
					$pos = strpos($row, '(');
					if($pos == 0){
						$pos = -1;
					}
					$row = substr($row, 0, $pos + 1) . $alias . substr($row, $pos + 1);
				}
			}
		}
		return $rows;
	}

	/**
	 * @param Array/String $tables
	 * @throws OBE_Exception
	 * @return String
	 */
	public static function compileTables(
		$tables)
	{
		$tables_c = [];
		$tables = MArray::AllwaysArray($tables);
		foreach($tables as $key => $item){
			$table = $item;
			if(!is_numeric($key)){
				$table .= ' AS ' . $key . ' ';
			}
			$tables_c[] = $table;
		}
		if(empty($tables_c)){
			throw new OBE_Exception('No tables in SQL');
			return NULL;
		}
		return implode(', ', $tables_c);
	}

	/**
	 * TODO : doplnit o alias, asi regularnim vyrazem na alnum v eventuelnich zavorkach kde neni tecka
	 */
	public static function compileConditions(
		$chunk)
	{
		$out = [];
		if(!empty($chunk)){
			if(is_array($chunk)){
				foreach($chunk as $key => $val){
					if(is_numeric($key)){
						if(is_array($val)){
							$eout = self::compileConditions($val);
							if(!empty($eout)){
								$out = array_merge($out, $eout);
							}
						}else{
							$out[] = 'AND';
							$out[] = self::checkForNULL($val);
						}
					}else{
						if(is_array($val)){
							$eout = self::compileConditions($val);
							if(!empty($eout)){
								$out[] = $key;
								$out[] = '(' . $eout . ')';
							}
						}else{
							$nitem = self::checkOpsAtEnd($key, '=') . ' ' . self::prepareValues($val);
							$out[] = 'AND';
							$out[] = self::checkForNULL($nitem);
						}
					}
				}
			}else{
				return $chunk;
			}
		}
		if(count($out) > 1 && in_array(mb_strtoupper($out[0]), [
			'AND',
			'OR'
		])){
			array_shift($out);
		}
		return implode(' ', $out);
	}

	public static function checkForNULL(
		$str)
	{
		$str = preg_replace('/= NULL|= *$/', 'IS NULL', $str);
		$str = preg_replace('/\!= NULL|\!= *$/', 'IS NOT NULL', $str);
		return $str;
	}

	/**
	 * zkontroluje retezec jestli na konci obsahuje = nebo in a kdyz tak retezec dolni o $op
	 * @param String $val
	 * @param String $op
	 * @return String
	 */
	public static function checkOpsAtEnd(
		$val,
		$op)
	{
		if(preg_match('/^.*(=| in) *$/i', $val)){
			return $val;
		}
		return $val . ' ' . $op;
	}

	public static function popEmptyValue(
		$vals)
	{
		if(!is_array($vals)){
			$vals = [
				$vals
			];
		}
		foreach($vals as $key => $val){
			if(empty($val) && $val != '0'){
				unset($vals[$key]);
			}
		}
		return $vals;
	}

	/**
	 * upravi hodnoty pro insert do databaze tak aby byli ulozitelne
	 * @param Mixed $value - hodnota
	 * @param String $key - nazev sloupce
	 * @return String
	 */
	public static function prepareValues(
		$value,
		$key = '')
	{
		if(!is_numeric($key) && !(strlen($key) > 0 && $key{0} == '!')){
			if($value === NULL){
				$value = 'NULL';
			}elseif($value instanceof DateTime){
				$value = "'" . $value->format('Y-m-d H:i:s') . "'";
			}elseif(empty($value)){
				if(is_numeric($value)){ // && !is_string($value) 4.8.2009 konflikt s datama v DBObjectClass - pri ukladani niceho do int sloupce chyba
					$value = "'0'";
				}elseif($value === NULL){
					$value = 'NULL';
				}elseif(is_string($value)){
					$value = "''";
				}elseif(is_bool($value)){
					$value = "'0'";
				}
			}else{
				if(is_bool($value)){
					$value = "'1'";
				}elseif(is_numeric($value) && !is_string($value)){
					$value = "'" . self::escape_string($value) . "'";
				}elseif(is_string($value)){
					if(!(in_array($value, self::$keyWords) || preg_match('/^\(SELECT/', $value))){ /*
					                                                                                * nahrazeno $value{0}=='(' kvuli tomu aby slo ukladat i
					                                                                                * hodnoty
					                                                                                * zacinajici zavorkou
					                                                                                */
						$value = "'" . self::escape_string($value) . "'";
					}
				}
			}
		}
		return $value;
	}

	public static function clearRowNames(
		&$rows)
	{
		foreach($rows as &$row){
			if(is_string($row) && $row{0} == '!'){
				$row = substr($row, 1);
			}
		}
	}

	public static function cleanBackApostrof(
		$row)
	{
		return rtrim(trim($row, '`'), '`');
	}

	public static function escape_string(
		$str)
	{
		return OBE_MySql::escape_string($str);
	}

	public static function prependNotEmpty(
		$prefix,
		$str)
	{
		if(!empty($str)){
			return $prefix . ' ' . $str;
		}
		return '';
	}

	public static function swapEmpty(
		$str,
		$replace)
	{
		if(empty($str)){
			return $replace;
		}
		return $str;
	}
}