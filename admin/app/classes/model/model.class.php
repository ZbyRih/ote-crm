<?php

class ModelClass extends ModelItemClass{

	/**
	 * array(
	 * 'name' => 'Tree'
	 * 'params' => []
	 * )
	 */
	var $name = NULL;

	var $alias = NULL;

	var $table = NULL;

	var $primaryKey = NULL;

	var $rows = [];

	var $conditions = [];

	var $order = [];

	var $group = [];

	var $having = [];

	var $defaultVals;

	public $dyn = [];

	var $behavior;

	/**
	 * popis asociativniho pole
	 * A(
	 * 'modelClassName' => A(
	 * type => ('hasOne', 'hasMany', 'belongsTo', 'hasAndBelongsToMany')
	 * - hasOne - LEFT JOIN (ma vlastni primarni klic a uklada se driv nez master do ktereho se zapise )
	 * - hasMany - dalsi sub dotaz
	 * - belongsTo - INNER JOIN
	 * - hasAndBelongsToMany - dalsi subdotaz
	 * alias => alias pro model
	 * table => table
	 * primaryKey => primarni klic acosiativnich dat
	 * rows => pozadovana pole
	 * foreignKey => klic reprezentujici id modelu v asociativnim modelu
	 * ownpk => true
	 * conditions => sql podminky
	 * ----------------------------------------------------------------------------------------------
	 * hasMany , hasAndBelongsToMany
	 * ----------------------------------------------------------------------------------------------
	 * order => seradit
	 * limit => omezit na pocet
	 * page => odsazeni
	 * joinTable => spojovaci tabulka
	 * joinRows => pripadna dalsi data ze spojovaci tabulky
	 * associationForeignKey => klic reprezuntujici id asociativnihomodelu ve spojovaci tabulce a nebo v modelu
	 * )
	 * )
	 * @var Array
	 */
	var $associatedModels = [];

	var $deprecateSave = false;

	var $callbacks;

	var $data;

	var $id = NULL;

	var $type = NULL;

	var $assocParams = NULL;
 // associacni data z nadrazeneho modelu
	/**
	 * pod klicem z pole $associatedModels je object vytvoreny podle dat z $associatedModels
	 * @var Array(ModelClass)
	 */
	var $_cachedAssoccModelObjects = [];

	/**
	 *
	 * @var array
	 */
	var $_cachedModelName2ClassName = [];

	var $_cachRemainsParams = NULL;

	var $_cachedJoinRows;

	var $_cachedAssocFields;

	var $_cachedLastSaveData;

	var $_saveWasInsert = false;

	var $_saveBelongsToWasInsert = false;

	var $_hasOneParentWasInsert = false;

	/**
	 * Pouzije se kdyz je potreba udrzet associativni modely v cache
	 * @var Boolean
	 */
	var $_cacheTemp = NULL;

	/**
	 * nepouzita promenna
	 * @var array
	 * @access private
	 */
	static $__associations = [
		'hasOne',
		'belongsTo',
		'hasMany',
		'hasAndBelongsToMany'
	];

	/**
	 * promenne ktere se mapuji na object z definice
	 * @var Array
	 */
	static $__assocParams = [
		'name',
		'alias',
		'table',
		'primaryKey',
		'rows',
		'type',
		'order',
		'defaultVals',
		'associatedModels',
		'conditions',
		'deprecateSave'
	];

	/**
	 * promenne ktere se pri mapovani ulozi do $assocParams
	 */
	static $__assocParams2 = [
		'foreignKey',
		'associationForeignKey',
		'ownpk',
		'order',
		'limit',
		'page',
		'joinTable',
		'joinRows'
	];

	static $__assocDefVals = [
		'conditions' => [],
		'order' => [],
		'group' => [],
		'limit' => NULL,
		'page' => 1,
		'type' => 'hasMany',
		'joinRows' => []
	];

	/**
	 * konstruktor
	 */
	function __construct($bInitialize = true){
		if($bInitialize){
			$this->Initialize();
		}
	}

	/**
	 * inicializace promennych ktere nesmi byt prazdne nahradnimi hodnotami
	 */
	function Initialize(){
		if($this->name === null){
			$this->name = get_class($this);
		}
		if($this->primaryKey === null){
			$this->primaryKey = 'id';
		}
		if($this->alias === NULL){
			$this->alias = $this->name;
		}
		if($this->table === NULL){
			$this->table = $this->name;
		}
	}

	/**
	 * nastaveni vnitrnich promennych dle predpisu
	 * @param ModelClass $object
	 * @param Array $definition
	 * @return void
	 */
	function SetFromDefinition($object, $definition){
		foreach(self::$__assocParams as $param_name){
			if(isset($definition[$param_name])){
				$object->$param_name = $definition[$param_name];
			}
		}

		$object->Initialize();

		foreach(self::$__assocDefVals as $key => $val){
			if(!isset($definition[$key])){
				$definition[$key] = $val;
			}
		}
		$object->assocParams = $definition;
	}

	/**
	 * vytvoreni modelu napojenych na tento model
	 * @param Integer $recursive
	 * @return void
	 */
	function _CreateAssociatedModelsRekursive($recursive = -1){
		$this->_clearCache();
		if($recursive != -1){
			if(is_array($this->associatedModels) && !empty($this->associatedModels)){
				foreach($this->associatedModels as $className => $definition){
					if(!isset($this->_cachedAssoccModelObjects[$className])){
						try{
							if(class_exists($className)){
								$object = new $className();
							}else{
								$object = new ModelClass(false);
								$object->name = $className;
							}
						}catch(Exception $e){
							$object = new ModelClass(false);
							$object->name = $className;
						}
						$object->SetFromDefinition($object, $definition);
						$object->_CreateAssociatedModelsRekursive(($recursive - 1));
						$this->_cachedAssoccModelObjects[$className] = $object;
						$this->_cachedModelName2ClassName[$object->name] = $className;

						$this->onCreateAssoc($object);
					}
				}
			}
		}
	}

