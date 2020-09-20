<?php

class DBSimpleObjectClass{

	var $table = null;

	var $name = null;

	var $alias = null;

	var $primaryKey = null;

	var $fields = [];

	var $conditions = [];

	var $order = [];

	var $group = [];

	var $having = [];

	var $defaultVals = [];

	var $bRowsToMain = false;

	var $resultOffset = null;

	var $resultLength = null;

	/**
	 * mapovani noveho(vygenerovaneho) nazvu => na originalni nazev( aby se nekrizili nazvy z ruznych tabulek)
	 * @var Array
	 */
	var $mapFields = [];

	static $OPERATORS = [
		'AND',
		'OR',
		'IN',
		'NOT IN'
	];

	/**
	 * Konstruktor
	 * @param String $table
	 * @param String $primaryKey
	 * @param String $alias
	 * @param Array $fields
	 * @param Array $conditions
	 * @param Array $order
	 * @param Array $group
	 * @param Array $defaultVals
	 * @return void
	 */
	function __construct($table, $primaryKey = null, $alias = null, $fields = [], $conditions = [], $order = [], $group = [], $having = [], $defaultVals = [], $name = null){
		$this->table = $table;
		$this->primaryKey = $primaryKey;
		if(empty($primaryKey)){
			$this->primaryKey = 'id';
		}

		$this->alias = $alias;
		if(empty($alias)){
			$this->alias = $this->table;
		}
		if($fields === null){
			$fields = [];
		}
		$this->fields = $fields;
		$this->conditions = $conditions;
		$this->order = $order;
		$this->group = $group;
		$this->having = $having;
		$this->defaultVals = $defaultVals;
		$this->name = $name;
		if(empty($name)){
			$this->name = $this->alias;
		}
	}

	/**
	 * vraci string sloupce oddelene ',' a opatrene aliasem
	 * @return string
	 */
	function GetFields($fieldCache, $resultOffset = 0){
		$fields = [];
		if(empty($this->fields)){
			$this->resultLength = $this->_getNumTableRows();
		}
		foreach($this->fields as $field){
			if(is_array($field)){
				foreach($field as $f){
					$fields[] = $this->_quoteFields($f, $fieldCache);
				}
			}else{
				$fields[] = $this->_quoteFields($field, $fieldCache);
			}
		}
		$fields = array_unique($fields);
		$this->resultLength = sizeof($fields);
		$this->resultOffset = $resultOffset;
		return implode(', ', $fields);
	}

	/**
	 *
	 * @param string $field
	 * @param DBObjFieldCache $fieldCache
	 * @return string
	 */
	function _quoteFields($field, $fieldCache = null){
		// jeste nefunguje uplne dokonale, z funkce to veme jen jeden sloupec ale muze jich tam byt vic
		if($this->alias !== null){
			$cfield = $field;
			$bFunc = false;
			if(strpos($cfield, '`') !== false && strpos($cfield, '`') === 0){
				$field = rtrim(trim($cfield, '`'), '`');
			}else{
				if($this->CheckAvoid($field)){
					return $field;
				}

				if(strpos($field, '@') === false){

					$fo = new DBField($field);
					$field = $fo->addModel($this->alias);

					if($fieldCache){
						if($alt = $fieldCache->getAlt($fo)){
							$this->mapFields[$alt] = $fo->getRealName();
							return $fo->replaceAlias($alt);
						}
					}
					return $fo->getOrg();
				}
			}
		}elseif($this->CheckAvoid($field)){
			return $field;
		}
		return $field;
	}

	function AddFields($fields){
		if(!empty($fields)){
			if(is_array($fields)){
				foreach($fields as $key => $field){
					if(is_numeric($key)){
						if(!in_array($field, $this->fields) || !array_key_exists($field, $this->fields)){
							$this->fields[] = $field;
						}
					}else{
						if(!in_array($key, $this->fields) || !array_key_exists($key, $this->fields)){
							$this->fields[$key] = $field;
						}
					}
				}
			}else{
				if(!in_array($fields, $this->fields) || !array_key_exists($fields, $this->fields)){
					$this->fields[] = $fields;
				}
			}
		}
	}

