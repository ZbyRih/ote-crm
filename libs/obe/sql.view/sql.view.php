<?php


class SqlView{

	/**
	 * promenna v niz se ulozi vytvorene sql
	 * @var string
	 */
	private $sql;

	/**
	 * array select carky doda system
	 * @var SqlElement
	 */
	public $select;

	/**
	 * array kazda polozka je bud retezec(pred se da carka) a nebo pole
	 * kde klic je vse co se k joinu vaze a hodnota urcuje typ joinu(left/right/inner)
	 * carky se dodaji automaticky
	 * array('table1', 'table2', 'table on bla bla' => 'join', ...
	 * , 'table4')
	 * @var SqlElement
	 */
	public $from;

	/**
	 * podminky AND/OR doda system kazda polozka where je bud retezec and obdrzi automaticky,
	 * nebo klic je podminka a hodnota je and/or
	 * @var SqlElement
	 */
	public $where;

	/**
	 * seskupovani carky doda system
	 * @var SqlElement
	 */
	public $groupby;

	/**
	 * carky doda system pole kde je polozka bud retezec(defaultne ASC), nebo pole kde je klic smer(asc/desc; defaultne asc) hodnota je nazev slupce
	 * @var SqlElement
	 */
	public $orderby;

	/**
	 * pro strankovani doda se po propocteni poctu produktu
	 * @var SqlElement
	 */
	public $limit;

	/**
	 * data ziskana dotazem
	 * @var array
	 */
	public $data;

	/**
	 * klic pole pod nejz se ulozi radek z DB
	 * @var String
	 */
	public $assocKey = NULL;

	/**
	 * callback fce pro praci nad sloupci volana pres array_map
	 * @var closure
	 */
	private $selectReplaces = [];

	private $rowMakeCallback = NULL;

	private $callBackForLine = NULL;

	public $bCallInternCallBack = true;

	private $join = [];

	public function __construct()
	{
		$this->select = new SqlElement($this->select);
		$this->from = new SqlElement($this->from);
		$this->where = new SqlElement($this->where);
		$this->groupby = new SqlElement($this->groupby);
		$this->orderby = new SqlElement($this->orderby);
		$this->limit = new SqlElement($this->limit);
	}

	public function __get(
		$varName)
	{
		if(!empty($varName)){
			if($varName{0} == '_'){
				$chVN = substr($varName, 1);
				if(isset($this->$chVN)){
					return $this->$chVN->array;
				}else{
					throw new Exception('Pozadavek na neexistujici promennou [' . $chVN . '] tridy ' . get_class($this));
				}
			}
		}
	}

	public function __clone()
	{
		$this->select = clone $this->select;
		$this->from = clone $this->from;
		$this->where = clone $this->where;
		$this->groupby = clone $this->groupby;
		$this->orderby = clone $this->orderby;
		$this->limit = clone $this->limit;
	}

	/**
	 * vrati jeden zaznam, skrz SQL LIMIT 1
	 * @return Array
	 */
	public function fetchOne(
		$assocKey = NULL)
	{
		$limit = clone $this->limit;
		$this->limit->ResetElements('1');
		if($data = $this->fetchAll($assocKey)){
			return reset($data);
		}
		$this->limit = clone $limit;
		return NULL;
	}

	/**
	 * vytvori sql z parametrů pohledu, pustí dotaz a vrátí celou kolekci
	 * @return Array
	 */
	public function fetchAll(
		$assocKey = NULL)
	{
		if($this->Execute($this->MakeSql(), $assocKey)){
			return $this->data;
		}
		return NULL;
	}

	/**
	 * vytvori podle parametru tridy sql dotaz
	 * @return string
	 */
	public function MakeSql()
	{
		$this->selectReplace();
		$sql = '';
		$sql = $this->makeSelect($this->_select);
		$sql .= $this->makeFrom($this->_from);
		$sql .= $this->makeWhere($this->_where);
		$sql .= $this->makeSimple(' GROUP BY', $this->_groupby);
		$sql .= $this->makeOrderBy($this->_orderby);
		$sql .= $this->makeSimple(' LIMIT', $this->_limit);
		$sql = $this->callbackCreateSQL($sql);
		return $sql;
	}

	/**
	 *
	 * @param String $sql
	 * @return Array
	 */
	public function Execute(
		$sql = NULL,
		$assocKey = NULL)
	{
		if($assocKey){
			$this->assocKey = $assocKey;
		}
		if($sql === NULL){
			if(!($sql = $this->sql)){
				$sql = $this->MakeSql();
			}
		}
		$this->sql = $sql;
		if($res = OBE_App::$db->query($sql)){
			$this->data = [];

			if($this->callBackForLine !== NULL && $this->bCallInternCallBack){
				while($item = OBE_App::$db->fetch_array($res)){
					$item = call_user_func($this->callBackForLine, $item);
					$this->addItem($this->callbackReadDBLine($item));
				}
			}elseif($this->callBackForLine !== NULL){
				while($item = OBE_App::$db->fetch_array($res)){
					$this->addItem(call_user_func($this->callBackForLine, $item));
				}
			}elseif($this->bCallInternCallBack){
				while($item = OBE_App::$db->fetch_array($res)){
					$this->addItem($this->callbackReadDBLine($item));
				}
			}else{
				while($item = OBE_App::$db->fetch_array($res)){
					$this->addItem($item);
				}
			}
			return !empty($this->data);
		}
		return NULL;
	}

	private function addItem(
		$item)
	{
		if($this->assocKey && isset($item[$this->assocKey])){
			$this->data[$item[$this->assocKey]] = $item;
		}else{
			$this->data[] = $item;
		}
	}

	public function callbackReadDBLine(
		$item)
	{
		return $item;
	}

