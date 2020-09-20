<?php


// CREATE TABLE `tx_zalohy` (
//   `zaloha_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
//   `odber_mist_id` BIGINT(20) UNSIGNED NOT NULL,
//   `klient_id` BIGINT(20) UNSIGNED NOT NULL,
//   `od` DATE DEFAULT NULL,
//   `do` DATE DEFAULT NULL,
//	 `vyse` FLOAT(10,2) DEFAULT '0.00',
//	 `uhrazeno` FLOAT(10,2) DEFAULT '0.00',
//	 `preplatek` FLOAT(10,2) DEFAULT '0.00',
//	 `uhr` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',    '0' vubec nebo castecne zaplaceno
//	 `vs` TINYTEXT COLLATE utf8_czech_ci,
//   PRIMARY KEY  (`zaloha_id`)
// ) ENGINE=INNODB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci
class MZalohy extends ModelClass
{

	static $INTERVAL = [
		0 => 'měsíční',
		1 => 'roční',
		2 => '1/2',
		3 => '1/4',
		4 => 'bez záloh'
	];

	var $name = 'Zalohy';

	var $table = 'tx_zalohy';

	var $primaryKey = 'zaloha_id';

	var $rows = [
		'zaloha_id',
		'klient_id',
		'odber_mist_id',
		'od',
		'do',
		'vyse',
		'vs'
	];

	var $associatedModels = [
		'MOdberMist' => [
			'type' => 'hasOne',
			'foreignKey' => 'odber_mist_id',
			'deprecateSave' => true
		]
	];

	/**
	 * (non-PHPdoc)
	 * @see ModelItemClass::onSaveBefor()
	 */
	public function onSaveBefor(
		&$modelItem
	) {
		$Zalohy = new MZalohy();
		$Zalohy->removeAssociateModels();

		$conditions = [
			'odber_mist_id' => $modelItem[$this->name]['odber_mist_id'],
			'klient_id' => $modelItem[$this->name]['klient_id'],
			'!(\'' . $modelItem[$this->name]['od'] . '\' BETWEEN od AND do',
			'OR',
			'!\'' . $modelItem[$this->name]['do'] . '\' BETWEEN od AND do)'
		];

		$OdbMist = new MOdberMist();
		$OdbMist->removeAssociateModels();
		$om = $OdbMist->FindOneById($modelItem[$this->name]['odber_mist_id']);

		if (isset($modelItem[$this->name]['zaloha_id'])) {
			$conditions[] = '!zaloha_id != ' . $modelItem[$this->name]['zaloha_id'];

			// if ($z = $Zalohy->FindOneById($modelItem[$this->name]['zaloha_id'])) {
			// if ($z[$this->name]['uhr']) {
			// 	throw new ModelSaveException(
			// 		'Se zálohou ' . $this->formatZalDate($modelItem[$this->name]) . ' pro ' . $om[$OdbMist->name]['com'] . ' nelze pracovat, protože je již uhrazena'
			// 	);
			// }
			// }
		}

		if ($zals = $Zalohy->FindAll($conditions)) {
			throw new ModelSaveException(
				'Záloha pro: ' . $om[$OdbMist->name]['com'] . ' ' . $this->formatZalDate($modelItem[$this->name]) . ' zasahuje do jiné zálohy'
			);
		}

		unset($modelItem[$this->name]['dne']);
		unset($modelItem[$this->name]['uhr']);
		unset($modelItem[$this->name]['uhrazeno']);
	}

	public function onDelete(
		$id = NULL,
		$conditions = NULL,
		$cascade = false
	) {
		$Z = new MZalohy();
		$Z->conditions = $conditions;

		if (!($zalohy = $Z->FindAllById($id))) {
			return false;
		}

		$zalohy = collection($zalohy);
		$ids = $zalohy->extract('Zalohy.id')->toList();

		$PPF = new MPVParFZ();
		if ($count = $PPF->Count('id', [
			'zaloha_id' => $ids
		])) {
			$count = reset($count);

			if ($count['num'] > 0) {
				throw new ModelDeleteException('Některá ze záloh je svázána s platbou nebo fakturou.');
			}

			// uz neplati
			// $wrong = $zalohy->filter(function (
			// 	$v)
			// {
			// 	return $v['Zalohy']['uhr'] || $v['Zalohy']['uhrazeno'] > 0;
			// })->buffered();

			// if($wrong->count() > 0){
			// 	throw new ModelDeleteException('Některá ze záloh je uhrazena.');
			// }
		}

		return true;
	}