	/**
	 * vynulovani cache s asociativnima objektama
	 * @return void
	 */
	function _clearCache(){
		$this->_cachedAssoccModelObjects = [];
		$this->_cachedModelName2ClassName = [];
	}

	function _makeCacheTemp(){
		$this->_cacheTemp = [
			'obj' => $this->_cachedAssoccModelObjects,
			'link' => $this->_cachedModelName2ClassName
		];
	}

	function _recallCacheTemp(){
		if(!empty($this->_cacheTemp)){
			$this->_cachedAssoccModelObjects = $this->_cacheTemp['obj'];
			$this->_cachedModelName2ClassName = $this->_cacheTemp['link'];
		}
	}

	/**
	 * oddeli z pole vsechny polozky jez nejsou z tohoto modelu
	 * @param Array $checkArray - pole jez se bude kontrolovat/navrat polozek jez sou jen z tohoto modelu
	 * @param Boolean $bMakeFlat - pokud je polozka pole bude jako pole sloucena s polem vysledku
	 * @param Boolean $bConditions - pokud jde o podminky kontroluje se rekursivne a ke se kontroluje jako row
	 * @return Array - polozky ktere nesjou z tohoto modelu
	 */
	function _separateItemsByModel(&$checkArray, $bMakeFlat = true, $bKeyAsRow = false, $bRecursive = false, $name = NULL, $alias = NULL){
		if($name === NULL){
			$name = $this->name;
		}
		if($alias === NULL){
			$alias = $this->alias;
		}
		$remeinItems = [];
		$currentItems = [];
		if(is_array($checkArray)){
			foreach($checkArray as $key => $item){
				if(is_numeric($key)){
					if($this->_isForThisModel($item, $name, $alias)){
						$currentItems[] = $item;
					}else{
						$remeinItems[] = $item;
					}
				}else{
					if(($key == $name && !$bKeyAsRow) || ($bKeyAsRow && $this->_isForThisModel($key, $name, $alias))){
						if(is_array($item) && $bMakeFlat){
							$currentItems = array_merge($currentItems, $item);
						}else{
							if($bRecursive && is_array($item)){
								$remainI = $this->_separateItemsByModel($item, $bMakeFlat, $bKeyAsRow, $bRecursive, $name, $alias);
								if(!empty($remainI)){
									$remeinItems[$key] = $remainI;
								}
							}
							if($bMakeFlat){
								$currentItems[] = $item;
							}else{
								$currentItems[$key] = $item;
							}
						}
					}else{
						$remeinItems[$key] = $item;
					}
				}
			}
			$checkArray = $this->_modelNameToAlias($currentItems, true, $name, $alias);
		}else{
		}
		return $remeinItems;
	}

