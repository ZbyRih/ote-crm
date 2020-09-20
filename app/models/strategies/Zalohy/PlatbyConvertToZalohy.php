<?php

namespace App\Models\Strategies\Zalohy;

use App\Extensions\Utils\Helpers\ArrayHash;
use Cake\Collection\Collection;
use Carbon\Carbon;

/**
 * @property-read \DateTime $od
 * @property-read \DateTime $do
 * @property-read int $odber_mist_id
 * @property-read \DateTime $when
 * @property-read float $vyse
 * @property-read float $dcoef
 */
class PlatbaDoFakturyDTO extends ArrayHash{
}

class PlatbyConvertToZalohy{

	public function __construct()
	{
	}

	/**
	 * @param Collection $zals
	 * @param Collection $plas
	 * @param int $omId
	 * @return \Cake\Collection\Iterator\ReplaceIterator
	 */
	public function convert(
		Collection $zals,
		Collection $plas,
		$omId)
	{
		return $plas->map(
			function (
				$v,
				$k) use (
			$omId,
			$zals)
			{
				$zals = $zals->filter(function (
					$_v,
					$_k) use (
				$v)
				{
					return $_v->od <= $v->when && $_v->do >= $v->when;
				})
					->toArray();

				$od = Carbon::instance($v->when);
				$do = Carbon::instance($v->when);

				if($first = reset($zals)){
					$od = Carbon::instance($first->od);
					$do = Carbon::instance($first->do);
				}else{
					$od->day = 1;
					$do->day = $do->daysInMonth;
				}

				return PlatbaDoFakturyDTO::from(
					[
						'od' => $od,
						'do' => $do,
						'odber_mist_id' => $omId,
						'when' => $v->when,
						'vyse' => $v->platba,
						'dcoef' => $v->dph_coef
					]);
			});
	}
}