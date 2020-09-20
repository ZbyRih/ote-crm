<?php

class categoryImportConfig extends importConfigClass{
	var $tables = ['es_categorys'];

	var $rows = [
		'es_categorys' => ['id' => 0, 'categoryname' => 1, 'pcodemask' => 2, 'parentid' => 3, 'position' => 4]
	];

	var $newItemsDBDef = [
		'es_categorys' => [0 => 'id', 'categoryname' => '', 'pcodemask' => '']
	];

	var $tablesIndexRows = [
		'es_categorys' => 'id'
	];

	var $reverseIndexRefs = [
	];
}


class categoryImportClass extends ImportViewElement{

	function __construct(){
		$config = new categoryImportConfig();

		parent::__construct('csv_file', $config);

		$this->setReadLineCallBack([$this, 'readLineCallBack']);
	}

	function readLineCallBack($item, $newIds){

		return $item;
	}
}