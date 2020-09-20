<?php

class ListCtrlClass{
	var $parentKey = 'parentid';
	var $positionKey = 'position';
	var $modelName;

	/**
	 * model class
	 *
	 * @var ModelClass
	 */
	var $model;
	var $operationFields = [];

	/**
	 *
	 * @param String $parentKey klic rodice, takzvane seskupovaci klic
	 * @param String $positionKey klic pro razeni
	 * @param String $modelName nazev modelu
	 * @param String $primaryKey nepovinny primarni klic
	 * @return ListCtrlClass
	 */
	function __construct($parentKey, $positionKey, $modelObj, $primaryKey = null){
		$this->parentKey = $parentKey;
		$this->positionKey = preg_replace('~ +(ASC|DESC)~','', $positionKey);

		$this->model = clone $modelObj;
		$this->model->removeManyTypeAssociatedModels();

		if($primaryKey !== null){
			$this->model->primaryKey = $primaryKey;
		}
		$this->operationFields = [$this->model->primaryKey, $this->positionKey];
		if($this->parentKey !== null){
			$this->operationFields[] = $this->parentKey;
		}
	}

	function Delete($id = null, $conditions = [], $cascade = false, $bShakeDowm = true){
		if($id !== null && $this->parentKey !== null){
			if($childs = $this->model->FindBy($this->model->name . '.' . $this->parentKey, $id, $conditions, $this->operationFields)){
				$childs = reset($childs);
				$childs = MArray::RetMultiModelArray($childs);
				foreach($childs as $child){
					$this->model->Delete($child[$this->model->name][$this->model->primaryKey], $conditions, $cascade);
				}
			}
		}

		if($org = $this->model->FindBy($this->model->primaryKey, $id, $conditions, $this->operationFields)){
			$org = reset($org);
			$rows = [];
			if($this->parentKey != null){
				$rows[] = $this->parentKey;
			}
			$rows[] = $this->positionKey;
			if($org[$this->model->name][$this->positionKey] !== null && $prev = $this->model->FindAll(
				$this->preventParent(
					array_merge(
						[
							'!' . $this->model->name . '.' . $this->positionKey . ' < ' . $org[$this->model->name][$this->positionKey]
						]
						, $conditions
					)
					, $org
				)
				, $rows
				, [$this->model->name . '.' . $this->positionKey => 'DESC']
				, 1 )){
				$prev = reset($prev);
				$pos = $prev[$this->model->name][$this->positionKey];
			}else{
				$pos = 1;
			}

			if($this->parentKey !== null){/* nesmyslna podminka, mazat by se meli potomci a ne predek */
				/**
				 * TODO : !!! rekurzivne domazat
				 */
			}
			$this->model->Delete($id, $conditions, $cascade);
			if($bShakeDowm){
				$this->ShakeDownPositions($this->getParent($org), $conditions, $pos);
			}
			return true;
		}
	}

	function MoveDown($id, $conditions = []){
		if($org = $this->model->FindOneBy($this->model->primaryKey, $id, $conditions, $this->operationFields)){

			$org = $this->checkNullPosition($org, $conditions);

			if($next = $this->model->FindAll(
				$this->preventParent(
					array_merge(
						[
							'!' . $this->model->name . '.' . $this->positionKey . ' > ' . $org[$this->model->name][$this->positionKey]
						]
						, $conditions
					)
					, $org
				)
				, $this->operationFields
				, [$this->model->name . '.' . $this->positionKey]
				, 1 )){
				$this->switchAndSavePositions(reset($next), $org);
				$this->ShakeDownPositions($this->getParent($org), $conditions, 0);
				return true;
			}
		}
		return false;
	}

	function MoveUp($id, $conditions = []){
		if($org = $this->model->FindOneBy($this->model->primaryKey, $id, $conditions, $this->operationFields)){

			$org = $this->checkNullPosition($org, $conditions);

			if($prev = $this->model->FindAll(
				$this->preventParent(
					array_merge(
						[
							'!' . $this->model->name . '.' . $this->positionKey . ' < ' . $org[$this->model->name][$this->positionKey]
						]
						, $conditions
					)
					, $org
				)
				, $this->operationFields
				, [$this->model->name . '.' . $this->positionKey => 'DESC']
				, 1 )){
				$this->switchAndSavePositions(reset($prev), $org);
				$this->ShakeDownPositions($this->getParent($org), $conditions, 0);
				return true;
			}
		}
		return false;
	}

	function checkNullPosition($item, $conditions){
		if($item[$this->model->name][$this->positionKey] === null){
			$this->ShakeDownPositions($this->getParent($item), $conditions, null);
			if($item = $this->model->FindOneBy($this->model->primaryKey, $item[$this->model->name][$this->model->primaryKey], $conditions, $this->operationFields)){
				return $item;
			}
			throw new OBE_Exception('Selhal pokus o napravení pořadí v ručně řazeném listu');
		}
		return $item;
	}

	function switchAndSavePositions($first, $next){
		$backupOrg = $this->getPosition($next);
		$this->setPosition($next, $this->getPosition($first));
		$this->setPosition($first, $backupOrg);
		$this->model->Save($first, null, 0);
		$this->model->Save($next, null, 0);
	}

