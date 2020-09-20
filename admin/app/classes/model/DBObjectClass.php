<?php


class DBObjectClass{

	/**
	 * hlavni tabulka od ktere se to vse bude odvijet
	 * @var DBSimpleObjectClass
	 */
	var $mainTable = NULL;

	var $limit;

	var $_cacheConditions = [];

	/**
	 *
	 * @var DBObjFieldCache
	 */
	var $_cacheFields = NULL;

	/**
	 * array(
	 * '$name'=> array(
	 * 'type' => NULL(natvrdo)/'INNER'(join)/'LEFT'(join)
	 * 'obj' => vytvorenej object
	 * 'linkedToKey' => klic z main table na kterej se bude vazat
	 * 'linkedTo' => NULL(na main)/'$name'(na jinej object)
	 * 'linkedKey'=> NULL(z toho to primary key)/'$name'(jinej sloupec)
	 * 'linkedOp' => NULL('=')/'$op'(jinej operator)
	 * )
	 * )
	 * @var Array
	 */
	var $joinObjects = [];

	/**
	 *
	 * @var ModelClass
	 */
	private $modelObj;

	/**
	 * Konstruktor
	 * @param DBSimpleObjectClass $mainTable
	 * @param ModelClass
	 * @return void
	 */
	function __construct($mainTable, $modelObj = NULL){
		if(empty($mainTable)){
			throw new Exception('Prázdný argument');
		}
		$this->modelObj = $modelObj;
		$this->mainTable = $mainTable;
	}

	/**
	 * pridani tabulek do selectu, primou vazbou
	 * @param DBSimpleObjectClass $object
	 * @param String $type
	 * @param String $linkedToKey
	 * @param String $linkedTo
	 * @param String $linkedKey
	 * @param String $linkedOp
	 * @return void
	 */
	function AddMoreTable($object, $type = NULL, $linkedToKey = NULL, $linkedTo = NULL, $linkedKey = NULL, $bRowsToMain = false, $linkedOp = '='){
		$name = $object->name;
		if($linkedTo !== NULL){
			$linkToObj = $this->GetObjectByName($linkedTo);
		}else{
			$linkToObj = $this->mainTable;
		}
		if($linkedKey !== NULL){
			$lK = $linkedKey;
		}else{
			$lK = $object->primaryKey;
		}
		if($linkedToKey !== NULL){
			$l2K = $linkedToKey;
		}else{
			$l2K = $linkToObj->primaryKey;
		}
		$object->SetConditions([
			$linkToObj->alias . '.' . $l2K . ' ' . $linkedOp . ' ' . $object->alias . '.' . $lK
		]);
		$object->bRowsToMain = $bRowsToMain;
		$name = (!empty($object->alias)) ? $object->alias : $name;
		$this->joinObjects[$name] = [
			'type' => $type,
			'obj' => $object,
			'LinkedTo' => $linkedTo
		];
	}

	/**
	 * update dat v db
	 * @param Array $data
	 * @return Boolean
	 */
	function Update($data){
		return $this->mainTable->Update($data, $this->modelObj->name);
	}

	/**
	 * vlozi data(stejne jako to dela update) jako novy zaznam do db a prida defaultni hodnoty
	 * @param Array $data
	 * @return Integer - primarykey id noveho radku /False
	 */
	function Insert($data){
		return $this->mainTable->Insert($data, $this->modelObj->name);
	}

	/**
	 * update radku db ocislovanim sloupce $rowtoupdate hodnouto od $start s prirustkem na radek $step
	 * @param String $rowtoupdate
	 * @param Integer $start
	 * @param Integer $step
	 * @return Boolean
	 */
	function UpdateWithParam($rowtoupdate, $start, $step, $order = '+'){
		$res = OBE_App::$db->query("SET @ind:=" . ($start - 1));
		$sql = 'UPDATE ' . $this->mainTable->table . ' SET ' . $rowtoupdate . ' = (@ind:=@ind' . $order . $step . ')';

		$sql = $this->addConditionsAndOrderBY($sql);

		AdminLogDBAccess::logUpdate((($this->modelObj) ? $this->modelObj->name : '_unknown_'), [
			$sql
		]);

		return OBE_App::$db->query($sql);
	}

