<?php

class DataTreeClass extends DataListClass{

	var $tree;

	var $parent2id;

	var $rassoc;

	/**
	 *
	 * @var integer
	 */
	protected $idKey = null;

	/**
	 *
	 * @var string
	 */
	protected $parentKey = null;

	/**
	 *
	 * @var string
	 */
	protected $nameKey = null;

	/**
	 *
	 * @var string
	 */
	protected $nodeName = 'sub';

	/**
	 *
	 * @var closure
	 */
	protected $convertItemCallBack = null;

	function __construct(
		$items,
		$idKey,
		$parentKey,
		$nameKey,
		$nodeName = 'sub')
	{
		parent::__construct($items);

		$this->idKey = $idKey;
		$this->parentKey = $parentKey;
		$this->nameKey = $nameKey;
		$this->nodeName = $nodeName;
		if(!empty($this->items)){
			$this->makeTree();
		}
	}

	/**
	 *
	 * @param closure $callBack
	 */
	public function setConvertItemCallBack(
		$callBack)
	{
		if($this->convertItemCallBack != null){
			throw new Exception('DataTreeClass::setConvertItemCallBack proměnná již není nullová');
		}
		$this->convertItemCallBack = $callBack;
	}

	function makeTree(
		$items = null)
	{
		if($this->convertItemCallBack != null && !is_callable($this->convertItemCallBack)){
			throw new Exception('DataTreeClass::$convertItemCallBack není platná funkce');
		}

		if(!empty($items)){
			$this->items = $items;
		}

		foreach($this->items as $index => $item){
			if($item[$this->parentKey] === null){
				$item[$this->parentKey] = 'null';
			}
			$this->rassoc[$item[$this->parentKey]][] = $index;
			$this->parent2id[$item[$this->parentKey]][] = $item[$this->idKey];
		}

		$this->tree = $this->createLeaf('null', 0);
	}

	function createLeaf(
		$key,
		$depth)
	{
		$data = [];
		if(isset($this->rassoc[$key])){
			foreach($this->rassoc[$key] as $index){
				$data[$index] = $this->convertItem($this->items[$index]);
				$data[$index]['id'] = $this->items[$index][$this->idKey];
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
		$ritem = null;
		if($this->convertItemCallBack != null){
			$ritem = call_user_func($this->convertItemCallBack, $item);
		}else{
			$ritem = [
				'id' => $item[$this->idKey],
				'name' => $item[$this->nameKey],
				'item' => $item
			];
		}
		return $ritem;
	}

	function getKeyValPair(
		$bAddNull = false)
	{
		$list = $this->collapseToList();
		if($bAddNull){
			$list->addNullItem('id', 'name');
		}
		return $list->getKeyValPairs('name', 'id');
	}

	/**
	 *
	 * @param String $prepand
	 * @return DataListClass
	 */
	function collapseToList(
		$prepand = '-')
	{
		$retList = [];
		$dep = 0;
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
		return $retList;
	}

	function _collapseToList(
		$subItems,
		$dep,
		&$retList,
		$prepand)
	{
		++$dep;
		foreach($subItems as $item){
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

	function getSubNode(
		$item)
	{
		if(isset($item[$this->nodeName])){
			return $item[$this->nodeName];
		}
		return null;
	}

	function getSubItemsIds(
		$parentId)
	{
		$childs = $this->getSubIds($parentId);
		if(!empty($childs)){
			return $childs;
		}
		return null;
	}

	function getSubIds(
		$ids)
	{
		$childs = [];
		if($ids != null){
			if(!is_array($ids)){
				$ids = [
					$ids
				];
			}
			foreach($ids as $id){
				if(isset($this->parent2id[$id])){
					$nchilds = $this->parent2id[$id];
					$subChildsIds = $this->getSubIds($nchilds);
					$childs = array_merge($childs, $nchilds, $subChildsIds);
				}
			}
		}
		return $childs;
	}
}