	public function formatZalDate(
		$item
	) {
		return '\'' . OBE_DateTime::convertFromDB($item['od']) . '\' - \'' . OBE_DateTime::convertFromDB($item['do']) . '\'';
	}

	public static function validace(
		$zal,
		$interval,
		$klient_id
	) {
		$vs = $zal['vs'];
		$vyse = $zal['vyse'];

		$SMLOm = new MSmlOM();
		if ($smlouva = $SMLOm->FindOne(
			[
				'!\'' . OBE_DateTime::convertToDB($zal['od']) . '\' BETWEEN SmlOM.od AND SmlOM.do',
				'odber_mist_id' => $zal['odber_mist_id'],
				'klient_id' => $klient_id
			]
		)) {
			$smlouva = $smlouva['SmlOM'];
		}

		if (OBE_DateTime::toDateUsr($zal['od']) > OBE_DateTime::toDateUsr($zal['do'])) {
			throw new ModelSaveException('Datum od musí být menší než datum do.');
		}

		if ($interval < 0) {
			if (!$smlouva) {
				throw new ModelSaveException('Pro datum zálohy se od data \'' . $zal['od'] . '\' nenašla platná smlouva, interval ze smlouvy nelze převzít.');
			} else if ($smlouva && !($smlouva['interval'] >= 0)) {
				throw new ModelSaveException('Ve smlouvě platné od \'' . $smlouva['od'] . '\' do \'' . $smlouva['do'] . '\' není uveden interval záloh.');
			} else {
				$zal['interval'] = $smlouva['interval'];
			}
		} else {
			$zal['interval'] = $interval;
		}

		if (empty($vs)) {
			if (!$smlouva) {
				throw new ModelSaveException('Pro datum zálohy se od data \'' . $zal['od'] . '\' nenašla platná smlouva, var. symbol ze smlouvy nelze převzít.');
			} else if ($smlouva && empty($smlouva['vs'])) {
				throw new ModelSaveException('Ve smlouvě platné od \'' . $smlouva['od'] . '\' do \'' . $smlouva['do'] . '\' není uveden var. symbol.');
			} else {
				$zal['vs'] = $smlouva['vs'];
			}
		}

		if (empty($vyse)) {
			if (!$smlouva) {
				throw new ModelSaveException('Výše zálohy musí být uvedena.');
			} else if ($smlouva && empty($smlouva['zaloha'])) {
				throw new ModelSaveException('Ve smlouvě platné od \'' . $smlouva['od'] . '\' do \'' . $smlouva['do'] . '\' není uvedena výše zálohy.');
			} else {
				$zal['vyse'] = $smlouva['zaloha'];
			}
		}

		return $zal;
	}

	public function years(
		$cond = []
	) {
		$years = $this->FindAll($cond, [
			'!YEAR(Min(od)) AS od',
			'!YEAR(MAX(do)) AS do'
		]);
		if ($years) {

			$years = reset($years);

			$min = $years[$this->name]['od'];
			$max = $years[$this->name]['do'];

			$years = [];

			if ($min && $max) {
				for ($y = $min; $y <= $max; $y++) {
					$years[] = $y;
				}
			}
		}

		if (!$years) {
			$years = [
				date('Y')
			];
		}

		return array_combine($years, $years);
	}

