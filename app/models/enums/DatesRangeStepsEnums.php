<?php

namespace App\Models\Enums;

class DatesRangeStepsEnums{

	const STEP_DEFAULT = null;

	const STEP_YEAR = 1;

	const STEP_HALF = 2;

	const STEP_QUATER = 3;

	public static $STEPS = [
		self::STEP_DEFAULT => 1,
		self::STEP_YEAR => 12,
		self::STEP_HALF => 6,
		self::STEP_QUATER => 3
	];
}