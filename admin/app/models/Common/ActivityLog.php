<?php

class MActivityLog extends ModelClass{

	var $name = 'ActivityLog';

	var $table = 'log_activity';

	var $primaryKey = 'id';

	var $rows = [
		'id',
		'user_id',
		'kdy',
		'modul',
		'aktivita',
		'master',
		'recid',
		'popis'
	];
}

class MActivityLogWUsers extends MActivityLog{

	var $order = [
		'kdy' => 'DESC'
	];

	var $associatedModels = [
		'MUser' => [
			'type' => 'belongsTo',
			'foreignKey' => 'user_id',
			'associationForeignKey' => 'id'
		]
	];
}