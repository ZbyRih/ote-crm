<?php


class MSpotreba extends ModelClass{

	public $name = 'Spotreba';

	public $table = 'tx_spotreba';

	public $primaryKey = 'id';

	public $rows = [
		'id',
		'odber_mist_id',
		'od',
		'do',
		'mwh'
	];
}