<?php
/**
 * overi zdali pole $array obsahuje klice $keys
 * @param array $keys
 * @param array $array
 * @return boolean
 */

class MArray{
	const NEAREST_DEFAULT = 0;
	const NEAREST_LOWER = 1;
	const NEAREST_HIGHER = 2;

	/**
	 * Zjistuje jestli je predane pole polem polí
	 *
	 * @param array $array - vstupni pole
	 * @return boolean - vraci true pokud je pole pole jinak false
	 */
	static function is_multiarray($array){
		if(is_array($array) || $array instanceof ArrayAccess){
			$element = array_pop($array);
			if(is_array($element) || $array instanceof ArrayAccess){
				return true;
			}
		}
		return false;
	}

	/**
	 * projde predane pole a z jeho podpolozek udela pole do ktereho ulozi hodnoty prvku source_key, index je automaticky
	 * array(array(source_key, ...), array(source_key, ...)) => array(source_key, source_key, ...)
	 * @param array $mArray
	 * @param string $source_key
	 * @return array
	 */
	static function GetMutliArrayIndexAsArray($mArray, $source_key){
		$ret_array = [];
		if(!empty($mArray)){
			foreach($mArray as $item){
				if(is_array($item) && isset($item[$source_key])){
					$ret_array[] = $item[$source_key];
				}
			}
		}
		return $ret_array;
	}

	/**
	 * projde pole a z nej vrati jen polozky ktere maji pod klicem - $fkey hodnotu $fvalue, zachova indexovani pole
	 * array(n1 => array(fkey, fvalue, ...), n2 => array(fkey, fvalue, ...)) => array(n1=> array(...), n3=>[])
	 * @param Array $array
	 * @param String $fkey
	 * @param String $fvalue
	 * @return Array
	 */
	static function FilterMArray($array, $fkey, $fvalue){
		$ret = [];
		foreach($array as $key => $item){
			if($item[$fkey] == $fvalue){
				$ret[$key] = $item;
			}
		}
		return $ret;
	}

	/**
	 * projde pole a z nej vrati pole z vyjmenovanymi klíči
	 * @param Array $array
	 * @param String $fkeys
	 * @return Array
	 */
	static function FilterValsByKeys($array, $fkeys){
		$fkeys = MArray::AllwaysArray($fkeys);
		return array_intersect_key($array, array_flip($fkeys));
	}

	/**
	 * projde pole a z nej vrati jen polozky ktere maji pod klicem - $fkey hodnotu $fvalue, zachova indexovani pole
	 * array(n1 => array(fkey, fvalue, ...), n2 => array(fkey, fvalue, ...)) => array(n1=> array(...), n3=>[])
	 * @param Array $array
	 * @param String $fkey
	 * @param String $fvalue
	 * @return Array
	 */
	static function FilterNegMArray($array, $fkey, $fvalue){
		$ret = [];
		foreach($array as $key => $item){
			if($item[$fkey] != $fvalue){
				$ret[$key] = $item;
			}
		}
		return $ret;
	}

	/**
	 * projde pole a vytvori pole v nemz bude klic klic subpole a k nemu bude prirazena hodnota z pole s klicem $val_name
	 * prochazene pole je ve tvaru array(key => array(..., val_name, ...), key => array(..., val_name, ...), ...) => array(key => $item[val_name], ...)
	 *
	 * @param Array $array
	 * @param String $val_name
	 * @return Array
	 */
	static function MapVal($array, $val_name){
		$ret = [];
		if(is_array($array) || $array instanceof ArrayAccess){
			foreach($array as $key => $item){
				if(is_array($item) && isset($item[$val_name])){
					$ret[$key] = $item[$val_name];
				}
			}
		}
		return $ret;
	}