	public function callbackCreateSQL(
		$sql)
	{
		return $sql;
	}

	/**
	 *
	 * @param Array $item
	 * @return Array
	 */
	function callBackJoin(
		$item)
	{
		foreach($this->join as $joinSqlView){
			$item = $joinSqlView->callbackReadDBLine($item);
		}
		return $item;
	}

	private function makeSelect(
		$mixed)
	{
		if(!empty($mixed)){
			if(!is_array($mixed)){
				$mixed = [
					$mixed
				];
			}
			if($this->rowMakeCallback){
				$mixed = array_map($this->rowMakeCallback, $mixed);
			}
			$tsql = implode(', ', $mixed);
		}
		if(empty($tsql)){
			$tsql = '*';
		}
		return 'SELECT DISTINCT ' . $tsql;
	}

	private function selectReplace(
		$replaces = NULL)
	{
		if($replaces !== NULL){
			$this->select->paramReplace($replaces);
		}elseif(!empty($this->selectReplaces)){
			$this->select->paramReplace($this->selectReplaces);
		}
	}

	private function makeFrom(
		$mixed)
	{
		$out = [];
		if(!empty($mixed) && is_array($mixed)){
			foreach($mixed as $key => $val){
				if(is_numeric($key)){
					$out[] = ',';
					$out[] = $val;
				}else{
					if(empty($val)){
						$val = 'INNER';
					}
					$out[] = $val . ' JOIN ' . $key;
				}
			}
		}
		if(count($out) > 1 && $out[0] == ','){
			array_shift($out);
		}
		return ' FROM ' . implode(' ', $out);
	}

	private function makeWhere(
		$mixed,
		$bNoWhere = false)
	{
		$out = [];
		if(!empty($mixed) && is_array($mixed)){
			foreach($mixed as $line){
				if(is_array($line)){
					$out[] = $this->makeWhere($line);
				}else{
					$out[] = 'AND';
					$out[] = $line;
				}
			}
		}else{
			if(!is_array($mixed)){
				$out[] = $mixed;
			}
		}
		if(count($out) > 1 && in_array(mb_strtoupper($out[0]), [
			'AND',
			'OR'
		])){
			array_shift($out);
		}
		if(empty($out)){
			$out = [
				'TRUE'
			];
		}
		if($bNoWhere){
			return implode(' ', $out);
		}
		return ' WHERE ' . implode(' ', $out);
	}

	private function makeOrderBy(
		$mixed)
	{
		$out = [];
		if(!empty($mixed)){
			foreach($mixed as $key => $val){
				$out[] = ',';
				if(is_numeric($key)){
					$out[] = $val . ' ASC';
				}else{
					$out[] = $key . ' ' . $val;
				}
			}
		}
		if(count($out) > 1 && $out[0] == ','){
			array_shift($out);
		}
		if(count($out) > 0){
			return ' ORDER BY ' . implode(' ', $out);
		}
		return '';
	}

	/* pro group by nebo limit */
	private function makeSimple(
		$key,
		$mixed)
	{
		$sql = '';
		if(!empty($mixed)){
			if(is_array($mixed)){
				$sqla = implode(',', $mixed);
			}else{
				$sqla = $mixed;
			}
			$sql = $key . ' ' . $sqla;
			if(empty($sqla)){
				return '';
			}
		}
		return $sql;
	}

	public function addSelectReplace(
		$key,
		$val = NULL)
	{
		if(is_array($key)){
			$this->selectReplaces = array_merge($this->selectReplaces, $key);
		}else{
			$this->selectReplaces[$key] = $val;
		}
	}

	public function setCallBack(
		$callback)
	{
		$oldCallBack = $this->callBackForLine;
		$this->callBackForLine = $callback;
		return $oldCallBack;
	}

	public function dropCallBack()
	{
		$this->callBackForLine = NULL;
	}

	/**
	 *
	 * @param SqlView $sqlView
	 */
	public function join(
		$sqlView,
		$join = NULL,
		$joinCond = NULL,
		$fromKey = NULL)
	{
		$this->select->join($sqlView->select);
		if($join){
			$aFrom = array_reverse($sqlView->from->array);

			if($cond = $sqlView->makeWhere($joinCond, true)){
				$cond = ' ON (' . $cond . ')';
			}

			$firstFrom = array_shift($aFrom);
			$lastKey = $this->from->AddElements([
				$firstFrom . $cond => $join
			], $fromKey);

			if($jWhere = $sqlView->where->array){
				$jWhere = array_reverse($jWhere);
			}

			while(!empty($aFrom)){
				$nextFrom = array_shift($aFrom);
				if(!empty($jWhere)){
					$nextCond = array_shift($jWhere);
					if($nextCond = $sqlView->makeWhere($nextCond, true)){
						$nextCond = ' ON (' . $nextCond . ')';
					}
				}else{
					$nextCond = '';
				}
				$lastKey = $this->from->AddElements([
					$nextFrom . $nextCond => $join
				], $lastKey);
			}
		}else{
			$this->from->join($sqlView->from);
			$this->where->join($sqlView->where);
		}
		$this->groupby->join($sqlView->groupby);
		$this->orderby->join($sqlView->orderby);
		$this->limit->join($sqlView->limit);
		$this->join[] = $sqlView;
	}

	public function getSql()
	{
		return $this->sql;
	}

	public function setReadLineCallBack(
		$callBack,
		$bUseIntern = true)
	{
		$this->callBackForLine = $callBack;
		$this->bCallInternCallBack = $bUseIntern;
	}

	public function disableInterCallback()
	{
		$this->bCallInternCallBack = false;
	}

	public function enableInterCallback()
	{
		$this->bCallInternCallBack = true;
	}
}
;
