<?php


class GP6Head extends ModelClass{

	const SEGMENT = [
		'INV' => 'Pravidelná', // 'Pravidelná fakturace',
		'COR' => 'Opravná', // 'Opravná fakturace (od 1.7.2011 nahrazeno hodnotou atributu corReason)',
		'EXI' => 'Mimořádná', // 'Mimořádná fakturace',
		'CAN' => 'Storno', // 'Storno fakturace',
		'EOC' => 'Ukončení sml.', // 'Ukončení smlouvy',
		'HST' => 'Historické' // 'Historické hodnoty fakturace'
	];

	const COR_REASON = [
		'01' => 'Oprava na základě chyby zjištěné provozovatelem distribuční soustavy',
		'02' => 'Oprava na základě reklamace'
	];

	var $name = 'GP6Head';

	var $primaryKey = 'id';

	var $table = 'tx_ote_invoice_head';

	var $rows = [
		'id',
		'odber_mist_id',
		'faktura_id',
		'depricated',
		'ote_id',
		'pofId',
		'type',
		'version',
		'priceTotal',
		'priceTotalDph',
		'from',
		'to',
		'cancelled',
		'yearReCalculatedValue',
		'attributes_segment',
		'attributes_number',
		'attributes_anumber',
		'attributes_corReason',
		'attributes_complId',
		'attributes_SCNumber',
		'subjects_opm'
	];

	public function getRangeByIds($ids){
		$gp6 = new GP6Head();
		if($res = $gp6->FindOne([
			'id' => (is_array($ids)) ? $ids : 'FALSE'
		], [
			'MIN(from)',
			'MAX(to)',
			'SUM(priceTotalDph)'
		])){
			return [
				'od' => $res[$gp6->name]['MIN(GP6Head.from)'],
				'do' => $res[$gp6->name]['MAX(GP6Head.to)'],
				'sum' => $res[$gp6->name]['SUM(GP6Head.priceTotalDph)']
			];
		}
		return null;
	}
}

class GP6HeadWMailAndOM extends GP6Head{

	var $associatedModels = [
		'MOTEMails' => [
			'type' => 'hasOne',
			'foreignKey' => 'ote_id',
			'deprecateSave' => true
		],
		'MOdberMist' => [
			'type' => 'hasOne',
			'foreignKey' => 'odber_mist_id',
			'deprecateSave' => true
		]
	];
}

class GP6HeadOM extends GP6Head{

	var $associatedModels = [
		'MOdberMist' => [
			'type' => 'hasOne',
			'foreignKey' => 'odber_mist_id',
			'deprecateSave' => true
		],
		'MOTEMails' => [
			'type' => 'hasOne',
			'foreignKey' => 'ote_id',
			'deprecateSave' => true
		]
	];
}

class GP6FullWMailAndOM extends GP6HeadWMailAndOM{

	var $associatedModels = [
		'MOTEMails' => [
			'type' => 'hasOne',
			'foreignKey' => 'ote_id',
			'deprecateSave' => true
		],
		'MOdberMist' => [
			'type' => 'hasOne',
			'foreignKey' => 'odber_mist_id',
			'deprecateSave' => true
		],
		'GP6Body' => [
			'type' => 'belongsTo',
			'associationForeignKey' => 'head_id',
			'deprecateSave' => true
		]
	];
}

class GP6Full extends GP6Head{

	var $associatedModels = [
		'GP6Body' => [
			'type' => 'belongsTo',
			'associationForeignKey' => 'head_id',
			'deprecateSave' => true
		]
	];
}