	/**
	 * projde pole a vytvori pole v nemz bude klic hodnota z pole s klicem $key_name a k nemu bude prirazena hodnota z pole s klicem $val_name
	 * prochazene pole je ve tvaru array(array(key_name, val_name, ...), array(key_name, val_name, ...), ...) => array($item[key_name] => $item[val_name], ...)
	 *
	 * @param Array $array
	 * @param String $key_name
	 * @param String $val_name
	 * @return Array
	 */
	static function MapValToKey($array, $key_name, $val_name){
		$ret = [];
		if(is_array($array) || $array instanceof ArrayAccess){
			foreach($array as $item){
				if(is_array($item) && isset($item[$key_name]) && isset($item[$val_name])){
					$ret[$item[$key_name]] = $item[$val_name];
				}
			}
		}
		return $ret;
	}

	/**
	 * projde pole a vytvori pole v nemz bude klic hodnota z pole s klicem $key_name a k nemu bude prirazena cela polozka
	 * prochazene pole je ve tvaru array(n => array(indexName, ...), ...) => array(indexName =>  array(...), ...)
	 *
	 * @param Array $array
	 * @param String $indexName
	 * @return Array
	 */
	static function MapItemToKey($array, $indexName){
		$ret = [];
		if(is_array($array) || $array instanceof ArrayAccess){
			foreach($array as $item){
				if(is_array($item) && isset($item[$indexName])){
					$ret[$item[$indexName]] = $item;
				}
			}
		}
		return $ret;
	}

	static function MapObjectItemToKey($arrayOfObjs, $propertyWithId){
		//		errorClass::Trace('in', $arrayOfObjs);
		$ret = [];
		if(is_array($arrayOfObjs) || $arrayOfObjs instanceof ArrayAccess){
			foreach($arrayOfObjs as $item){
				if(is_object($item) && property_exists($item, $propertyWithId)){
					$ret[$item->{$propertyWithId}] = $item;
				}
			}
		}
		//		errorClass::Trace('out', $ret);
		return $ret;
	}

	/**
	 * projde pole a vytvori pole v nemz bude klic hodnota z pole s klicem $key_name a k nemu bude prirazeny celi prvek pole
	 * prochazene pole je ve tvaru array(array(modelName => array(indexName, ...)), ...) => array(indexName => array(modelName => array(...)), ...)
	 *
	 * @param Array $array
	 * @param String $modelName
	 * @param String $indexName
	 * @return Array
	 */
	static function MapModelItemToKey($array, $modelName, $indexName){
		$ret = [];
		if((is_array($array) || $array instanceof ArrayAccess) && !empty($array)){
			foreach($array as $item){
				if(is_array($item) && isset($item[$modelName][$indexName])){
					$ret[$item[$modelName][$indexName]] = $item;
				}
			}
		}
		return $ret;
	}

	static function FilterModelByValue($marray, $modelName, $valKey, $value){
		$return = [];
		if(is_array($marray) || $marray instanceof ArrayAccess){
			foreach($marray as $item){
				if(isset($item[$modelName]) && isset($item[$modelName][$valKey]) && $item[$modelName][$valKey] == $value){
					$return[] = $item;
				}
			}
		}
		return $return;
	}

	/**
	 * projde pole a vytvori pole v nemz bude klic hodnota z pole s klicem $key_name a k nemu bude prirazena hodnota z pole s klicem $val_name
	 * prochazene pole je ve tvaru array(array(key_name, val_name, ...), array(key_name, val_name, ...), ...) => array($item[key_name] => $item[val_name], ...)
	 *
	 * @param Array $array
	 * @param String $modelName
	 * @param String $key_name
	 * @param String $val_name
	 * @return Array
	 */
	static function MapValToKeyFromMArray($array, $modelName, $key_name, $val_name){
		$ret = [];
		if((is_array($array) || $array instanceof ArrayAccess) && !empty($array)){
			foreach($array as $item){
				if(isset($item[$modelName]) && isset($item[$modelName][$key_name]) && isset($item[$modelName][$val_name])){
					$ret[$item[$modelName][$key_name]] = $item[$modelName][$val_name];
				}
			}
		}
		return $ret;
	}