	function UpdateRowByValueWithNoEscape($row, $value){
		$sql = 'UPDATE ' . $this->mainTable->table . ' SET ' . $row . ' = ' . $value;

		$sql = $this->addConditionsAndOrderBY($sql);

		AdminLogDBAccess::logUpdate($this->modelObj->name, [
			$sql
		]);

		return OBE_App::$db->query($sql);
	}

	function addConditionsAndOrderBY($sql){
		$where = $this->mainTable->GetConditions();
		$orders = $this->mainTable->GetOrderby();
		if(!empty($where)){
			$sql .= ' WHERE ' . $where . '';
		}
		if(!empty($orders)){
			$sql .= ' ORDER BY ' . $orders . '';
		}
		return $sql;
	}

	/**
	 * kompiluje cely query ve formatu SELECT
	 * @return String
	 */
	function _compileSelectQuery($bWLimits = true, $order = []){
		$elements = $this->_compileToElements($order);
		$sql = $this->_createSqlFromElements($elements);

		$limits = '';
		if($bWLimits){
			$limits = $this->_compileLimits();
		}
		if(!empty($limits)){
			$sql .= ' LIMIT ' . $limits;
		}
		return $sql;
	}

	function _compileToElements($order = []){
		$fields = $this->_compileFields();
		$tables = $this->_compileTables();
		$conditions = $this->_compileConditions();
		$orders = $this->_compileOrderby($order);
		$groups = $this->_compileGroups();
		$havings = $this->_compileHavings();
		return [
			$fields,
			$tables,
			$conditions,
			$groups,
			$havings,
			$orders
		];
	}

	function _createSqlFromElements($elements){
		$sql = 'SELECT DISTINCT ' . $elements[0];
		if(!empty($elements[1])){
			$sql .= ' FROM ' . $elements[1];
		}
		if(!empty($elements[2])){
			$sql .= ' WHERE ' . $elements[2];
		}
		if(!empty($elements[3])){
			$sql .= ' GROUP BY ' . $elements[3];
		}
		if(!empty($elements[4])){
			$sql .= ' HAVING ' . $elements[4];
		}
		if(!empty($elements[5])){
			$sql .= ' ORDER BY ' . $elements[5];
		}
		return $sql;
	}

	/**
	 * kompiluje fieldy z jednotlivych objektů
	 * @return Array
	 */
	function _compileFields(){
		$this->_cacheFields = new DBObjFieldCache();
		$mFields[] = $this->mainTable->GetFields($this->_cacheFields);
		$rOffset = $this->mainTable->resultLength;
		foreach($this->joinObjects as $name => $objectInfo){
			if($nFields = $objectInfo['obj']->GetFields($this->_cacheFields, $rOffset)){
				$mFields[] = $nFields;
			}
			$rOffset += $objectInfo['obj']->resultLength;
		}
		return implode(', ', $mFields);
	}

	/**
	 * kompiluje tabulky s aliasy pro FROM klauzuli
	 * @return String
	 */
	function _compileTables(){
		$tables = '`' . $this->mainTable->table . '` AS `' . $this->mainTable->alias . '`';
		$mainName = $this->mainTable->alias;
		$linkageOrder = [];
		foreach($this->joinObjects as $name => $objectInfo){
			if($objectInfo['type'] === NULL){
				$tables .= ', `' . $objectInfo['obj']->table . '` AS `' . $objectInfo['obj']->alias . '`';
				$this->_cacheConditions[] = $objectInfo['obj']->GetConditions();
			}else{
				$cond = $objectInfo['obj']->GetConditions();
				$tables .= ' ' . $objectInfo['type'] . ' JOIN `' . $objectInfo['obj']->table . '` AS `' . $objectInfo['obj']->alias . '` ON (' . $cond . ')';
			}
		}
		return $tables;
	}

