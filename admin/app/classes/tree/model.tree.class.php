<?php

class ModelTreeClass extends DataTreeClass{

	/**
	 *
	 * @var ModelClass
	 */
	private $model = null;

	private $addSpecRows = [];

	/**
	 *
	 * @var modelNameKeyPair
	 */
	private $nameSrc = null;

	/**
	 *
	 * @param ModelListClass $listObj
	 * @param String $parentKey
	 * @param String $nodename
	 * @return void
	 */
	function __construct(
		$modelObj,
		$parentKey,
		$nameKey,
		$addSpec = null,
		$nodeName = 'sub')
	{
		$this->addSpecRows = MArray::AllwaysArray($addSpec);

		if($this->setModel($modelObj)){
			$this->nameSrc = new modelNameKeyPair($this->model, $nameKey);

			parent::__construct(null, $this->model->primaryKey, $parentKey, $nameKey, $nodeName);
		}
	}

	function setModel(
		$modelObj)
	{
		if($modelObj !== null){
			if(!is_object($modelObj)){
				$this->model = new $modelObj();
			}else{
				$this->model = $modelObj;
			}
			return true;
		}
		return false;
	}

	function makeTree(
		$items = null,
		$modelObj = null)
	{
		if($modelObj !== null){
			$this->setModel($modelObj);
		}
		if($items === null){
			$items = $this->model->FindAll();
		}

		$this->nameSrc = new modelNameKeyPair($this->model, $this->nameKey);

		if(!empty($items)){
			$this->items = $items;
		}

		if(!empty($this->items)){
			$lev1 = reset($this->items);
			if(MArray::isNumericKey($lev1[$this->model->name])){
				$this->items = $this->normalize($modelData);
			}
			$this->rassoc = [];

			foreach($this->items as $index => $item){
				$MD = $item[$this->model->name];
				if(array_key_exists($this->parentKey, $MD)){
					if($MD[$this->parentKey] === null){
						$MD[$this->parentKey] = 'null';
					}
				}else{
					OBE_Trace::dump('nema prent klic', $this->parentKey, $item);
				}
				$this->rassoc[$MD[$this->parentKey]][] = $index;
				$this->parent2id[$MD[$this->parentKey]][] = $item[$this->model->name][$this->model->primaryKey];
			}
			$this->tree = $this->createLeaf('null', 0);
			return $this->tree;
		}
		return null;
	}

	function normalize(
		$oldData)
	{
		$newRes = [];
		foreach($oldData as $modl1){
			foreach($modl1[$this->model->name] as $subitem){
				$newRes[] = [
					$this->model->name => $subitem
				];
			}
		}
		return $newRes;
	}

	function createLeaf(
		$key,
		$depth)
	{
		$data = [];
		if(isset($this->rassoc[$key])){
			foreach($this->rassoc[$key] as $index){
				$item = $this->items[$index];
				$data[$index] = $this->convertItem($item);
				$data[$index]['id'] = $item[$this->model->name][$this->model->primaryKey];
				$data[$index]['name'] = $this->nameSrc->extract($item);
				$data[$index]['level'] = $depth;
				$data[$index][$this->nodeName] = $this->createLeaf($data[$index]['id'], $depth + 1);
				if(empty($data[$index][$this->nodeName])){
					unset($data[$index][$this->nodeName]);
				}
			}
		}
		return $data;
	}

	function convertItem(
		$item)
	{
		if($this->convertItemCallBack != null){
			$ritem = call_user_func($this->convertItemCallBack, $item);
		}else{
			$ritem = [
				'item' => $item
			];
		}

		foreach($this->addSpecRows as $key => $val){
			if(isset($item[$this->model->name][$key])){
				$ritem[$val] = $item[$this->model->name][$key];
			}
		}

		return $ritem;
	}

	/**
	 *
	 * @param String $decore
	 * @return DataListClass
	 */
	function collapseToList(
		$prepand = '-')
	{
		$retList = [];
		$dep = 0;
		if(!empty($this->tree)){
			foreach($this->tree as $key => $item){
				$mitem = $item;
				if(isset($item[$this->nodeName])){
					unset($mitem[$this->nodeName]);
				}
				$retList[] = $mitem;
				if(isset($item[$this->nodeName])){
					$this->_collapseToList($item[$this->nodeName], $dep, $retList, $prepand);
				}
			}
		}
		return new DataListClass($retList);
	}

	function _collapseToList(
		$items,
		$dep,
		&$retList,
		$prepand)
	{
		++$dep;
		foreach($items as $item){
			$item['name'] = str_pad('', $dep, $prepand) . ' ' . $item['name'];
			$mitem = $item;
			if(isset($item[$this->nodeName])){
				unset($mitem[$this->nodeName]);
			}
			$retList[] = $mitem;
			if(isset($item[$this->nodeName])){
				$this->_collapseToList($item[$this->nodeName], $dep, $retList, $prepand);
			}
		}
	}
}