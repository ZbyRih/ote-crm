<?php
class MEntityTag extends ModelClass{
	var $name = 'EntityTag';
	var $table = 'obe_entity_tags';
	var $primaryKey = 'entitytagid';
	var $rows = ['entitytagid', 'entitytagname'];

	public function getName($item){
		if(isset($item[$this->name])){
			return $item[$this->name]['entitytagname'];
		}
	}
}

class MEntityTagRel extends ModelClass{
	var $name = 'EntityTagRel';
	var $table = 'obe_entity_tags_2_entity';
	var $primaryKey = 'id';
	var $rows = ['id', 'entityid', 'entitytagid'];
	var $associatedModels = [];
}

class MEntityRelTags extends MEntityTagRel{
	var $associatedModels = [
		'MEntityTag' => [
			  'type' => 'belongsTo'
			, 'foreignKey' => 'entitytagid'
			, 'order' => ['entitytagname']
		]
	];
}