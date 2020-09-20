<?php

class MAttachment extends ModelClass{

	var $name = 'Attachment';
	var $table = 'obe_files';
	var $primaryKey = 'fileid';
	var $rows = ['fileid', 'filename', 'filetype', 'obtype', 'groupid', 'description', 'addinfo'];
	var $associatedModels = [
		  'MEntity' => [
			  'type' => 'belongsTo'
		  ]
	];

	public function onDelete($id, $conditions, $cascade){
		return true;
	}

	public function onSaveBefor(&$modelItem){
		if(!isset($modelItem['Entity']['moduleid']) || $modelItem['Entity']['moduleid'] == NULL){
			$modelItem['Entity']['moduleid'] = MODULES::ATTACHMENT;
			$modelItem['Entity']['creatorid'] = AdminUserClass::$userId;
			$modelItem['Entity']['langid'] = OBE_Language::$id;
		}
	}
}