<?php
class MEntity2EntityRelSave extends ModelClass {
	var $name = 'MEntity2EntityRelSave';
	var $alias = 'MEntity2EntityRelSave';
	var $table = 'obe_entity2entity';
	var $primaryKey = 'id';
	var $rows = ['id', 'l_entityid', 'r_entityid', 'position', 'description'];
	var $order = ['position'];
	var $associatedModels = [];
}

class MAttachmentRel extends MEntity2EntityRelSave {
	var $associatedModels = [
		  'MAttachment' => [
			  'type' => 'belongsTo'
			, 'foreignKey' => 'r_entityid'
		]
	];
}