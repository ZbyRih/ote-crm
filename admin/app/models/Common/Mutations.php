<?php

class MMutations extends ModelClass{
	var $name = 'Mutations';
	var $alias = 'Mutations';
	var $table = 'obe_mutations';
	var $primaryKey = 'mutationid';
	var $rows = ['mutationid', 'keyname', 'name', 'groupid', 'access', 'visible'];
	var $associatedModels = [
		  'MEntity' => [
			  'type' => 'belongsTo'
		]
	];
}

class MLanguages extends ModelClass{
	var $name = 'Language';
	var $table = 'obe_languages';
	var $primaryKey = 'langid';
	var $rows = ['langid', 'languageshortcut', 'languagename', 'langlabel', 'visible', 'fraze', 'icoimage', 'position', 'currencyid', 'default', 'codes'];

	function onSaveBefor(&$modelItem){
		// fraze jsou pro kazdej jazyk zvlast
	}
}