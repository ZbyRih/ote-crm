<?php

namespace App\Models\Enums;

class KlientEnums{

	const KIND_PO = 1;

	const KIND_FO = 0;

	static $LABELS_SHORT = [
		self::KIND_PO => 'PO',
		self::KIND_FO => 'FO'
	];

	static $LABELS_LONG = [
		self::KIND_PO => 'Právnická osoba',
		self::KIND_FO => 'Fyzická osoba'
	];
}