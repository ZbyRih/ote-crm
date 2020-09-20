<?php

class MDescription extends ModelClass{
	var $name = 'Description';
	var $table = 'obe_descriptions';
	var $primaryKey = 'descriptionid';
	var $rows = ['descriptionid', 'name', 'keywords', 'pagedesc', 'description', 'intro'];

	var $bIntroSaved = NULL;

	function _save(&$data){
		if(isset($data['description'])){
			$data['intro'] = OBE_Strings::extractFirstParagraf($data['description'], 'p');
		}
		return parent::_save($data);
	}
}