<?php

class MDBAccessLog extends ModelClass{

	var $name = 'DBAccessLog';

	var $table = 'log_db_access';

	var $primaryKey = 'id';

	var $rows = [
		'id',
		'app',
		'userid',
		'date',
		'action',
		'name1',
		'name2',
		'data'
	];
}

class MDBAccessLogWUsers extends MDBAccessLog{

	var $order = [
		'date' => 'DESC'
	];

	var $associatedModels = [
		'MUser' => [
			'type' => 'belongsTo',
// 			, 'foreignKey' => 'id'
// 			, 'associationForeignKey' => 'userid'
			'foreignKey' => 'userid',
			'associationForeignKey' => 'id'
		]
	];
}