<?php

class MComments extends ModelClass{

	var $name = 'Comments';

	var $table = 'tx_comments';

	var $primaryKey = 'comment_id';

	var $rows = [
		'comment_id',
		'klient_id',
		'owner_id',
		'inserted',
		'text'
	];

	var $defaultVals = [
		'inserted' => 'NOW()'
	];

	public function onSaveBefor(&$modelItem){
		if(!isset($modelItem['Comments']['owner_id']) || $modelItem['Comments']['owner_id'] == NULL){
			$modelItem['Comments']['owner_id'] = AdminUserClass::$userId;
		}
	}
}

class MCommentsWUsers extends MComments{

	var $order = [
		'inserted' => 'DESC'
	];

	var $associatedModels = [
		'MUser' => [
			'type' => 'belongsTo',
			'foreignKey' => 'owner_id',
			'associationForeignKey' => 'id'
		]
	];
}