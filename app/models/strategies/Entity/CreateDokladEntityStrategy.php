<?php

namespace App\Models\Strategies\Doklad;

use App\Models\Orm\Orm;
use App\Models\Orm\Platby\PlatbaEntity;
use App\Models\Entities\DokladEntity;
use App\Models\Strategies\Odberatel\CreateOdberatelIdentityStrategy;

class CreateDokladEntityStrategy{

	/** @var Orm */
	private $orm;

	/**
	 * @param Orm $orm
	 */
	public function setOrm(
		$orm)
	{
		$this->orm = $orm;
	}

	/**
	 * @param PlatbaEntity $platba
	 * @return DokladEntity
	 */
	public function create(
		PlatbaEntity $platba)
	{
		$doklad = new DokladEntity();

		$doklad->vs = $platba->vs;
		$doklad->cislo = $platba->doklad->cislo;
		$doklad->vystaveno = $platba->doklad->created;
		$doklad->dphCoef = $platba->dphCoef;

		$doklad->platba->when = $platba->when;
		$doklad->platba->sum = $platba->platba;
		$doklad->platba->cu = $platba->fromCu;
		$doklad->platba->vs = $platba->vs;

		$doklad->fakSkup = (bool) $platba->zarazeni->omId;
		$doklad->odberMist = $platba->zarazeni->omId ? $this->orm->odberMist->getById($platba->zarazeni->omId) : null;

		$strOdb = new CreateOdberatelIdentityStrategy();
		$strOdb->setOrm($this->orm);
		$doklad->odberatel = $strOdb->create($platba->zarazeni->klientId);

		return $doklad;
	}
}