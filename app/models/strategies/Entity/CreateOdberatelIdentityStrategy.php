<?php

namespace App\Models\Strategies\Odberatel;

use App\Models\Entities\OdberatelEntity;
use App\Models\Orm\Orm;

class CreateOdberatelIdentityStrategy{

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

	public function create(
		$klientId,
		$faSkupId = null)
	{
		$odb = new OdberatelEntity();

		$klient = $this->orm->klients->getById($klientId);

		$odb->identity = $klient->klientDetailId->getIdentity();
		$odb->faIdent = $klient->klientDetailId->getSalutation();
		$odb->fakturacni = $klient->getFakturacni();

		if($faSkupId){
			$fakSkup = $this->orm->fakSkups->getById($faSkupId);
			$konKlient = $this->orm->klients->getById($fakSkup->faKlientId);
			$odb->kontaktni = $konKlient->getKontaktni();
			$odb->konIdent = $konKlient->klientDetailId->getSalutationKorespondence();
		}

		if(!$odb->kontaktni){
			$odb->kontaktni = $klient->getKontaktni();
		}

		if(!$odb->konIdent){
			$odb->konIdent = $klient->klientDetailId->getSalutationKorespondence();
		}

		return $odb;
	}
}