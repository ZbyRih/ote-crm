<?php

namespace App\Models\Strategies;

use App\Extensions\Utils\Strings;

class BankPlatbyParseVypisPlainStrategy{

	/**
	 * @param string $cnt
	 * @return string[]
	 */
	public function read(
		$cnt)
	{
		$pars = explode("\r\n\r\n", $cnt);

		$pars = collection($pars)->filter(function (
			$v,
			$k)
		{
			return Strings::beginWith($v, 'datum ');
		})->toList();

		return $pars;
	}
}