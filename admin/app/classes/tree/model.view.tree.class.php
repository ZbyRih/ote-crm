<?php

class ModelViewTreeClass extends ViewElementClass{

	/**
	 * @var ModelListClass
	 */
	var $listObj = null;

	/**
	 * @var ModelTreeClass
	 */
	var $tree = null;

	var $list = [];

	var $model = null;

	public function __construct($type = null){
		parent::__construct('tree');
	}

	/**
	 *
	 * @param array $listConfig
	 * @param String $parentKey
	 * @param String $nameKey
	 * @param array $addSpec
	 * @param String $nodeName
	 * @return void
	 */
	public function init($listConfig, $info, $parentKey, $nameKey, $addSpec = null, $form = 'main', $nodeName = 'sub'){

		$obj = new ModelListClass();
		$obj->modelInit($info, $form);
		$obj->configByArray($listConfig);
		$obj->setConfig('postProccess', false);

		$obj->moveToFirstColumn(new ModelNameKeyPair($obj->model, $nameKey));

		$this->listObj = $obj;
		$this->model = $obj->model;

		$this->tree = new ModelTreeClass($obj->model, $parentKey, $nameKey, $addSpec, $nodeName);
		$this->tree->setConvertItemCallBack([$this, 'convertItem']);

		return $this;
	}

	function convertItem($item){
		$newData = [
			  'spec' => null
			, 'data' => $this->listObj->_getMapedField($item)
		];
		return $this->listObj->_dataProcessCallBack($newData, $item);
	}

	/**
	 * (non-PHPdoc)
	 * @see ViewElementClass::getElementView()
	 */
	public function getElementView(){
		$this->listObj->createList();

		$items = $this->listObj->data['LIST']['data'];

		if(!empty($items)){
			$tree = $this->tree->makeTree($items, $this->listObj->model);
			$this->data = $this->listObj->ArrayForSmarty($tree);
		}

		return $this;
	}

// 	public function createListCols(){
// 		$this->listObj->createListCols();
// 	}

// 	public function getCols(){
// 		return $this->listObj->getCols();
// 	}

// 	public function getOrder(){
// 		return $this->listObj->getOrder();
// 	}
}