	/**
	 *
	 * @param array $array
	 * @param array $fields
	 */
	static function GetMFields($array, $fields, $model = null){
		$ret = [];
		foreach ($fields as $ff){

			if(!strpos($ff, '.')){
				$f = $ff;
				$m = $model;
			}else{
				list($m, $f) = explode('.', $ff);
			}

			if(isset($array[$m][$f])){
				$ret[] = $array[$m][$f];
			}
		}
//  		OBE_Trace::dump($array, $fields, $ret);
		return $ret;
	}

	/**
	 * projde pole a vytvori pole v nemz bude klic hodnota z pole s klicem $key_name a k nemu bude prirazena hodnota z pole s klicem $val_name
	 * prochazene pole je ve tvaru array(array(key_name, val_name, ...), array(key_name, val_name, ...), ...) => array($item[key_name] => $item[val_name], ...)
	 *
	 * @param Array $array
	 * @param String $modelName
	 * @param String $key_name
	 * @param String $val_name
	 * @return Array
	 */
	static function MapValToKeyFromMArrays($array, $keyModel, $key, $valModel, $val){
		$ret = [];
		if((is_array($array) || $array instanceof ArrayAccess) && !empty($array)){
			foreach($array as $item){
				if(isset($item[$keyModel]) && isset($item[$keyModel][$key])
					&& isset($item[$valModel]) && isset($item[$valModel][$val])){
					$ret[$item[$keyModel][$key]] = $item[$valModel][$val];
				}
			}
		}
		return $ret;
	}


	/**
	 * Vraci polozku/y pole kde polozka/y ma pod klicem $key hodnotu $val $item[$key]==$val
	 *
	 * @param array $mArray
	 * @param string $key
	 * @param mixed $val
	 *
	 * @return array - polozka pole
	 */
	static function GetMArrayItemByKey($mArray, $key, $val){
		$ret = [];
		foreach($mArray as $index => $item){
			if(is_array($item) && isset($item[$key]) && $item[$key] == $val){
				$ret[$index] = $item;
			}
		}
		if(!empty($ret)){
			return $ret;
		}
		return NULL;
	}

	/**

	*/
	static function GetMArrayForOneModel($mArray, $modelName){
		$ret = [];
		if(is_array($mArray) || $mArray instanceof ArrayAccess){
			foreach($mArray as $key => $item){
				if(isset($item[$modelName])){
					$ret[] = $item[$modelName];
				}
			}
		}
		return $ret;
	}

	static function getKeyValsFromModels($mArray, $modelName, $val_key){
		$ret = [];
		if(is_array($mArray) || $mArray instanceof ArrayAccess){
			foreach($mArray as $key => $item){
				if(isset($item[$modelName][$val_key])){
					$ret[] = $item[$modelName][$val_key];
				}
			}
		}
		return $ret;
	}

	static function GetMArrayForOneModelIndexed($mArray, $modelName, $indexKey, $sliceOffset = NULL){
		$ret = [];
		if(is_array($mArray) || $mArray instanceof ArrayAccess){
			foreach($mArray as $key => $item){
				if(isset($item[$modelName])){
					$vals = array_values($item[$modelName]);
					if($sliceOffset){
						$vals = array_slice($vals, $sliceOffset);
					}
					$ret[$item[$modelName][$indexKey]] = $vals;
				}
			}
		}
		return $ret;
	}

	static function colapseMArray($mArray){
		$ret = [];
		if(is_array($mArray) || $mArray instanceof ArrayAccess){
			foreach($mArray as $key => $item){
				$subRet = [];
				foreach($item as $subitems){
					$subRet = array_merge($subRet, $subitems);
				}
				$ret[$key] = $subRet;
			}
		}
		return $ret;
	}

	static function setSubItems($mArray, $key, $value){
		foreach ($mArray as $k => $items){
			$mArray[$k][$key] = $value;
		}
		return $mArray;
	}

	static function setSubItemsIfNot($mArray, $key, $value){
		foreach ($mArray as $k => $items){
			if(!isset($mArray[$k][$key])){
				$mArray[$k][$key] = $value;
			}
		}
		return $mArray;
	}

	/**
	 * copa asi tahle funkce dela
	 */
	static function RetMultiModelArray(&$data){
		if(!empty($data)){
			if(!self::isNumericKey($data)){
				$data = [$data];
			}
		}else{
			$data = [];
		}
		return $data;
	}

