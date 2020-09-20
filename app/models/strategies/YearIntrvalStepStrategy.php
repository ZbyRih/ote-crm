<?php

namespace App\Models\Strategies;

use App\Models\Enums\DatesRangeStepsEnums;

class YearIntrvalStepStrategy{

	public function get(
		$interval)
	{
		if(!array_key_exists($interval, DatesRangeStepsEnums::$STEPS)){
			return DatesRangeStepsEnums::$STEPS[DatesRangeStepsEnums::STEP_DEFAULT];
		}

		return DatesRangeStepsEnums::$STEPS[$interval];
	}
}