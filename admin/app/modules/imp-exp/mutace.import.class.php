<?php
class mutaceImportConfig extends importConfigClass{
	var $tables = ['obe_entitys', 'obe_mutations'];
	var $rows = [
		  'obe_entitys' => ['entityid' => 3]
		, 'obe_mutations' => ['keyname' => 2, 'name' => 1, 'mutationid' => 3, 'groupid' => 4, 'access' => 5, 'visible' => 6]
		, 'fraze' => ['fraze' => 0]
	];

	var $newItemsDBDef = [
		  'obe_entitys' => [0 => 'entityid', 'moduleid' => NULL, 'langid' => '0', 'createddate' => 'NOW()', 'creatorid' => NULL]
		, 'obe_mutations' => [0 => 'mutationid']
	];

	var $tablesIndexRows = [
		  'obe_entitys' => 'entityid'
		, 'obe_mutations' => 'mutationid'
	];

	function __construct($bSuperUser, $moduleId, $defaultGroup){
		$this->newItemsDBDef['obe_entitys']['moduleid'] = $moduleId;
		$this->newItemsDBDef['obe_entitys']['creatorid'] = AdminUserClass::$userId;
		$this->newItemsDBDef['obe_entitys']['langid'] = OBE_Language::$id;
		if(!$bSuperUser){
			$this->newItemsDBDef['obe_mutations']['access'] = 0;
			$this->newItemsDBDef['obe_mutations']['visible'] = 1;

			unset($this->rows['obe_mutations']['access']);
			unset($this->rows['obe_mutations']['visible']);
		}
		$this->newItemsDBDef['obe_mutations']['groupid'] = $defaultGroup;
	}
}

class mutaceImportClass extends ImportViewElement{
	var $fraze = [];
	var $mutace;

	function __construct($fraze, $bSuperUser, $moduleId, $defaultGroup){
		$this->fraze = $fraze;

		$config = new mutaceImportConfig($bSuperUser, $moduleId, $defaultGroup);
		parent::__construct('csf_file', $config);

		$this->setReadLineCallBack([$this, 'readLineCallBack']);
		$this->setReadUserValidCallBack([$this, 'readUserValidCallBack']);

		$mutaceObj = new MMutations();
		$mutace = $mutaceObj->FindAll();
		$this->mutace = MArray::MapModelItemToKey($mutace, 'Mutations', 'keyname');
	}

	function readLineCallBack($item, $newIds, $line){

		if(isset($item['fraze']['fraze'])){
			$this->fraze[$item['obe_mutations']['keyname']] = $item['fraze']['fraze'];
		}

		return $item;
	}

	function readUserValidCallBack(&$item){
		if(isset($this->mutace[$item['obe_mutations']['keyname']])){

			if(isset($item['fraze']['fraze'])){
				$this->fraze[$item['obe_mutations']['keyname']] = $item['fraze']['fraze'];
			}

			$item['obe_mutations']['mutationid'] = $this->mutace[$item['obe_mutations']['keyname']]['Mutations']['mutationid'];
			$item['obe_entitys']['entityid'] = $this->mutace[$item['obe_mutations']['keyname']]['Mutations']['mutationid'];

		}else if(isset($item['obe_mutations']['mutationid'])){
			$item['obe_mutations']['mutationid'] = NULL;
			$item['obe_entitys']['entityid'] = NULL;
		}
		return true;
	}
}