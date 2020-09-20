<?php

namespace App\Models\Strategies\Doklad;

use App\Models\Orm\Platby\PlatbaEntity;
use App\Models\Orm\Doklad\DokladEntity;

class NezarazenaPlatbaException extends \Exception{
}

class CreateDokladStrategy{

	/** @var \Closure */
	private $cislo;

	/**
	 * @param \Closure $cislo
	 */
	public function setCislo(
		$cislo)
	{
		$this->cislo = $cislo;
	}

	/**
	 * @param PlatbaEntity $platba
	 * @throws NezarazenaPlatbaException
	 * @return PlatbaEntity
	 */
	public function create(
		PlatbaEntity $platba)
	{
		if(!$platba->hasZarazeni()){
			throw new NezarazenaPlatbaException('Platba musí být zařazena, jinak pro ní nelze vystavit doklad.');
		}

		// 		if($platba->hasDoklad()){
// 			return $platba->doklad;
// 		}

		$d = new DokladEntity();
		$d->cislo = call_user_func($this->cislo);
		$d->created = new \DateTime();
		$d->platba = $platba->platba;
		$d->denZdanPln = $platba->when;
		$d->platbaId = $platba;

		return $d;
	}
}