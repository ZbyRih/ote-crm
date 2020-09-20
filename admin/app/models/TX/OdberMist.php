<?php

class MOdberMist extends ModelClass{

	public static $DIST = [
		'0' => 'RWE GasNet s.r.o.',
		'1' => 'E.ON Distribuce a.s.'
	];

	var $name = 'OdberMist';

	var $table = 'tx_odber_mist';

	var $primaryKey = 'odber_mist_id';

	var $rows = [
		'odber_mist_id',
		'com',
		'eic',
		'address_id',
		'popis',
		'dist_id',
		'deprecated',
		'owner_id',
		'created_by',
		'createdate'
	];

	var $defaultVals = [
		'createdate' => 'NOW()',
		'deprecated' => 0
	];

	var $associatedModels = [
		'MAddress' => [
			'type' => 'belongsTo',
			'name' => 'Address',
			'foreignKey' => 'address_id',
			'associationForeignKey' => 'address_id'
		],
		'MUser' => [
			'type' => 'belongsTo',
			'foreignKey' => 'owner_id',
			'associationForeignKey' => 'id',
			'deprecateSave' => true,
			'rows' => [
				'id',
				'jmeno'
			]
		]
	];

	public function onSaveBefor(&$modelItem){
		if(!isset($modelItem['OdberMist']['owner_id']) || $modelItem['OdberMist']['owner_id'] == NULL){
			$modelItem['OdberMist']['owner_id'] = AdminUserClass::$userId;
		}

		$OM = new MOdberMist();
		$com = trim($modelItem[$this->name]['com']);
		$eic = trim($modelItem[$this->name]['eic']);
		$messages = NULL;

		if(empty($com) && !empty($eic)){
			$con = [
				'eic' => $eic
			];
		}elseif(!empty($com) && empty($eic)){
			$con = [
				'com' => $com
			];
		}elseif(empty($com) && empty($eic)){
			$new = new ModelSaveException('Duplicita');
			$new->addErrors('Minimálně EIC nebo COM musí být uvedené');
			throw $new;
		}else{
			$con = [
				[
					'eic' => $eic,
					'OR',
					'com' => $com
				]
			];
		}

		if(isset($modelItem[$this->name]['odber_mist_id'])){
			$con = array_merge($con, [
				'!odber_mist_id != ' . $modelItem[$this->name]['odber_mist_id']
			]);
		}

		$res = $OM->FindAll($con);
		if($res){

			foreach($res as $r){
				$depricated = '';
				if($r[$this->name]['deprecated']){
					$depricated = ' a je deaktivováno';
				}
				if($eic == $r[$this->name]['eic'] && !empty($eic)){
					$messages[] = 'Odběrné místo s tímto \'' . $eic . '\' EIC již existuje' . $depricated;
					$eic = NULL;
				}
				if($com == $r[$this->name]['com'] && !empty($com)){
					$messages[] = 'Odběrné místo s tímto \'' . $com . '\' číslem již existuje' . $depricated;
					$com = NULL;
				}
				if(!$com && !$eic){
					break;
				}
			}

			if($messages){
				$new = new ModelSaveException('Duplicita');
				$new->addErrors($messages);
				throw $new;
			}
		}
		$modelItem[$this->name]['com'] = $com;
		$modelItem[$this->name]['eic'] = $eic;
	}

	public function onDelete($id, $conditions, $cascade){
		$SmlOM = new MSmlOM();
		if($SmlOM->Count([
			$SmlOM->primaryKey
		], [
			'odber_mist_id'
		])){
			return false;
		}
		return true;
	}

	public static function identity($c, $com = true){
		return $c['OdberMist']['eic'] . (($com) ? (', ' . $c['OdberMist']['com']) : '') . ' - ' . MAddress::addr($c);
	}
}