<?php

class MEntity extends ModelClass{

	const HANDLER = 'MEntity';
	const DATA_NAME = 'Entity';

	var $name = 'Entity';
	var $alias = 'MEntity';
	var $table = 'obe_entitys';
	var $primaryKey = 'entityid';
	var $rows = ['entityid', 'moduleid', 'creatorid', 'langid', 'createddate'];
	var $defaultVals = ['createddate' => 'NOW()'];

	public static function preSet(&$modelItem, $moduleId){
		if(!isset($modelItem[self::DATA_NAME]['moduleid']) || $modelItem[self::DATA_NAME]['moduleid'] == NULL){
			$modelItem[self::DATA_NAME]['moduleid'] = $moduleId;
			$modelItem[self::DATA_NAME]['creatorid'] = AdminUserClass::$userId;
			$modelItem[self::DATA_NAME]['langid'] = OBE_Language::$id;
		}
	}
}