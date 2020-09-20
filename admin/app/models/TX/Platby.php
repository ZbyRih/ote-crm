<?php


// CREATE TABLE `tx_platby` (
// 		`platba_id` BIGINT(20) DEFAULT NULL,
// 		`when` DATETIME DEFAULT NULL,
// 		`from_cu` VARCHAR(40) DEFAULT NULL,
// 		`platba` FLOAT(10,2) DEFAULT '0.00',
// 		`preplatek` FLOAT(10,2) DEFAULT '0.00',
// 		`vs` VARCHAR(20) DEFAULT NULL,
// 		`ks` VARCHAR(20) DEFAULT NULL,
// 		`ss` VARCHAR(20) DEFAULT NULL,
// 		`msg` VARCHAR(255) DEFAULT NULL,
// 		`man` TINYINT(1) DEFAULT '0',
// 		`link` TINYINT(1) DEFAULT '0',
// 		`mail_id` bigint(10) DEFAULT NULL
//		`deprecated` TINYINT(1) DEFAULT '0',
// 		`cislo` VARCHAR(255) DEFAULT NULL
// ) ENGINE=INNODB DEFAULT CHARSET=utf8

class MPlatby extends ModelClass{

	const CIS = 'prijmove_platby';

	var $name = 'Platba';

	var $table = 'tx_platby';

	var $primaryKey = 'platba_id';

	var $rows = [
		'platba_id',
		'mail_id',
		'!isLinkedPlatba(Platba.platba, Platba.platba_id) AS link',
		'!getLinkedPlatba(Platba.platba_id) AS linked',
		'when',
		'from_cu',
		'subject',
		'platba',
		'preplatek',
		'vs',
		'ks',
		'ss',
		'msg',
		'man',
		'edit',
		'deprecated',
		'cislo'
	];

	var $defaultVals = [
		'deprecated' => '0'
	];

	public function onSaveBefor(&$modelItem){
		unset($modelItem['Platba']['link']);
		unset($modelItem['Platba']['linked']);
	}

	public function onDelete($id, $conditions, $cascade){
		$P = new MPlatby();
		$P->conditions = $conditions;

		if(!($pls = $P->FindAllById($id))){
			return false;
		}

		return $this->canDelete($pls);
	}

	public function canDelete($pls){
		$pls = collection($pls);
		$ids = $pls->extract('Platba.id')->toList();
		$ciss = $pls->extract('Platba.cislo')->filter(function ($v){
			return $v;
		})->toList();

		if(!empty($ciss)){
			throw new ModelDeleteException('Plabu s dokladem nelze smazat.');
		}

		$PPF = new MPVParFZ();
		if($count = $PPF->Count('id', [
			'platba_id' => $ids
		])){
			$count = reset($count);

			if($count['num'] > 0){
				throw new ModelDeleteException('Platba je svázána s fakturou nebo zálohou.');
			}
		}

		return true;
	}

	public function getUnlinkVSList($year){
		return array_unique(
			collection(
				(new static())->FindAll(
					[
						'deprecated' => 0,
						'YEAR(when)' => [
							$year + 0,
							$year + 1
						],
						'!TRIM(LEADING \'0\' FROM TRIM(vs)) != ""',
						'!isLinkedPlatba(Platba.platba, Platba.platba_id) IS NOT TRUE'
					], [
						'!TRIM(LEADING \'0\' FROM TRIM(vs)) AS vs'
					], [
						'vs'
					]))->extract('Platba.vs')->toList());
	}

	public function getUnlinkVS($year, $vs){
		$ret = (new static())->FindAll(
			[
				'deprecated' => 0,
				'YEAR(when)' => [
					$year + 0,
					$year + 1
				],
				'!TRIM(LEADING \'0\' FROM TRIM(vs))' => $vs,
				'!isLinkedPlatba(Platba.platba, Platba.platba_id) IS NOT TRUE'
			],
			[
				'platba_id',
				'!TRIM(LEADING \'0\' FROM TRIM(vs)) AS vs',
				'when',
				'from_cu',
				'link',
				'man',
				'!(platba - IFNULL(getLinkedPlatba(Platba.platba_id), 0)) AS platba'
			], [
				'vs',
				'when'
			]);
		if($ret){
			return collection($ret);
		}
		return collection([]);
	}

	public function getYears($cond = []){
		$years = $this->FindAll($cond, [
			'!YEAR(MIN(`when`)) AS y_min',
			'!YEAR(MAX(`when`)) AS y_max'
		]);

		if($years){
			$years = reset($years);

			$min = $years[$this->name]['y_min'];
			$max = $years[$this->name]['y_max'];

			$years = [];

			if($min && $max){
				$years = range($min, $max);
			}

		}

		if(!$years){
			$years = [
				date('Y')
			];
		}

		return array_combine($years, $years);
	}

	public static function getNewCis(){
		return Counters::getNext(self::CIS);
	}

	public static function resetCis(){
		$Pla = new MPlatby();
		$val = null;
		if($p = $Pla->FindOne([
			'!cislo IS NOT NULL'
		], [], [
			'cislo' => 'DESC'
		])){
			$val = $p[$Pla->name]['cislo'];
		}
		Counters::set(self::CIS, $val);
	}
}

class MPlatbyDet extends ModelClass{

	var $associatedModels = [
		'MBMails' => [
			'type' => 'belongsTo',
			'foreignKey' => 'mail_id'
		]
	];
}

class MPlatbaTagRel extends ModelClass{

	var $name = 'PlatbaTagRel';

	var $table = 'tx_platba2tag';

	var $primaryKey = 'id';

	var $rows = [
		'id',
		'platba_id',
		'tag_id'
	];

	var $associatedModels = [];
}

class MPlatbaRelTags extends MPlatbaTagRel{

	var $name = 'PlatbaRelTags';

	var $associatedModels = [
		'MEntityTag' => [
			'type' => 'belongsTo',
			'foreignKey' => 'tag_id',
			'associationForeignKey' => 'entitytagid'
		]
	];
}
