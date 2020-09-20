<?php

namespace App\Extensions\Utils;

class Callback{

	public static function arr(
		$obj,
		$fce)
	{
		return [
			$obj,
			$fce
		];
	}
}