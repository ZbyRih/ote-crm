<?php

namespace App\Models\Strategies\Zalohy;

use Cake\Collection\Collection;

class ZalohyMapPlatbyStrategy{

	/**
	 * @param Collection $zals
	 * @param Collection $plas
	 */
	public function map(
		Collection $zals,
		Collection $plas,
		$defaultDphCoef)
	{
		$zals = $zals->toArray();
		$plas = $plas->toArray();

		$z = reset($zals);
		$p = reset($plas);

		if(!$z){
			return [];
		}

		$ret = [];
		$z->uhrazeno = 0;
		$z->when = null;
		$z->dcoef = $defaultDphCoef;

		do{
			if($p){
				if(($z->vyse - $z->uhrazeno) > $p->platba){

					if(!$z->when){
						$z->when = $p->when;
					}

					$z->uhrazeno += $p->platba;
					$p = next($plas);

					continue;
				}

				if(($z->vyse - $z->uhrazeno) < $p->platba){
					$p->platba -= ($z->vyse - $z->uhrazeno);

					$z->uhrazeno = $z->vyse;
					$z->when = $p->when;
					$z->dcoef = $p->dph_coef;
				}

				if(($z->vyse - $z->uhrazeno) == $p->platba){
					$z->uhrazeno = $z->vyse;

					$z->when = $p->when;
					$z->dcoef = $p->dph_coef;

					$p = next($plas);
				}
			}

			$ret[] = $z;

			if($z = next($zals)){
				$z->uhrazeno = 0;
				$z->when = null;
				$z->dcoef = $defaultDphCoef;
			}
		}while($z);

		return $ret;
	}
}