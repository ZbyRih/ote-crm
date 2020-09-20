<?php


class MPVParFZ extends ModelClass{

	var $name = 'PVParFZ';

	var $table = 'tx_pv_par_fz';

	var $primaryKey = 'id';

	var $rows = [
		'id',
		'platba_id',
		'pohyb_id',
		'faktura_id',
		'zaloha_id',
		'suma',
		'dne'
	];

	public function unlink($key, $id){
		if($faks = $this->FindAll([
			$key => $id
		])){
			$faks = collection($faks)->each(function ($v, $k){
				$this->Delete($v['PVParFZ']['id']);
			});
		}
	}

	public function addLink($plaId, $key, $id, $when, $uhr){
		$s = [
			$this->name => [
				'platba_id' => $plaId,
				$key => $id,
				'dne' => $when,
				'suma' => $uhr
			]
		];

		$this->Save($s);
	}
}

class MPVParFZWithAssoc extends MPVParFZ{

	var $associateModels = [
		'MPlatby' => [
			'type' => 'belongsTo',
			'name' => 'Platba',
			'foreignKey' => 'platba_id',
			'associationForeignKey' => 'platba_id'
		],
		'MPohyb' => [
			'type' => 'belongsTo',
			'name' => 'Pohyb',
			'foreignKey' => 'pohyb_id',
			'associationForeignKey' => 'id'
		],
		'MFaktury' => [
			'type' => 'belongsTo',
			'name' => 'Faktura',
			'foreignKey' => 'faktura_id',
			'associationForeignKey' => 'id'
		],
		'MZalohy' => [
			'type' => 'belongsTo',
			'name' => 'Zalohy',
			'foreignKey' => 'zaloha_id',
			'associationForeignKey' => 'zaloha_id',
			'associatedModels' => null
		]
	];
}