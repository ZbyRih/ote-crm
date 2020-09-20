<?php

class ModelHelper{

	const SEP_COLS = '/((\w+\.)?(?<![^\w\(\. \'])\w+\(?)/i';

	// druha verze
	/**
	 * funkce zkontroluje zda retezec obsahuje table_name.row_name v cemkoliv
	 * @param String $str
	 * @param Array $regs
	 * @return Boolean
	 */
	static function HaveModel($str, &$regs = []){
		return preg_match('/^.*[a-z0-9_]+\.([a-z0-9_]+)(.*)$/i', $str, $regs);
	}

	/**
	 * funkce zkontroluje zda je prostej retezec(row_name) necim obalenej
	 * @param String $str
	 * @param Array $regs
	 * @return Boolean
	 */
	static function MoreThanField($str, &$regs = []){
		return preg_match('/(^[a-z0-9_]+\()([a-z0-9_]+){1}(.*\))(.*)/i', $str, $regs);
	}

	/**
	 * funkce zkontroluje zda je table_name.row_name necim obaleno
	 * @param String $str
	 * @param Array $regs
	 * @return Boolean
	 */
	static function MoreThanModelAndField($str, &$regs = []){
		$ret = preg_match('~(^[a-z0-9_]+\()([a-z0-9_]+)\.([a-z0-9_]+)(.*)(\).*)$~i', $str, $regs);
		return $ret;
	}

	static function GetModel($str){
		$regs = [];
		if(preg_match('/^([a-z0-9_]+\()*([a-z0-9_]+)\.([a-z0-9_]+)(.*)$/i', $str, $regs)){
			return $regs[2];
		}
		return NULL;
	}

	static function GetModelAndRow($str){
		$regs = [];
		if(preg_match('/^([a-z0-9_]+\()*([a-z0-9_]+)\.([a-z0-9_]+)(.*)$/i', $str, $regs)){
			return [
				$regs[2],
				$regs[3]
			];
		}
		return NULL;
	}

	/**
	 *
	 * @param String $str
	 * @param ModelClass $altModel
	 */
	static function getModelAndCol($str, $altModel){
		if(self::HaveModel($str)){
			return self::GetModelAndRow($str);
		}else{
			return [
				$altModel->name,
				$str
			];
		}
	}

	static function getModelNameAndRowLikeMArray($displayRowName, $basicModel){
		if(is_string($displayRowName)){
			list($modelName, $rowName) = ModelHelper::getModelAndCol($displayRowName, $basicModel);
			$displayRowName = [
				$modelName => [
					$rowName
				]
			];
		}elseif($displayRowName !== NULL){
			if(!is_array($displayRowName) || empty($displayRowName)){
				throw new OBE_Exception('displayRowName neobsahuje nazvy sloupcu', NULL, 0, $displayRowName);
			}
			foreach($displayRowName as $model => $rows){
				if(!is_array($rows)){
					$displayRowName[$model] = [
						$rows
					];
				}
			}
		}
		return $displayRowName;
	}

	static function getDataDump($listRows, $modelData, $deprecateNULL = false){
		$dump = [];
		if(!empty($listRows)){
			foreach($listRows as $model => $rows){
				if(!empty($rows)){
					foreach($rows as $row){
						if(isset($modelData[$model][$row]) || ($modelData[$model][$row] === NULL && !$deprecateNULL)){
							$dump[] = $modelData[$model][$row];
						}
					}
				}
			}
		}
		if(!empty($dump)){
			return $dump;
		}
		return NULL;
	}

	public static function getFields($matches){
		$ret = [];
		foreach($matches as $match){
			if(strpos($match, '(')){
				continue;
			}
			if($match == 'AS'){
				break;
			}
			$ret[] = $match;
		}
		return $ret;
	}
}

class DBFieldManyException extends \Exception{
}

class DBField{

	const SEP_COLS = '/((\w+\.)?(?<![^\w\(\. \'])\w+\(?)/i';

	// druha verze

	var $original = NULL;

	var $fields = NULL;

	var $alias = NULL;

	var $complex = false;

	var $many = false;

	public function __construct($field){
		$this->original = $field;
		$this->extract($field);
	}

