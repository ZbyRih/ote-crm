<?php

class fieldsImportClass extends importClass{
	var $tables = [
		  'vtiger_categorys'
	];

	var $rows = [
		  'vtiger_categorys' => [
			  'categoryid' => -1
			, 'name' => 0
			, 'parentid' => 1
			, '`order`' => 2
		  ]
	];

	function ReadCallBack($item){
		return $item;
	}
}