	/**
	 * kompiluje podminky pro WHERE klauzuli
	 * @return String
	 */
	function _compileConditions($chunks = NULL){
		$sql[] = $this->mainTable->GetConditions($chunks);
		$sql = array_merge($sql, $this->_cacheConditions);
		foreach($sql as $key => $chunk){
			if(empty($chunk)){
				unset($sql[$key]);
			}
		}
		return implode(' AND ', $sql);
	}

	/**
	 * kompiluje podminky pro ORDERBY klauzuli
	 * @return String
	 */
	function _compileOrderby($orgOrder){
		$mOrderBY = [];
		if($mainOB = $this->mainTable->GetOrderby()){
			$mOrderBY[] = $mainOB;
		}
		foreach($this->joinObjects as $name => $objectInfo){
			if($aob = $objectInfo['obj']->GetOrderby()){
				$mOrderBY[] = $aob;
			}
		}

		$ford = [];

		if(!empty($orgOrder)){
			foreach($orgOrder as $k => $v){
				if(is_numeric($k)){
					if(in_array($v, $mOrderBY)){
						$ford[] = $v;
						if($kd = array_search($v, $mOrderBY)){
							unset($mOrderBY[$kd]);
						}
					}
				}else{
					if(in_array($k . ' ' . $v, $mOrderBY)){
						$ford[] = $k . ' ' . $v;
						if($kd = array_search($k . ' ' . $v, $mOrderBY)){
							unset($mOrderBY[$kd]);
						}
					}
				}
			}
		}

		foreach($mOrderBY as $k => $v){
			$ford[] = $v;
		}

		return implode(', ', $ford);
	}

	/**
	 * kompiluje podminky pro GROUPBY klauzuli
	 * @return String
	 */
	function _compileGroups(){
		$mGroupBY = [];
		if($mainGB = $this->mainTable->GetGroups()){
			$mGroupBY[] = $mainGB;
		}
		foreach($this->joinObjects as $name => $objectInfo){
			if($agb = $objectInfo['obj']->GetGroups()){
				$mGroupBY[] = $agb;
			}
		}
//		errorClass::Trace("Group BY", $mGroupBY);
		return implode(', ', $mGroupBY);
	}

	function _compileHavings($chunks = NULL){
		if(!empty($this->mainTable->having)){
			$sql[] = $this->mainTable->GetConditions($this->mainTable->having);
//	 		$sql = array_merge($sql, $this->_cacheConditions);
			foreach($sql as $key => $chunk){
				if(empty($chunk)){
					unset($sql[$key]);
				}
			}
//			errorClass::Trace("_compileConditions", $sql);
			return implode(' AND ', $sql);
		}
		return '';
	}

	/**
	 * kompiluje limits na retezec
	 * @return String/NULL
	 */
	function _compileLimits(){
		if(!empty($this->limit)){
			return implode(', ', $this->limit);
		}else{
			return NULL;
		}
	}

	function SetLimits($offset, $length){
		if($offset){
			$this->limit[] = $offset;
		}
		if($length){
			$this->limit[] = $length;
		}
	}

	/**
	 * vrati String dotaz vytvoreny pres _compileSqlQuery()
	 * @return String
	 */
	function GetRawSql(){
		return $this->_compileSelectQuery();
	}

	/**
	 * vrati jeden zaznam z vysledku sql dotazu vytvoreneho pres _compileSqlQuery()
	 * @return Array - 1level/NULL
	 */
	function FetchOne($type = NULL){
		$sql = $this->_compileSelectQuery();
		if($data = OBE_App::$db->FetchSingleArray($sql)){
			return $this->_separate($data);
		}
		return NULL;
	}