	public function extract($source){

		if($source{0} === '!'){

			$items = explode(' ', $source);
			if(count($items) > 3){
				end($items);
				if(strtoupper(prev($items)) == 'AS'){
					$this->alias = end($items);
				}
			}

			$this->original = substr($source, 1);
			$this->fields = [];
			$this->complex = true;
			return;
		}

		if($matches = $this->divide($source)){
			$matches = reset($matches);
			$rmatch = array_reverse($matches);
			foreach($rmatch as $key => $item){
				if($item == 'AS'){ // alias neni complex
					$this->alias = $rmatch[$key - 1];
					break;
				}
			}

			foreach($matches as $key => $item){
				if(strpos($item, '(')){ // ma fci tak je complex, a vsechno co zacina ( preskakujeme
					$this->complex = true;
					continue;
				}
				if($item == 'AS'){ // alias neni complex
					break;
				}
				$this->fields[] = $item;
			}
			$this->many = (count($this->fields) > 1) ? true : false;
			$this->fields = array_unique($this->fields);
		}
	}

	public function divide($source){
		$matches = [];
		if(preg_match_all(self::SEP_COLS, $source, $matches)){
			if(!empty($matches)){ // dostaneme vsechno NECO( nece neco.neco AS neco
				return $matches;
			}
		}
		return NULL;
	}

	public function getRealName(){
		if($this->hasAlias()){
			return $this->getAlias();
		}
		if($this->isComplex()){
			return $this->original;
		}else{
			return $this->getField();
		}
	}

	public function addModel($modelName, $str = NULL){
		$replaces = [];
		foreach($this->fields as $f){
			if(!strpos($f, '.') && !is_numeric($f)){
				$replaces[$f] = $modelName . '.' . $f;
			}
		}
		if(!empty($replaces)){
			$res = str_replace(array_keys($replaces), $replaces, (($str) ? $str : $this->original));
			if(!$str){
				$this->original = $res;
			}
			return $res;
		}
		return (($str) ? $str : $this->original);
	}

	public function replaceAlias($alias, $str = NULL){
		if(!$this->hasAlias()){
			return $this->addAlias($alias, $str);
		}
		if(!$str){
			$this->alias = $alias;
		}
		$res = str_replace(' AS ' . $this->alias, ' AS ' . $alias, (($str) ? $str : $this->original));
		if(!$str){
			$this->original = $res;
		}
		return $res;
	}

	public function addAlias($alias, $str = NULL){
		if(!$str){
			$this->alias = $alias;
		}
		$res = (($str) ? $str : $this->original) . ' AS ' . $alias;
		if(!$str){
			$this->original = $res;
		}
		return $res;
	}

	public function getOrg(){
		return $this->original;
	}

	public function isComplex(){
		return $this->complex;
	}

	public function hasMany(){
		return $this->many;
	}

	public function hasAlias(){
		return ($this->alias != NULL);
	}

	public function getAlias(){
		return $this->alias;
	}

	public function hasModel(){
		if($this->many){
			throw new DBFieldManyException('Příliš mnoho sloupců ' . $this->original);
		}
		if(strpos(reset($this->fields), '.')){
			return true;
		}
		return false;
	}

	public function getModel(){
		if($this->many){
			throw new DBFieldManyException('Příliš mnoho sloupců ' . $this->original);
		}
		$mc = explode('.', reset($this->fields));
		if($mc){
			return $mc[0];
		}
		return NULL;
	}

	public function getField(){
		if($this->many){
			throw new DBFieldManyException('Příliš mnoho sloupců ' . $this->original);
		}
		return reset($this->fields);
	}

	public function getFirstField(){
		return reset($this->fields);
	}
}

class ModelNameKeyPair{

	var $modelName;

	var $rowName;

	/**
	 *
	 * @param ModelClass $model
	 * @param String $itemdef
	 */
	function __construct($model = null, $itemdef){
		if(ModelHelper::haveModel($itemdef)){
			$ret = ModelHelper::getModelAndRow($itemdef);
			$this->modelName = $ret[0];
			$this->rowName = $ret[1];
		}else{
			$this->modelName = $model->name;
			$this->rowName = $itemdef;
		}
	}

	function extract($item){
		if(isset($item[$this->modelName][$this->rowName])){
			return $item[$this->modelName][$this->rowName];
		}
		return NULL;
	}

	function back(){
		return $this->modelName . '.' . $this->rowName;
	}
}