	public function aktualizovatVS(
		$om,
		$klient_id,
		$year
	) {
		$SMLOm = new MSmlOM();
		if ($smlouvy = $SMLOm->FindAll([
			'!\'' . $year . '\' BETWEEN YEAR(SmlOM.od) AND YEAR(SmlOM.do)',
			'odber_mist_id' => $om,
			'klient_id' => $klient_id
		])) {
			$vs = NULL;
			foreach ($smlouvy as $s) {
				$vs = $s['OdberMist']['com'];
				if (isset($s['FakSkup']) && $s['FakSkup']['cis']) {
					$vs = $s['FakSkup']['cis'];
				}
			}

			$Zalohy = new MZalohy();
			$zals = $Zalohy->FindAll([
				'odber_mist_id' => $om,
				'klient_id' => $klient_id,
				'!\'' . $year . '\' BETWEEN YEAR(Zalohy.od) AND YEAR(Zalohy.do)'
			]);

			foreach ($zals as &$z) {
				$z['Zalohy']['vs'] = $vs;
			}

			$Zalohy->Save($zals);

			AdminApp::$mainModule->activityLog(
				'Upraveno',
				'Byly zaktualizovány V.S. pro: ' . count($zals) . ' záloh pod OM s číslem' . $s['OdberMist']['com'] . '.',
				null,
				'info'
			);
		}
		return true;
	}

	public function getByRangeAndOmId(
		$omId,
		$dbOd,
		$dbDo
	) {
		$zal = new MZalohy();
		$dbOd = 'DATE(\'' . OBE_DateTime::convertDTToDB($dbOd) . '\')';
		$dbDo = 'DATE(\'' . OBE_DateTime::convertDTToDB($dbDo) . '\')';
		if ($res = $zal->FindAll(
			[
				'odber_mist_id' => $omId,
				'!((od BETWEEN ' . $dbOd . ' AND ' . $dbDo . ')',
				'OR',
				'!(do BETWEEN ' . $dbOd . ' AND ' . $dbDo . '))'
			]
		)) {
			return $res;
		}
		return null;
	}

	public function getUnlink(
		$year,
		$vs
	) {
		$ret = (new static())->removeAssociateModels()->FindAll([
			'YEAR(od)' => [
				$year + 0,
				$year + 1
			],
			'!TRIM(LEADING \'0\' FROM TRIM(vs))' => $vs
		], [], [
			'vs',
			'od'
		]);
		if ($ret) {
			return collection($ret);
		}
		return collection([]);
	}
}

class MOdberMistWZalSum extends MOdberMist
{

	function __construct(
		$bInitialize = true
	) {
		$this->associatedModels['MZalohy'] = [
			'type' => 'hasOne',
			'foreignKey' => 'odber_mist_id',
			'deprecateSave' => true,
			'associatedModels' => []
		];

		$this->associatedModels['MSmlOM'] = [
			'type' => 'belongsTo',
			'foreignKey' => 'odber_mist_id',
			'associationForeignKey' => 'odber_mist_id',
			'deprecateSave' => true,
			'associatedModels' => [
				'MContacts' => [
					'type' => 'belongsTo',
					'foreignKey' => 'klient_id',
					'associationForeignKey' => 'klient_id',
					'deprecateSave' => true,
					'associatedModels' => [
						'MContactDetails' => [
							'type' => 'belongsTo',
							'foreignKey' => 'klient_detail_id',
							'associationForeignKey' => 'klient_detail_id',
							'deprecateSave' => true
						]
					]
				]
			]
		];
		parent::__construct($bInitialize);
	}
}

class MZalohyWOdb extends MZalohy
{

	function __construct(
		$bInitialize = true
	) {
		$om = $this->associatedModels['MOdberMist'];
		unset($this->associatedModels['MOdberMist']);
		$this->associatedModels['MContacts'] = [
			'type' => 'hasOne',
			'foreignKey' => 'klient_id',
			'deprecateSave' => true
		];
		$this->associatedModels['MOdberMist'] = $om;
		parent::__construct($bInitialize);
	}

	/**
	 * @param ModelClass $object
	 */
	public function onCreateAssoc(
		$object
	) {
		if (get_class($object) == 'MContacts') {
			$object->removeAssociateModelsByName('MContactFlags');
		} else if (get_class($object) == 'MOdberMist') {
			$obj = $object->getAssocObj('MAddress');
			$obj->alias = 'OmAddr';
			$obj = $object->getAssocObj('MUser');
			$obj->alias = 'OmUsr';
		}
	}
}