	static function MergeMultiArray($array1, $array2){
		$ret = $array1;
		foreach($array2 as $key => $val){
			if(is_array($val)){
				$a1 = $val;
			}else{
				$a1 = [$val];
			}
			if(isset($array1[$key])){
				if(is_array($array1[$key])){
					$ret[$key] = array_merge($ret[$key], $a1);
				}else{
					$ret[$key] = array_merge([$ret[$key]], $a1);
				}
			}else{
				$ret[$key] = $a1;
			}
		}
		return $ret;
	}

	/**
	 * funkce spojujici vicero poli s kontrolou na obsah a typ
	 *
	 * @param [...]
	 * @return array
	 */
	static function MultiMerge(){
		$num_args = func_num_args();
		if($num_args > 0){
			$compile = [];
			for($i = 0; $i < $num_args; $i++){
				$next = func_get_arg($i);
				if(!empty($next) && is_array($next)){
					$compile = array_merge($compile, $next);
				}
			}
			if(!empty($compile)){
				return $compile;
			}
		}
		return NULL;
	}

	static function MergeArrays(&$arrayOut, $arrayToJoin, $outKey, $uKey){
		foreach($arrayOut as &$item){
			if(isset($arrayToJoin[$item[$outKey]])){
				$item[$uKey] = $arrayToJoin[$item[$outKey]];
			}
		}
	}

	static function MergeArrays3(&$arrayOut, $arrayToJoin, $tKey){
		foreach($arrayOut as $key => &$item){
			if(isset($arrayToJoin[$key])){
				$item[$tKey] = $arrayToJoin[$key];
			}
		}
	}

	static function RemapArrayKeys($array, $keysForSwap){
		$rArray = [];
		foreach($array as $index => $item){
			$rArray[$keysForSwap[$index]] = $item;
		}
		return $rArray;
	}

	static function PopEmptyValues($vals){
		if(!is_array($vals) || $vals instanceof ArrayAccess){
			$vals = [$vals];
		}
		foreach($vals as $key => $val){
			if(empty($val) && $val != '0'){
				unset($vals[$key]);
			}
		}
		return $vals;
	}

	static function IsEmpty($array){
		$array = self::AllwaysArray($array);
		foreach($array as $item){
			if(!empty($item) || $item == 0){
				return false;
			}
		}
		return true;
	}

	static function CleanDoubledVals($array){
		$narray = [];
		foreach($array as $modelname => $rows){
			foreach($rows as $row){
				if(!isset($narray[$modelname]) || !in_array($row, $narray[$modelname])){
					$narray[$modelname][] = $row;
				}
			}
		}
		return $narray;
	}

	static function AllwaysArray($array){
		if(!is_array($array) || $array instanceof ArrayAccess){
			if($array === NULL){
				return [];
			}else{
				return [$array];
			}
		}
		return $array;
	}

	/**
	 * funkce vrati associativni pole kde jsou klice z prvniho pole i v druhem
	 * @param Array $arrayToIntersect
	 * @param Array $keys
	 * return Array
	 */
	static function intersect_key($arrayToIntersect, $keys){
		return array_intersect_key($arrayToIntersect, array_flip($keys));
	}

	static function unshift(&$sourceArray, $unshiftArray){
		if(!is_array($sourceArray)){
			$sourceArray = [];
		}
		$keys = array_merge(array_keys($unshiftArray), array_keys($sourceArray));
		$values = array_merge($unshiftArray, $sourceArray);
		$sourceArray = array_combine($keys, $values);
	}

	static function isNumericKey($array){
		if(is_array($array) || $array instanceof ArrayAccess){
			reset($array);
			if(is_numeric(key($array))){
				return true;
			}
		}
		return false;
	}

	static function getListFromTree($tree, $subKey, &$items = [], $onlyId = NULL){
		foreach($tree as $item){
			$subItems = NULL;
			if(isset($item[$subKey])){
				$subItems = $item[$subKey];
				unset($item[$subKey]);
			}
			if($onlyId){
				$items[] = $item[$onlyId];
			}else{
				$items[] = $item;
			}
			if($subItems){
				self::getListFromTree($subItems, $subKey, $items, $onlyId);
			}
		}
	}