	/**
	 * kompiluje orderBy na string, vsechny polozky orderby budou oquaotovany
	 * @return String/NULL
	 */
	function GetOrderby(){
		$out = [];
		$revMapFileds = array_flip($this->mapFields);
		if(!empty($this->order)){
			foreach($this->order as $key => $val){
				$dir = ' ASC';
				if(is_numeric($key)){
					if(preg_match('/^(.*) (ASC|DESC)$/i', $val, $regs)){
						$val = $regs[1];
						$dir = ' ' . $regs[2];
					}
					if(isset($revMapFileds[$val])){
						$val = $revMapFileds[$val];
					}else{
						$val = $this->_quoteFields($val);
					}
					$out[] = $val . $dir;
				}else{
					if(preg_match('/^(.*) (ASC|DESC)$/i', $key, $regs)){
						$key = $regs[1];
						$dir = ' ' . $regs[2];
					}
					if(isset($revMapFileds[$key])){
						$key = $revMapFileds[$key];
					}else{
						$key = $this->_quoteFields($key);
					}

					if(is_array($val)){
						$out[] = $key . ' IN (' . implode(', ', $val) . ') ' . $dir;
					}else{
						$out[] = $key . ' ' . $val;
					}
				}
			}
		}
		if(count($out) > 0){
			return implode(', ', $out);
		}
		return null;
	}

	/**
	 * vrati zkompilovany groups
	 * @return String/NULL
	 */
	function GetGroups(){
		if(!empty($this->group)){
			if(!is_array($this->group)){
				return $this->group;
			}
			return implode(', ', $this->group);
		}
		return null;
	}

	/**
	 * kompiluje podminky pro WHERE klauzuli
	 * @param mixed $chunks
	 * @return String
	 */
	function GetConditions($chunks = null, $noQuote = false){
		$saveAlias = $this->alias;
		if($noQuote){
			$this->alias = null;
		}
		if(empty($chunks)){
			$chunks = $this->conditions;
		}
		$conditions = $this->_compileConditionsRek($chunks);
		$sql = OBE_Sql::checkForNULL($conditions);
		$this->alias = $saveAlias;
		return $sql;
	}