	/**
	 * zjisti, jestli je v polozce $val definovan model a jestli je to model schodny s $this
	 * @param $val
	 * @return Boolean
	 */
	function _isForThisModel($val, $name, $alias){
		if(is_array($val)){
			foreach($val as $v){
				if(!$this->_isForThisModel($v, $name, $alias)){
					return false;
				}
			}
		}else if($modelName = ModelHelper::GetModel($val)){
			if(strcmp($modelName, $name) != 0){
				if(strcmp($modelName, $alias) != 0){
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * TODO: proverit moznost odstranit tuto fci :) !!!!!!
	 * @param Array $items
	 * @param Boolean $bRecursive
	 * @return Array
	 */
	function _modelNameToAlias($items, $bRecursive = false, $name = NULL, $alias = NULL){
		$ret = [];
		foreach($items as $key => $item){
			if(is_array($item) && !is_numeric($key)){
				if($bRecursive){
					$item = $this->_modelNameToAlias($item, $bRecursive);
				}
				$key = str_replace($name, $alias, $key);
			}else{
			}
			$ret[$key] = $item;
		}
		return $ret;
	}

	/**
	 * vytvori simple DB object
	 * @param Array $conditions
	 * @param Array $fields
	 * @param Array $order
	 * @param Array $group
	 * @return DBSimpleObjectClass
	 */
	function _createSimpleDBObject($conditions = [], $fields = [], $order = [], $group = []){
		/* zkombinovani podminek */
		if(!empty($this->conditions)){
			$conditions = array_merge($this->conditions, $conditions);
		}
		$anotherConditions = $this->_separateItemsByModel($conditions, false, true, true);
		/* oddeleni polozek tohho modelu od ostatnich */
		$anotherFields = [];
		if(!empty($fields)){
			$anotherFields = $this->_separateItemsByModel($fields);
		}
		if(empty($fields)){
			if(!empty($this->rows)){
				$fields = $this->rows;
				$af = $this->_separateItemsByModel($fields);
				$anotherFields = array_merge($af, $anotherFields);
			}else{
				$fields = [];
			}
		}

		$anotherOrder = [];
		if(!empty($order)){
			$anotherOrder = $this->_separateItemsByModel($order, false, true);
		}
		if(empty($order)){
			if(!empty($this->order)){
				$order = $this->order;
			}else{
				$order = [];
			}
		}
		$anotherGroup = [];
		if(!empty($group)){
			$anotherGroup = $this->_separateItemsByModel($group);
		}
		if(empty($group)){
			if(!empty($this->group)){
				$group = $this->group;
			}else{
				$group = [];
			}
		}

		$anotherHaving = [];
		$having = [];
		if(!empty($having)){
			$anotherHaving = $this->_separateItemsByModel($having);
		}
		if(empty($having)){
			if(!empty($this->having)){
				$having = $this->having;
			}else{
				$having = [];
			}
		}

		return [
			new DBSimpleObjectClass($this->table, $this->primaryKey, $this->alias, $fields, $conditions, $order, $group, $having, $this->defaultVals, $this->name),
			$anotherConditions,
			$anotherFields,
			$anotherOrder,
			$anotherGroup
		];
	}

	/**
	 * vytvori DBObjectClass na zaklade vstupnich dat a dat ze tridy
	 * @return DBObjectClass
	 */
	function _createDBObject($conditions = [], $fields = [], $order = [], $group = [], $recursive = -1, $limit = NULL, $page = 1){
		list($sdbo, $anotherConditions, $anotherFields, $anotherOrder, $anotherGroup) = $this->_createSimpleDBObject($conditions, $fields, $order, $group);
		$add_lk = [];
		foreach($this->_cachedAssoccModelObjects as $modelClass => $obj){
			if(($obj->type == 'hasMany' || $obj->type == 'hasAndBelongsToMany') && !isset($obj->assocParams['joinTable'])){
				$add_lk[] = $this->primaryKey;
				$L2K = $this->primaryKey;
				if(isset($obj->assocParams['foreignKey']) && !empty($obj->assocParams['foreignKey'])){
					$L2K = $obj->assocParams['foreignKey'];
				}

				if(isset($obj->assocParams['associationForeignKey']) && !empty($obj->assocParams['associationForeignKey'])){
					if($obj->type == 'hasMany'){
						array_pop($add_lk);
						$add_lk[] = $obj->assocParams['associationForeignKey'];
					}
				}

				if(!in_array($L2K, $add_lk) && $L2K != $this->primaryKey){
					$add_lk[] = $L2K;
				}
			}
		}

		$sdbo->AddFields($add_lk);
		$dbo = new DBObjectClass($sdbo, $this);
		if($limit !== NULL){
			if(is_array($limit)){
				$dbo->SetLimits($limit[0], $limit[1]);
			}else{
				$dbo->SetLimits((($page !== NULL) ? ($page - 1) * $limit : NULL), $limit);
			}
		}
		$this->_joinAssocModels($dbo, $anotherConditions, $anotherFields, $anotherOrder, $anotherGroup, ($recursive - 1));
		return $dbo;
	}

	/**
	 * pripojeni dat pro hasOne a belongsTo
	 * @param DBObjectClass $dbo
	 * @return Array
	 */
	function _joinAssocModels($dbo, $conditions = [], $fields = [], $order = [], $group = [], $recursive = -1){
		if($recursive != -1){

			if(isset($this->assocParams['joinTable']) && !empty($this->assocParams['joinTable'])){
				$anotherConditions = $this->_separateItemsByModel($conditions, false, true, true, $this->assocParams['joinTable'],
					$this->assocParams['joinTable']);
				if(!isset($this->assocParams['associationForeignKey'])){
					$this->assocParams['associationForeignKey'] = NULL;
				}
				$joinObj = new DBSimpleObjectClass($this->assocParams['joinTable'], $this->assocParams['associationForeignKey'], $this->assocParams['joinTable'],
					$this->assocParams['joinRows'], $conditions, NULL, NULL, NULL, NULL, $this->name);
				$dbo->AddMoreTable($joinObj, NULL, $this->primaryKey, $this->name, $this->assocParams['associationForeignKey'], true);
				$conditions = $anotherConditions;
			}

			foreach($this->_cachedAssoccModelObjects as $modelClass => $obj){
				if($obj->type == 'belongsTo' || $obj->type == 'hasOne'){
					$L2K = $this->primaryKey;
					$LK = $obj->primaryKey;
					list($ndbo, $conditions, $fields, $order, $group) = $obj->_createSimpleDBObject($conditions, $fields, $order, $group);
					if($obj->type == 'belongsTo'){
						if(isset($obj->assocParams['foreignKey']) && !empty($obj->assocParams['foreignKey'])){
							$L2K = $obj->assocParams['foreignKey'];
						}
						if(isset($obj->assocParams['associationForeignKey']) && !empty($obj->assocParams['associationForeignKey'])){
							$LK = $obj->assocParams['associationForeignKey'];
						}
						$type = 'INNER';
					}elseif($obj->type == 'hasOne'){
						if(isset($obj->assocParams['foreignKey']) && !empty($obj->assocParams['foreignKey'])){
							$LK = $L2K = $obj->assocParams['foreignKey'];
						}
						if(isset($obj->assocParams['associationForeignKey']) && !empty($obj->assocParams['associationForeignKey'])){
							$L2K = $obj->assocParams['associationForeignKey'];
						}
						$type = 'LEFT';
					}
					$dbo->AddMoreTable($ndbo, $type, $L2K, $this->name, $LK);
					list($conditions, $fields, $order, $group) = $obj->_joinAssocModels($dbo, $conditions, $fields, $order, $group, ($recursive - 1));
				}
			}
		}
		$this->_cachRemainsParams = [
			$conditions,
			$fields,
			$order,
			$group
		];
		return [
			$conditions,
			$fields,
			$order,
			$group
		];
	}

	/**
	 * vrati pouze prvni nalezeny vysledek
	 * @param fields - pole ktera chceme vratit
	 * @param conditions - podminka pro hledani
	 * @param order - seradit data
	 * @param recursive - do hloubky vetveni modelu
	 */
	function FindOne($conditions = [], $fields = [], $order = [], $recursive = -1){
		$this->_CreateAssociatedModelsRekursive(($recursive - 1));
		$dbo = $this->_createDBObject($conditions, $fields, $order, [], $recursive);
		/* tady budou joiny atd has many atd */
		if($data = $this->fetchOneDBO($dbo)){
			$this->data = $data;
			if($data = $this->FindAssociateData($data[$this->name], $fields, ($recursive - 1))){
				$this->data = array_merge($this->data, $data);
			}
			$this->_clearCache();
			return $this->data;
		}
		$this->_clearCache();
		return NULL;
	}

	function FindOneBy($row, $value, $conditions = [], $fields = [], $order = [], $recursive = -1){
		MArray::unshift($conditions, [
			$row => $value
		]);
		return $this->FindOne($conditions, $fields, $order, $recursive);
	}

	/**
	 * vrati jeden nalezeny zaznam kde je $id hodnoa pro primaryKey
	 * @param integer $id
	 * @param Array $fileds - sloupce ktere se maji vratit
	 * @param integer $recursive - do jake hloubky zanoreni defaultne -1(nekonecno)
	 */
	function FindOneById($id, $fileds = [], $recursive = -1){
		return $this->FindOneBy($this->primaryKey, $id, [], $fileds, [], $recursive);
	}

	/**
	 * vrati vsechny nalezene vysledky
	 * @param fileds
	 * @param conditions
	 * @param order
	 * @param recursive
	 * @param
	 */
	function FindAll($conditions = [], $fields = [], $order = [], $limit = NULL, $page = 1, $recursive = -1, $bRawSql = false){
		$conditions = MArray::AllwaysArray($conditions);
		$this->_CreateAssociatedModelsRekursive(($recursive - 1));
		$dbo = $this->_createDBObject($conditions, $fields, $order, [], $recursive, $limit, $page);
		if($bRawSql){
			return $dbo->GetRawSql();
		}
		if($data = $this->fetchAllDBO($dbo, $order)){
			if(!empty($data)){
				foreach($data as &$item){
					if($a_data = $this->FindAssociateData($item[$this->name], $fields, ($recursive - 1))){
						$item = array_merge($item, $a_data);
					}
				}
			}
			$this->data = $data;
			$this->_clearCache();
			return $this->data;
		}
		$this->_clearCache();
		return NULL;
	}

	function FindAllById($ids, $fields = [], $order = [], $limit = NULL, $page = 1, $recursive = -1, $bRawSql = false){
		return $this->FindAll([
			$this->primaryKey => $ids
		], $fields, $order, $limit, $page, $recursive, $bRawSql);
	}

	/**
	 *
	 * @param DBObjectClass $dbo
	 */
	private function fetchAllDBO($dbo, $order = []){
		$data = $dbo->FetchAll($order);
		return $this->modData($data);
	}

	/**
	 *
	 * @param DBObjectClass $dbo
	 */
	private function fetchOneDBO($dbo){
		$data = $dbo->FetchOne();
		return $this->modData($data);
	}

	/**
	 *
	 * @param $row
	 * @param $value
	 * @param $conditions
	 * @param $fields
	 * @param $group
	 * @param $recursive
	 * @return Integer počet nebo null
	 */
	function CountBy($row, $value, $conditions = [], $fields = [], $group = [], $recursive = -1){
		if($row !== NULL){
			$conditions[$row] = $value;
		}
		$this->_CreateAssociatedModelsRekursive(($recursive - 1));
		$dbo = $this->_createDBObject($conditions, $fields, [], $group, $recursive, NULL, 1);
		return $dbo->Count();
	}

	function Count($fields, $conditions = [], $group = [], $recursive = -1){
		if(!is_array($fields)){
			$fields = [
				$fields
			];
		}
		$this->_CreateAssociatedModelsRekursive(($recursive - 1));
		$dbo = $this->_createDBObject($conditions, $fields, [], $group, $recursive);
		return $dbo->CountFields();
	}

	/**
	 * vrati vysledek ve stylu FindAll
	 * @param row - sloupec
	 * @param value - hodnota/hodnoty
	 * @param fileds - pole ktera vratit
	 * @param conditions - podminky
	 * @param order - seradit
	 * @param recursive - do hloupky vetveni modelu
	 */
	function FindBy($row, $value, $conditions = [], $fields = [], $order = [], $limit = NULL, $page = NULL, $recursive = -1){
		$conditions[$row] = $value;
		$data = $this->FindAll($conditions, $fields, $order, $limit, $page, $recursive);
		return $data;
	}

	function GetFindAllRawSql($conditions = [], $fields = [], $order = [], $limit = NULL, $page = 1, $recursive = -1){
		$this->_CreateAssociatedModelsRekursive(($recursive - 1));
		$dbo = $this->_createDBObject($conditions, $fields, $order, [], $recursive, $limit, $page);
		return $dbo->GetRawSql();
	}

	/**
	 * vrati data pro pripojeni (asociovana data)
	 */
	function FindAssociateData($forItem, $fields, $recursive = -1){
		if(isset($forItem[$this->primaryKey]) && $recursive != -1 && !empty($this->associatedModels)){
			$data = [];
			foreach($this->_cachedAssoccModelObjects as $modelName => $object){
				if($object->type == 'hasMany' || $object->type == 'hasAndBelongsToMany'){
//					pro findby tabulka pokud je potreba
					if(isset($object->assocParams['joinTable'])){
						$table_name = $object->assocParams['joinTable'] . '.';
					}else{
						$table_name = '';
					}
//					prednastaveni podle ceho hledat asociativni data
					$findBy = $object->primaryKey;
					$val = $forItem[$this->primaryKey];
//					dovyreseni podle typu spojeni
					if(isset($object->assocParams['foreignKey'])){
						$findBy = $object->assocParams['foreignKey'];
						if($object->type == 'hasAndBelongsToMany'){
							if(isset($object->assocParams['associationForeignKey']) && !empty($object->assocParams['associationForeignKey'])){
								$findBy = $object->assocParams['associationForeignKey'];
							}else{
								$findBy = $object->primaryKey;
							}
							$val = $forItem[$object->assocParams['foreignKey']];
						}elseif($object->type == 'hasMany' && !isset($object->assocParams['joinTable'])){
							if(isset($object->assocParams['associationForeignKey']) && !empty($object->assocParams['associationForeignKey'])){
								$val = $forItem[$object->assocParams['associationForeignKey']];
								;
							}
						}
					}
//					a hledame
					if($ndata = $object->FindBy($table_name . $findBy, $val, $this->_cachRemainsParams[0], $this->_cachRemainsParams[1],
						$this->_cachRemainsParams[2], $object->assocParams['limit'], $object->assocParams['page'], $recursive)){
						foreach($ndata as $modelData){
							$data[] = $modelData;
						}
					}
				}
			}
			return $data;
		}
		return NULL;
	}

	/**
	 * Uklada bud data predana jako data, pokud data obsahuji primarykey tak dojde k
	 * update, pokud ne dojde k insert, nebo data ktera obsahuje po poslednim ziskani
	 * vysledku
	 * @param Mixed $data - array('model' => array(....),...)
	 * @param Integer $savedId - pokud v datech modelu neni uvedeno primary key tak se pouzije toto
	 * @param Integer $recursion - hloubka rekurzivniho ulozeni -1 je neomezena
	 * @param Boolean $itWasInsert - vynuceni insertu predanych dat modelu
	 * @return boolean
	 */
	function Save(&$data = NULL, $savedId = NULL, $recursion = -1, $itWasInsert = false){
		$data = $this->removeDyn($data);
		$this->_CreateAssociatedModelsRekursive(($recursion - 1));
		$this->_hasOneParentWasInsert = $itWasInsert;
		$this->id = $savedId;
		if($data === NULL){
			$data = $this->data;
		}else{
			$this->data = $data;
		}
		if(!empty($data)){
			if(MArray::isNumericKey($data)){
				$return = true;
				foreach($data as &$item){
					if(!empty($item)){
						$this->id = NULL;
						$return &= $this->_saveParseModel($item, $recursion);
					}
				}
			}else{
				return $this->_saveParseModel($data, $recursion);
			}
			return $return;
		}else{
			OBE_Trace::callPoint('Empty data to save');
		}
		return false;
	}

	function _getAssocModelObjectsManyType(){
		$assocArrayodObjs = [];
		foreach($this->_cachedAssoccModelObjects as $obj){
			if(!in_array($obj->type, [
				'belongsTo',
				'hasOne'
			])){
				$assocArrayodObjs[$obj->name] = $obj;
			}
		}
		return $assocArrayodObjs;
	}

	function _saveParseModel(&$data, $recursion = -1){
		$return = true;
		if(!isset($data[$this->name])){
			$data[$this->name] = [];
		}
// 		OBE_Trace::dump('_saveParseModel', $data);
		$return &= $this->_saveBelongsToBefore($data, $recursion);
		$data[$this->name] = $this->_removeAndCacheJoinRows($data[$this->name]);

		$return &= $this->_saveHasOneWAssocFK($data, $recursion);

		$this->onSaveBefor($data);

		$return &= $this->_save($data[$this->name]);

		$this->onSaveAfter($data);

		$return &= $this->_saveHasOne($data, $recursion);

		if(($recursion - 1) !== -1){

			$assocModels = $this->_getAssocModelObjectsManyType();

			foreach($data as $modelName => $fields){
				if(array_key_exists($modelName, $assocModels)){

					/* takhle uz ty data nikdy nedostanu */

					if(MArray::isNumericKey($fields)){
						foreach($fields as $item){
							$return &= $this->_saveParseAssocModel($modelName, $item, ($recursion - 1));
						}
					}else{
						$return &= $this->_saveParseAssocModel($modelName, $fields, ($recursion - 1));
					}
				}elseif(is_numeric($modelName)){
					foreach($fields as $_modelName => $item){
						if(array_key_exists($_modelName, $assocModels)){
							$return &= $this->_saveParseAssocModel($_modelName, $item, ($recursion - 1));
						}
					}
				}
			}
		}
		if($return){
			return $this->id;
		}
		return $return;
	}

	function _saveBelongsToBefore(&$data, $recursion){
		$result = true;
		$this->_saveBelongsToWasInsert = false;
		if($belongsTo = MArray::GetMArrayItemByKey($this->associatedModels, 'type', 'belongsTo')){
			foreach($belongsTo as $className => $defs){
				if(isset($this->_cachedAssoccModelObjects[$className])){ /* kvuli omezeni rekurzivnosti nemusi bejt vytvoreny */
					$assocObj = $this->_cachedAssoccModelObjects[$className];
					if(!$assocObj->deprecateSave){
						$objName = $assocObj->name;
						if(!isset($data[$objName])){
							$data[$objName] = [];
						}
						$result &= $assocObj->Save($data, NULL, ($recursion - 1));
// 						$data[$objName] = $assocObj->data[$objName];
						if($result !== false){
							$result = true;
							$id = $assocObj->id;
							if(isset($defs['foreignKey'])){
								if(isset($defs['associationForeignKey'])){
									$data[$objName][$defs['associationForeignKey']] = $id;
									$data[$this->name][$defs['foreignKey']] = $id;
								}else{
									$data[$objName][$defs['foreignKey']] = $id;
								}
							}elseif(in_array($assocObj->primaryKey, $this->rows)){
								$data[$objName][$assocObj->primaryKey] = $id;
							}else{
								if($assocObj->_saveWasInsert){
									$this->_saveBelongsToWasInsert = true;
								}
								$this->id = $id;
							}
						}else{
							OBE_Core::$LogToFile->Write("FUCK to vrati nejakou picovinu " . print_r($result, true));
						}
					}
				}
			}
		}
		return $result;
	}

	function _saveHasOneWAssocFK(&$data, $recursion){
		$result = true;
		if($hasOne = MArray::GetMArrayItemByKey($this->associatedModels, 'type', 'hasOne')){
			foreach($hasOne as $className => $defs){
				$chachObjectName = $className;
				if(isset($this->_cachedAssoccModelObjects[$chachObjectName])){ /* kvuli omezeni rekurzivnosti nemusi bejt vytvoreny */
					$asscObj = $this->_cachedAssoccModelObjects[$chachObjectName];
					if(!$asscObj->deprecateSave && isset($defs['ownpk'])){
						$wasParentInsert = $this->_saveWasInsert;
						if(!isset($data[$asscObj->name])){
							$data[$asscObj->name] = [];
						}

						$result &= $asscObj->Save($data, NULL, ($recursion - 1), $wasParentInsert);

						$data[$this->name][$defs['associationForeignKey']] = $asscObj->id;

						if($result !== false){
							$result = true;
						}
					}
				}
			}
		}
		return $result;
	}

	function _saveHasOne(&$data, $recursion){
		$result = true;
		if($hasOne = MArray::GetMArrayItemByKey($this->associatedModels, 'type', 'hasOne')){
			foreach($hasOne as $className => $defs){
				$chachObjectName = $className;
				if(isset($this->_cachedAssoccModelObjects[$chachObjectName])){ /* kvuli omezeni rekurzivnosti nemusi bejt vytvoreny */
					$asscObj = $this->_cachedAssoccModelObjects[$chachObjectName];
					if(!$asscObj->deprecateSave && !isset($defs['ownpk'])){
						$wasParentInsert = $this->_saveWasInsert;
						if(!isset($data[$asscObj->name])){
							$data[$asscObj->name] = [];
						}
						$nid = NULL;
						if(isset($defs['foreignKey'])){
							if(isset($data[$asscObj->name][$defs['foreignKey']])){
								$data[$asscObj->name][$defs['foreignKey']] = $this->id;
							}else{
								$nid = $this->id;
								$data[$asscObj->name][$defs['foreignKey']] = NULL;
							}
						}else{
							$nid = $this->id;
						}

						$result &= $asscObj->Save($data, $nid, ($recursion - 1), $wasParentInsert);

						if($result !== false){
							$result = true;
						}
					}
				}
			}
		}
		return $result;
	}

	function _removeAndCacheJoinRows($data){

		//z dat odstranit pripojeny sloupce
		if(isset($this->assocParams['joinRows']) && !empty($data)){
			foreach($this->assocParams['joinRows'] as $delRow){
				if(isset($data[$delRow])){
					$this->_cachedJoinRows[$delRow] = $data[$delRow];
					unset($data[$delRow]);
				}
			}
		}

		return $data;
	}

	function _saveParseAssocModel($modelName, &$data, $recursion){
		$result = true;
		if($recursion !== -1){
			$modelObject = NULL;
			if(!isset($this->_cachedAssoccModelObjects[$modelName])){
				$name = $this->_cachedModelName2ClassName[$modelName];
				$modelObject = $this->_cachedAssoccModelObjects[$name];
			}else{
				$modelObject = $this->_cachedAssoccModelObjects[$modelName];
			}
			/* preddovyplnit data o foreignKey */

			$this->_preFillData($modelName, $data/*[$modelName]*/, $modelObject);

			$sdata = [
				$modelName => $data
			];
			$result &= $modelObject->Save($sdata, $modelObject->id, ($recursion - 1));
			$data/*[$modelName]*/ = $modelObject->data/*[$modelName]*/;
			if($result !== false){
				$result = true;
				$this->_processJoinTable($modelName, $modelObject);
			}
			$modelObject->id = NULL;
			//nejdriv data pripojena a nakonec zaznam do relacni tabulky jeli nejaka
			//analyzuje jeli nad danym nazvem nejak definovana relacni tabulka
		}
		return $result;
	}

	function _preFillData($modelName, &$data, $object){
		if(isset($this->associatedModels[$modelName])){
			$assocData = $this->associatedModels[$modelName];
		}else{
			$name = $this->_cachedModelName2ClassName[$modelName];
			$assocData = $this->associatedModels[$name];
		}
		/**
		 * pokud je typ modelu has one nebo has many je this->id ve sloupci asoc->foreignKey
		 * a nebo pokud neni definovana joinTable
		 */
		if(!isset($assocData['joinTable'])){
			if(!isset($assocData['foreignKey'])){
				$forKey = $this->name . '_id';
			}else{
				$forKey = $assocData['foreignKey'];
			}
			$asscForKey = NULL;
			if(isset($assocData['associationForeignKey'])){
				$asscForKey = $assocData['associationForeignKey'];
			}
			if($assocData['type'] != 'belongsTo'){
				if($assocData['type'] != 'hasOne'){
					if($assocData['type'] == 'hasAndBelongsToMany' && $asscForKey !== NULL){
						$forKey = $asscForKey;
					}
					if(!isset($data[$forKey])){
						$data[$forKey] = $this->id;
					}
				}else{
					if(!isset($data[$assocData['primaryKey']])){
						$id = $this->_cachedLastSaveData[$this->primaryKey];
						if(isset($assocData['foreignKey'])){
							$data[$assocData['foreignKey']] = $id;
						}else{
							$object->id = $id;
						}
					}
				}
			}
		}
	}

	/**
	 * podle modelu ulozi data do spojovaci tabulky jeli spravne nadefinovano v assoc..models
	 * @param string $modelName
	 * @param object $object
	 */
	function _processJoinTable($modelName, $object){
		if(isset($this->associatedModels[$modelName])){
			$assocData = $this->associatedModels[$modelName];
		}else{
			$name = $this->_cachedModelName2ClassName[$modelName];
			$assocData = $this->associatedModels[$name];
		}
		if(isset($object->assocParams['joinTable'])){
			$sdbo = new DBSimpleObjectClass($assocData['joinTable']);
			$dbo = new DBObjectClass($sdbo, $this);

			$dbo->table = $assocData['joinTable'];
			// vytvoreni jmen klicu
			if(isset($assocData['foreignKey'])){
				$forKey = $assocData['foreignKey'];
			}else{
				$forKey = $this->name . '_id';
			}
			if(isset($assocData['associationForeignKey'])){
				$ascKey = $assocData['associationForeignKey'];
			}else{
				$ascKey = $object->name . '_id';
			}
			//nastaveni dat
			//vlozeni dat do databaze
			$dbo->conditions[] = $forKey . ' = ' . $this->id;
			$dbo->conditions[] = $ascKey . ' = ' . $object->id;
			if(($num = $dbo->Count()) > 0){
				if(isset($object->_cachedJoinRows) && !empty($object->_cachedJoinRows)){
					$data = $object->_cachedJoinRows;
					$dbo->Update($data);
				}
			}else{
				$data[$forKey] = $this->id;
				$data[$ascKey] = $object->id;
				$dbo->Insert($data);
			}
		}
	}

	function _save(&$data){
		$this->_saveWasInsert = false;
		if($this->id !== NULL && !array_key_exists($this->primaryKey, $data)){
			$data[$this->primaryKey] = $this->id;
		}
		if(isset($data[$this->primaryKey]) && $data[$this->primaryKey] !== NULL && !$this->_saveBelongsToWasInsert && !$this->_hasOneParentWasInsert){
			$this->_update($data);
		}else{
			$this->_saveWasInsert = true;
			$this->_insert($data);
		}
		$this->_cachedLastSaveData = $data;
		return true;
	}

	function _insert(&$data){
		$dbo = $this->_createDBObject();
		$dbo->defaultVals = $this->defaultVals;
		if($this->id !== NULL){
			$data[$this->primaryKey] = $this->id;
		}
		if(!isset($data[$this->primaryKey])){
			$data[$this->primaryKey] = NULL;
		}

		$this->onInsertBefor($data);

		if($this->id = $dbo->Insert($data)){
			$data[$this->primaryKey] = $this->id;
			$this->onInsertAfter($data);
			return $this->id;
		}

		if(isset($data[$this->primaryKey])){
			$this->id = $data[$this->primaryKey];
			$this->onInsertAfter($data);
		}
		return false;
	}

	function _update(&$data){
		$dbo = $this->_createDBObject();
		$dbo->defaultVals = $this->defaultVals;

		$this->onUpdateBefor($data);

		$dbo->Update($data);

		$this->onUpdateAfter($data);

		$this->id = $data[$this->primaryKey];
	}

	/**
	 * mazani defaultne se nemaze cascadou
	 * @param Mixed $id - pole hodnot nebo jen jedna integer hodnota, nebo nic pak se maze vsechno
	 * @param Array $conditions - podminky omezujici mazani
	 * @param Boolean $cascade - smazat i asociovana data
	 */
	function Delete($id = NULL, $conditions = NULL, $cascade = false){
		$this->_CreateAssociatedModelsRekursive(($cascade) ? -2 : -1);
		$this->_makeCacheTemp();
		$conditions = MArray::AllwaysArray($conditions);
		$dbo = $this->_createDBObject($conditions);

		if($id !== NULL){
			$ids = MArray::AllwaysArray($id);
		}else{
			$ids = [];
			$dbo->SetConditions($conditions, true);
			if($datas = $dbo->FetchAll()){
				$ids = MArray::getKeyValsFromModels($datas, $this->name, $this->primaryKey);
			}
		}

		if(!empty($ids)){
			foreach($ids as $id){

				if($this->onDelete($id, $conditions, $cascade)){
					if($cascade){
						$this->_deleteCheckBelongsTo($id);
					}

					$dbo->SetConditions($conditions, true);

					if($id !== NULL){
						$dbo->SetConditions($this->primaryKey . ' = ' . $id);
					}

					$dbo->Delete();
				}
			}
		}
		return true;
	}

	/**
	 * TODO: jeste se musi osetrit spojovaci tabulky
	 * @param Integer $id - id rodice
	 * @return void
	 */
	function _deleteCheckBelongsTo($id){
		$f_fields = [];
		$t_fields = [];
		$assocM = [];
		if(!empty($this->associatedModels)){
			foreach($this->associatedModels as $modelName => $params){
				if($params['type'] == 'belongsTo' || $params['type'] == 'hasOne'){
					$f_pk = $this->primaryKey;
					$t_pk = $this->_cachedAssoccModelObjects[$modelName]->primaryKey;
					if($params['type'] == 'belongsTo'){
						if(isset($params['foreignKey']) && !empty($params['foreignKey'])){
							$f_pk = $params['foreignKey'];
						}
					}elseif($params['type'] == 'hasOne'){
						if(isset($params['foreignKey']) && !empty($params['foreignKey'])){
							$f_pk = $params['foreignKey'];
							$t_pk = $params['foreignKey'];
							if(isset($params['associationForeignKey']) && !empty($params['associationForeignKey'])){
								$f_pk = $params['associationForeignKey'];
							}
						}
					}
					$f_fields[$modelName] = $f_pk;
					$t_fields[$modelName] = $t_pk;
					$assocM[$modelName] = $params;
				}
			}
		}
		if(!empty($assocM)){
			$fields = array_unique(array_values($f_fields));
			if($delForItem = $this->FindOneById($id, $fields, 0)){
				$this->_recallCacheTemp();
				foreach($assocM as $modelName => $params){
					$conditions = [];
					$delId = $delForItem[$this->name][$f_fields[$modelName]];

					if($t_fields[$modelName] != $this->_cachedAssoccModelObjects[$modelName]->primaryKey){
						$conditions = [
							$t_fields[$modelName] => $delForItem[$this->name][$f_fields[$modelName]]
						];
						$delId == NULL;
					}
					$this->_cachedAssoccModelObjects[$modelName]->Delete($delId, $conditions);
				}
			}
		}
	}

	function GetListFromData($rowName, $modelName, $data, $list = []){
		if(is_array($data)){
			list($key, $val) = each($data);
			reset($data);
			if(is_numeric($key)){
				foreach($data as $item){
					$list = $this->GetListFromData($rowName, $modelName, $item, $list);
				}
			}else{
				if(isset($data[$rowName])){
					$list[] = $data[$rowName];
				}elseif(isset($data[$modelName])){
					$list = $this->GetListFromData($rowName, $modelName, $item, $list);
				}
			}
		}
		return $list;
	}

	function GenerateScheme($recursive = -1){
		static $directItems = [
			'name',
			'alias',
			'table',
			'primaryKey',
			'type'
		];
		static $arrays = [
			'rows',
			'conditions',
			'group',
			'order'
		];
		static $assocp = [
			'foreignKey',
			'associationForeignKey',
			'joinTable'
		];
		$this->_CreateAssociatedModelsRekursive(($recursive - 1));
		$tab = str_pad('', -1 * $recursive, '	');
		$out = $tab . (($recursive === -1) ? "\n" : '') . 'className = ' . get_class($this) . "\n";
		foreach($directItems as $item){
			$out .= $tab . $item . ' = ' . $this->{$item} . "\n";
		}
		foreach($arrays as $item){
			$out .= $tab . $item . ' = ' . preg_replace('/ +/', ' ', str_replace("\n", '', print_r($this->{$item}, true))) . "\n";
		}
		if($recursive < -1){
			foreach($assocp as $item){
				$str = '';
				if(isset($this->assocParams[$item])){
					$str = $this->assocParams[$item];
				}
				$out .= $tab . $item . ' = ' . $str . "\n";
			}
			$out .= $tab . 'joinRows = ' . preg_replace('/ +/', ' ', str_replace("\n", '', print_r($this->assocParams['joinRows'], true))) . "\n";
		}
		foreach($this->_cachedAssoccModelObjects as $key => $obj){
			$out .= $tab . 'AssocModels < ' . $key . "\n";
			$out .= $obj->GenerateScheme(($recursive - 1));
		}
		if($recursive >= -1){
			OBE_Trace::dump($out);
		}
		return $out;
	}

	function removeAssociateModels(){
		$this->associatedModels = [];
		return $this;
	}

	function removeAssociateModelsByName($name){
		if(isset($this->associatedModels[$name])){
			unset($this->associatedModels[$name]);
		}
	}

	function removeManyTypeAssociatedModels(){
		$this->removeAssociatedModelsByType([
			'hasMany',
			'hasAndBelongsToMany'
		]);
	}

	function removeAssociatedModelsByType($types = NULL){
		if($types !== NULL){
			if(!is_array($types)){
				$types = [
					$types
				];
			}
			foreach($this->associatedModels as $key => $assoc){
				if(in_array($assoc['type'], $types)){
					unset($this->associatedModels[$assoc['type']]);
				}
			}
		}else{
			$this->removeAssociateModels();
		}
	}

	/**
	 *
	 * @param String $rowName
	 * @param String $value - hodnota bude oquatovana
	 * @return void
	 */
	function updateRow($rowName, $value){
		if($this->id){
		}
	}

	/**
	 *
	 * @param String $rowName
	 * @param String $value - hodnota nebude oquotovana
	 * @param int $id - primarni klic
	 * @return void
	 */
	function setRow($colName, $value, $id = NULL){
		$this->_CreateAssociatedModelsRekursive(-2);
		if(ModelHelper::HaveModel($colName)){
			list($model, $col) = ModelHelper::GetModelAndRow($colName);
		}else{
			$model = $this->name;
			$col = $colName;
		}
		if($model == $this->name){
			if($id == NULL && is_array($this->data) && array_key_exists($this->name, $this->data)){
				if(array_key_exists($this->primaryKey, $this->data[$this->name])){
					$id = $this->data[$this->name][$this->primaryKey];
				}
			}
			if(!is_null($id)){
				$dbso = new DBSimpleObjectClass($this->table, $this->primaryKey, NULL, NULL, [
					$this->primaryKey => $id
				]);
				$dbo = new DBObjectClass($dbso, $this);
				return $dbo->UpdateRowByValueWithNoEscape($col, $value);
			}
		}else{
			$return = false;
			foreach($this->_cachedAssoccModelObjects as $obj){
				$obj->data = $this->data;
				$return |= $obj->setRow($colName, $value, $id);
			}
			return $return;
		}
		$this->_clearCache();
		return false;
	}

	public function addAssociatedModels($asocModels){
		$this->associatedModels = array_merge($this->associatedModels, $asocModels);
	}

	public function isolateAssociatedModels($asocModelToIsolate){
		$this->associatedModels = [
			$asocModelToIsolate => $this->associatedModels[$asocModelToIsolate]
		];
	}

	public function addCondition($conditions){
		$this->conditions = array_merge($this->conditions, $conditions);
	}

	public function isSetPK($item){
		if(!isset($item[$this->name])){
			throw new OBE_Exception('in array $item is not key ' . $this->name, get_class($this) . '::isSetPK');
		}

		if(!isset($item[$this->name][$this->primaryKey])){
			return false;
		}

		if($item[$this->name][$this->primaryKey] == NULL || empty($item[$this->name][$this->primaryKey])){
			return false;
		}

		return true;
	}

	public function dump(){
		list($dbso) = $this->_createSimpleDBObject();
		$dbo = new DBObjectClass($dbso, $this);
		$sql = $dbo->GetRawSql();
		OBE_Trace::dump($sql);
	}

	/**
	 *
	 * @param string $className
	 * @return ModelClass
	 */
	public function getAssocObj($className){
		if(!isset($this->_cachedAssoccModelObjects[$className])){
			OBE_Trace::callPoint('V ' . get_class($this) . '->_cachedAssoccModelObjects neexistuje index ' . $className);
			exit();
		}
		return $this->_cachedAssoccModelObjects[$className];
	}

	public function getExistsCols($cols){
		if(isset($cols[$this->name])){
			$ret[$this->name] = [];
			foreach($cols[$this->name] as $col){
				if(in_array($col, $this->rows)){
					$ret[$this->name][] = $col;
				}
			}
			return $ret;
		}
		return $cols;
	}

	private function removeDyn($data){
		$keys = array_combine($this->dyn, $this->dyn);
		reset($data);
		if(is_numeric(key($data))){
			foreach($data as &$d){
				if(isset($d[$this->name])){
					$d[$this->name] = array_diff_key($d[$this->name], $keys);
				}
			}
		}else{
			if(isset($data[$this->name])){
				$data[$this->name] = array_diff_key($data[$this->name], $keys);
			}
		}
		return $data;
	}

	public function modData($data){
		return $data;
	}
}