	/**
	 * setrese pozici vsech polozek
	 * @param $parentid
	 * @param $conditions
	 * @param $from
	 * @param $start
	 * @param $to
	 */
	function ShakeDownPositions($parentid, $conditions = [], $from = 1, $to = null, $start = null){
		if($this->parentKey !== null){
			$conditions[] = '!' . $this->parentKey . ' = \'' . $parentid . '\'';
		}
		if($from !== null){
			$conditions[] = '!' . $this->positionKey . ' >= ' . $from;
		}
		if($to !== null){
			$conditions[] = '!' . $this->positionKey . ' <= ' . $to;
		}
		$dbso = new DBSimpleObjectClass($this->model->table, $this->model->primaryKey, null, [], $conditions, [$this->positionKey]);
		$dbo = new DBObjectClass($dbso, $this->model);
		if($start === null){
			$start = $from;
		}
		$dbo->UpdateWithParam($this->positionKey, $start, 1);
	}

	function ShakeUpPositions($parentid, $conditions = [], $from = 1, $to = null, $start = null){
		$pureModel = clone $this->model;
		$pureModel->removeAssociateModels();

		if($this->parentKey !== null){
			$conditions[] = '!' . $this->parentKey . ' = ' . $parentid;
		}
		$conditions[] = '!' . $this->positionKey . ' >= ' . $from;
		if($to !== null){
			$conditions[] = '!' . $this->positionKey . ' <= ' . $to;
		}
		$dbso = new DBSimpleObjectClass($this->model->table, $this->model->primaryKey, null, [], $conditions, [$this->positionKey/* . ' DESC'*/]);
		$dbo = new DBObjectClass($dbso);
		if($start === null){
			$start = $from;
		}
		$dbo->UpdateWithParam($this->positionKey, $start, 1/*, '-'*/);
	}

	/**
	 * vrati nejblizssi volnou hodnotu pozice v listu
	 *
	 * @param Integer $parent - hodnota pro sloupec parent
	 * @param Array $conditions - podminky
	 * @return Integer
	 */
	function GetLastOrderValue($parent = null, $conditions = []){
		$pureModel = clone $this->model;
		$pureModel->removeAssociateModels();

		$field = 'max(' . $pureModel->name . '.' . $this->positionKey . ')';
		if($this->parentKey !== null){
			$conditions[$pureModel->name . '.' . $this->parentKey] = $parent;
		}
		$maxsortval = $pureModel->FindAll($conditions, [$field]);
		$maxsortval = reset($maxsortval);
		if($maxsortval[$pureModel->name][$field] > 0){
			return ($maxsortval[$pureModel->name][$field] + 1);
		}
		return 1;
	}

	function setNewPositionToItem($item, $parent = null, $conditions = []){
		$newPos = $this->GetLastOrderValue($parent, $conditions);
		$this->setPosition($item, $newPos);
		return $item;
	}

	function modParent($parent){
		if(!is_numeric($parent) && $parent !== null){
		}elseif($parent === 'null'){
			$parent = 'null';
		}
		return $parent;
	}

	function preventParent($array, $item){
		if($this->parentKey !== null){
			list($modelName, $pCol) = ModelHelper::getModelAndCol($this->parentKey, $this->model);
			if(strpos($this->parentKey, '.')){
				foreach($array as $key => $cond){
					if(strpos($cond, $pCol) !== false || (strpos($cond, '.') && strpos($cond, $this->parentKey))){
						return $array;
					}
				}
			}
			$array[] = '!' . $modelName . '.' . $pCol . ' = ' . $this->modParent(DBSimpleObjectClass::_prepare_val($item[$modelName][$pCol]));
		}
		return $array;
	}

	function moveToIndex($itemId, $toIndex, $conditions = []){
//		OBE_Log::log('---- move to - s ----');
		if($org = $this->model->FindOneBy($this->model->primaryKey, $itemId, $conditions, $this->operationFields)){
			if($new = $this->model->FindAll($this->preventParent($conditions, $org)
				, $this->operationFields
				, [$this->model->name . '.' . $this->positionKey]
				, [$toIndex, 1])
			){
				$new = reset($new);
				$newPos = $this->getPosition($new);
				$orgPos = $this->getPosition($org);
//				errorClass::Trace('org ' . $orgPos . ' new ' . $newPos);
				if($orgPos < $newPos){
//					errorClass::Trace('shakeUp from ' . ($orgPos + 1) . ' to ' .  $newPos . ' start by ' . $orgPos);
					$this->ShakeUpPositions($this->getParent($org), $conditions, $orgPos + 1, $newPos, $orgPos);
				}else{
//					errorClass::Trace('shakeDown from ' . $newPos . ' start by ' .  ($newPos + 1) . ' to - neni');
					$this->ShakeDownPositions($this->getParent($org), $conditions, $newPos, $orgPos, $newPos + 1);
				}
			}else{
//				errorClass::Trace('umisteni na konec');
				$newPos = $this->GetLastOrderValue($this->getParent($org), $conditions);
			}
			$this->setPosition($org, $newPos);
			$this->model->Save($org, null, 0);
//			OBE_Log::log('---- move to - e:T ----');
			return true;
		}
//		OBE_Log::log('---- move to - e:F ----');
		return false;
	}

	function getPosition($item){
		return $item[$this->model->name][$this->positionKey];
	}

	function setPosition(&$item, $newPos){
		$item[$this->model->name][$this->positionKey] = $newPos;
	}

	function getParent($item){
		if($this->parentKey !== null){
			return $item[$this->model->name][$this->parentKey];
		}
		return null;
	}
}