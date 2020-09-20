<?php

class MTemplates extends ModelClass{
	var $name = 'Templates';
	var $table = 'obe_templates';
	var $primaryKey = 'template_id';
	var $rows = ['template_id', 'key', 'subject', 'sub_content'];
	var $associatedModels = [
		  'MEntity' => [
			  'type' => 'belongsTo'
		]
		, 'MDescription' => [
			  'type' => 'hasOne'
		]
	];
}