	static function getNearestVal($val, $values){
		if(!empty($values)){
			asort($values);
			$lastDiff = NULL;
			$lastVal = NULL;
			foreach($values as $sv){
				$diff = abs($val - $sv);
				if($lastDiff === NULL || $lastDiff > $diff){
					$lastDiff = $diff;
					$lastVal = $sv;
				}
			}
			return $lastVal;
		}
		return NULL;
	}

	static function extendObject($obj, $config){
		foreach($config as $key => $value){
			if(property_exists($obj, $key)){
				$obj->{$key} = $value;
			}
		}
	}

	/**
	 * Finds nearest value in numeric array.
	 * Don't use it in loop.
	 *
	 * @param array $array
	 * @param int $value
	 * @param int $method ARRAY_NEAREST_DEFAULT|ARRAY_NEAREST_LOWER|ARRAY_NEAREST_HIGHER
	 * @return int
	*/
	public static function numeric_nearest($array, $value, $method = self::NEAREST_DEFAULT) {
		sort($array);
		return self::numeric_sorted_nearest($array, $value, $method);
	}

	/**
	 * Finds nearest value in numeric array. Can be used in loops.
	 * Array needs to be non-assocative and sorted.
	 *
	 * @param array $array
	 * @param int $value
	 * @param int $method ARRAY_NEAREST_DEFAULT|ARRAY_NEAREST_LOWER|ARRAY_NEAREST_HIGHER
	 * @return int
	 */
	public static function numeric_sorted_nearest($array, $value, $method = self::NEAREST_DEFAULT) {
		$count = count($array);

		if($count == 0) {
			return null;
		}

		$div_step               = 2;
		$index                  = floor($count / $div_step);
		$best_index             = null;
		$best_score             = null;
		$direction              = null;
		$indexes_checked        = [];

		while(true) {

			if(isset($indexes_checked[$index])) {
				break ;
			}

			$curr_key = isset($array[$index])? $array[$index]: null;

			if($curr_key === null) {
				break ;
			}

			$indexes_checked[$index] = true;

			// perfect match, nothing else to do
			if($curr_key == $value) {
				return $curr_key;
			}

			$prev_key = isset($array[$index - 1])? $array[$index - 1] : null;
			$next_key = isset($array[$index + 1])? $array[$index + 1] : null;

			switch($method) {
				default:
				case self::NEAREST_DEFAULT:
					$curr_score = abs($curr_key - $value);

					$prev_score = $prev_key !== null ? abs($prev_key - $value) : null;
					$next_score = $next_key !== null ? abs($next_key - $value) : null;

					if($prev_score === null) {
						$direction = 1;
					}else if ($next_score === null) {
						break;
					}else{
						$direction = $next_score < $prev_score ? 1 : -1;
					}
					break;
				case self::NEAREST_LOWER:
					$curr_score = $curr_key - $value;
					if($curr_score > 0) {
						$curr_score = null;
					}else{
						$curr_score = abs($curr_score);
					}

					if($curr_score === null) {
						$direction = -1;
					}else{
						$direction = 1;
					}
					break;
				case self::NEAREST_HIGHER:
					$curr_score = $curr_key - $value;
					if($curr_score < 0) {
						$curr_score = null;
					}

					if($curr_score === null) {
						$direction = 1;
					}else{
						$direction = -1;
					}
					break;
			}

			if(($curr_score !== null) && ($curr_score < $best_score) || ($best_score === null)) {
				$best_index = $index;
				$best_score = $curr_score;
			}

			$div_step *= 2;
			$index += $direction * ceil($count / $div_step);
		}

		return isset($array[$best_index])? $array[$best_index]: null;
	}

	public static function max($array){
		$max = null;
		$a = null;
		foreach ($array as $a){
			if($a > $max || $max === null){
				$max = $a;
			}
		}
		return $a;
	}
}