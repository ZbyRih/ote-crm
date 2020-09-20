<?php


class MHelper extends ModelClass{

	var $name = 'Helper';

	var $table = 'obe_help';

	var $primaryKey = 'help_id';

	var $rows = [
		'help_id',
		'module_id',
		'desc'
	];

	var $dyn = [
		'name'
	];

	public function getContextById($id){
		$helpObj = new MHelper();
		if($row = $this->FindOneBy('module_id', $id)){
			return $row[$this->name]['desc'];
		}
		return NULL;
	}

	public function modData($data){
		if(is_array($data) && !empty($data)){
			$k = key($data);
			if(is_numeric($k)){
				foreach($data as &$d){
					$id = $d['Helper']['module_id'];
					if(isset(AdminApp::$modulesName2Id[$id])){
						$d['Helper']['name'] = AdminApp::$modulesName2Id[$id];
					}else{
						$d['Helper']['name'] = 'undefined';
					}
				}
			}else{
				$id = $data['Helper']['module_id'];
				if(isset(AdminApp::$modulesName2Id[$id])){
					$data['Helper']['name'] = AdminApp::$modulesName2Id[$id];
				}else{
					$data['Helper']['name'] = 'undefined';
				}
			}
		}
		return $data;
	}
}