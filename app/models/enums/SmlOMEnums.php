<?php

namespace App\Models\Enums;

class SmlOMEnums{

	const INTERVAL_MONTH = 0;

	const INTERVAL_YEAR = 1;

	const INTERVAL_HALF = 2;

	const INTERVAL_QUARTER = 3;

	const INTERVAL_FREE = 4;

	public static $INTERVAL_LABELS = [
		self::INTERVAL_MONTH => 'měsíční',
		self::INTERVAL_YEAR => 'roční',
		self::INTERVAL_HALF => '1/2',
		self::INTERVAL_QUARTER => '1/4',
		self::INTERVAL_FREE => 'bez záloh'
	];

	const INFINITY = '9999-12-31 00:00:00';
}