	function _separate($data){
//		errorClass::Trace("_separate", sizeof($data));
		$ndata = $this->mainTable->GetOwnPartOfResult($data);
		foreach($this->joinObjects as $obj){
			if($elem = $obj['obj']->GetOwnPartOfResult($data)){
				if($obj['obj']->bRowsToMain){
					$ndata[$this->mainTable->name] = array_merge($ndata[$this->mainTable->name], $elem[$obj['obj']->name]);
				}else{
					$ndata = array_merge($ndata, $elem);
				}
			}
		}
		return $ndata;
	}

	/**
	 * vrati array([0] => array(radek vysledku), ..., [n] => array(...)) z sql dotazu vytvoreneho pres _compileSqlQuery()
	 * @return Array/NULL
	 */
	function FetchAll($orgOrder = []){
		$sql = $this->_compileSelectQuery(true, $orgOrder);
		OBE_App::$db->SetCallBack([
			$this,
			'_separate'
		]);
		if($data = OBE_App::$db->FetchArray($sql)){
//			errorClass::Trace($data);
			return $data;
		}
		return NULL;
	}

	/**
	 * Vrati id vysledku query dotazu vytvoreneho pres _compileSqlQuery()
	 * @return resource vysledku
	 */
	function Select(){
		$sql = $this->_compileSelectQuery();
		return OBE_App::$db->query($sql);
	}

	/**
	 * vrati pocet zaznamu ze sql vytvoreneho pres _compileSqlQuery()
	 * @return Integer/NULL
	 */
	function Count(){
		$sql = $this->_compileSelectQuery(false);
		if($res = OBE_App::$db->FetchSingleArray("SELECT COUNT(*) AS mnum FROM (" . $sql . ") AS xxx")){
			return $res['mnum'];
		}
		return NULL;
	}

	function CountFields(){
		$elements = $this->_compileToElements();
		$elements[0] = ' count(' . $elements[0] . ') as num, ' . $elements[0];
		$sql = $this->_createSqlFromElements($elements);
		if($data = OBE_App::$db->FetchArray($sql)){
			return $data;
		}
		return NULL;
	}

	/**
	 * mazani podle podminek
	 * @return void
	 */
	function Delete(){
		$conditons = $this->mainTable->GetConditions(NULL, true);
		OBE_App::$db->query("DELETE FROM " . $this->mainTable->table . " WHERE " . $conditons);
	}

	/**
	 * nastaveni podminek pro select
	 * @param Mixed $conditions - podminky
	 * @param Boolean $bDropConds - zahodit jiz existujici podminky
	 * @param String $objName - pokud se maj modifikovat podminky nejakeho objektu
	 * @return void
	 */
	function SetConditions($conditions, $bDropConds = false, $objName = NULL){
		if($objName === NULL){
			$sdbo = $this->mainTable;
		}else{
			$sdbo = $this->GetObjectByName($objName);
		}
		if($sdbo){
			$sdbo->SetConditions($conditions, $bDropConds);
		}
	}

	/**
	 * vrati pripojeny object podle jmena
	 * @param $name
	 * @return DBSimpleObjectClass
	 */
	function GetObjectByName($name){
		if($name == $this->mainTable->name){
			return $this->mainTable;
		}
		if(is_array($this->joinObjects)){
			if(array_key_exists($name, $this->joinObjects)){
				return $this->joinObjects[$name]['obj'];
			}
		}
		return NULL;
	}
}

class DBObjFieldCache{

	private $cache = [];

	private $counter = 0;

	/**
	 *
	 * @param DBField $field
	 */
	function getAlt($field){
		$alt = NULL;

		$real = $field->getRealName();

		if($this->isCahced($real)){
			$this->counter++;

			if($field->isComplex() && !$field->hasAlias()){
				$alt = $field->getFirstField() . $this->counter;
			}else{
				$alt = $real . $this->counter;
			}
		}

		$this->cache[] = $real;

		return $alt;
	}

	public function isCahced($field){
		if(in_array($field, $this->cache)){
			return true;
		}
		return false;
	}
}