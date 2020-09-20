<?php

class MDokument extends ModelClass{
	var $name = 'Dokument';
	var $table = 'obe_documents';
	var $primaryKey = 'documentid';
	var $rows = ['documentid', 'title', 'flipping', 'valid_from', 'valid_to_sign', 'valid_to', 'position', 'as_form', 'target_mail'];
	var $defaultVals = ['position' => 0, 'valid_from' => 'NOW()', 'valid_to_sign' => 0, 'flipping' => 0];
	var $associatedModels = [
		'MEntity' => [
			  'type' => 'belongsTo'
		]
		, 'MDescription' => [
			  'type' => 'hasOne'
		]
	];
}