	/**
	 * zkompiluje podminky do formatu sql dotazu
	 * ??? vlozena pole oaliasovat kdyz nema v nazvu pred sebou tecku? NELZE kvuli selectu a podstrkovanejm podminkam
	 * pozdeji rozsirit
	 * @param mixed $chunk
	 * @return String
	 */
	function _compileConditionsRek($chunk = null){
		$out = [];

		if(!empty($chunk)){
			foreach($chunk as $key => $val){
				if(is_numeric($key)){
					if(is_array($val)){
						$eout = $this->_compileConditionsRek($val);
						if(!empty($eout)){
							$out[] = '(' . $eout . ')';
						}
					}else{
						if(in_array($val, self::$OPERATORS)){
							$out[] = $val;
						}else{
							if(!in_array(end($out), self::$OPERATORS)){
								$out[] = 'AND';
							}
							/**
							 * kdyz val neodpovida vzoru neco.neco = neco.neco tak oquotovat pole ale proc?
							 */
							if(!preg_match('/^.*([a-zA-Z0-9_]+)(\.[a-zA-Z0-9_]+)? *= *([a-zA-Z0-9_]+)(\.[a-zA-Z0-9_]+)?.*$/', $val)){
								if($val{0} != '!'){
									$val = $this->_quoteFields($val);
								}else{
									$val = mb_substr($val, 1);
								}
							}else{
								$this->CheckAvoid($val);
							}

							$out[] = OBE_Sql::checkForNULL($val);
						}
					}
				}else{
					if(is_array($val)){
						if(in_array($key, self::$OPERATORS)){
							$eout = $this->_compileConditionsRek($val);
							if(!empty($eout)){
								$out[] = $key;
								$out[] = '(' . $eout . ')';
							}
						}else{
							$out[] = 'AND';
							$out[] = $this->_quoteFields($key);

							if(array_intersect(array_keys($val), self::$OPERATORS)){
								$out[] = $this->_compileConditionsRek($val);
							}else{
								$out[] = ' IN ';
								$out[] = '(' . implode(', ', $this->_prepare_val($val)) . ')';
							}
						}
					}else{
						if(!in_array(end($out), self::$OPERATORS)){
							$out[] = 'AND';
						}
						if(in_array($key, self::$OPERATORS)){
							$out[] = $key;
							if(is_array($val)){
								$out[] = '(' . implode(', ', $this->_prepare_val($val)) . ')';
							}else{
								$out[] = '(' . $val . ')';
							}
						}else{
							$out[] = $this->_quoteFields($key);
							$out[] = OBE_Sql::checkForNULL('= ' . $this->_prepare_val($val));
						}
					}
				}
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

	/**
	 * callback array_map pro escapeovani predanych hodnot
	 * @param mixed $value - scalar
	 * @return String
	 */
	static function _prepare_val($value){
		if(!is_string($value) && is_array($value)){
			$prepareVals = [];
			foreach($value as $subValue){
				$prepareVals[] = self::_prepare_val($subValue);
			}
			return $prepareVals;
		}else{
			$escape_val = OBE_App::$db->escape_string($value);
			if($value === null){
				$escape_val = 'null';
			}elseif(is_string($value) && !in_array($value, OBE_Sql::$keyWords)){
				if(!empty($escape_val) && $escape_val{0} !== "'"){
					$escape_val = "'" . $escape_val . "'";
				}else{
					if($escape_val !== '0'){
						$escape_val = "''";
					}
				}
			}
			return $escape_val;
		}
	}

	/**
	 * nastaveni podminek pro select
	 * @param Mixed $conditions - podminky
	 * @param boolean $bDrop - zahodit jiz existujici podminky
	 * @return void
	 */
	function SetConditions($conditions, $bDrop = false){
		if($bDrop){
			$this->conditions = [];
		}
		if(!empty($conditions)){
			if(is_array($conditions)){
				$this->conditions = array_merge($this->conditions, $conditions);
			}else{
				$this->conditions[] = $conditions;
			}
		}
	}

	/**
	 * data doplni o pripadne defautni hodnoty
	 * @param Array $data
	 * @return Array
	 */
	function _mixedWitchDefaultData($data){
		$data_keys = array_keys($data);
		if(!empty($this->defaultVals)){
			foreach($this->defaultVals as $key => $defval){
				if(isset($data[$key])){
					if(is_string($data[$key]) && empty($data[$key])){
						$data[$key] = $defval;
					}
				}else{
					$data[$key] = $defval;
				}
			}
		}
		return $data;
	}

	/**
	 * update dat v db, hodnoty dat se projdou, oescapeuji, tam kde jde o retezec a nejde o hodnoty shodne s globalnim polem $keywords
	 * se tyto uzavrou do uvozovek, pokud tyto jeste neobsahuji
	 * @param Array $data
	 * @param String $modelName
	 * @return Boolean
	 */
	function Update($data, $modelName){
		if(isset($data[$this->primaryKey])){
			$this->conditions = [
				$this->primaryKey => $data[$this->primaryKey]
			];
			unset($data[$this->primaryKey]);
		}

		AdminLogDBAccess::logUpdate($modelName, $data);

		return OBE_App::$db->Update($this->table, $data, $this->GetConditions(null, true));
	}

	/**
	 * vlozi oescapeovana data(jako update) jako novy zaznam do db a prida defaultni hodnoty
	 * @param Array $data
	 * @param String $modelName
	 * @return Integer - primarykey id noveho radku /False
	 */
	function Insert($data, $modelName){
		$data = $this->_mixedWitchDefaultData($data);

		AdminLogDBAccess::logInsert($modelName, $data);

		if(OBE_App::$db->Insert($this->table, $data)){
			return OBE_App::$db->GetLastInsertId();
		}
		return false;
	}

	function GetOwnPartOfResult($data){
		if($this->resultOffset !== null && $this->resultLength !== null){

			$slicedata = array_slice($data, $this->resultOffset, $this->resultLength, true);
			foreach($this->mapFields as $key => $name){
				if($name != $key){
					$slicedata[$name] = $slicedata[$key];
					unset($slicedata[$key]);
				}
			}
			return [
				$this->name => $slicedata
			];
		}
		return null;
	}

	function _getNumTableRows(){
		if($data = OBE_App::$db->FetchArray(
			'select COLUMN_NAME AS col_num FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=\'' . OBE_App::$db->getDBName() . '\' AND table_name=\'' . $this->table . '\'')){
			$this->fields = MArray::GetMutliArrayIndexAsArray($data, 'COLUMN_NAME');
			return sizeof($this->fields);
		}
		return null;
	}

	function CheckAvoid(&$str){
		if($str{0} === '!'){ // vykricnik vsechno preskoci
			$str = mb_substr($str, 1);
			return true;
		}
		return false;
	}
}