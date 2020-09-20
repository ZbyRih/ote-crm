<?php

namespace App\Models\Strategies\Fakturace;

use App\Extensions\Utils\Helpers\ArrayHash;
use App\Models\Orm\Orm;
use App\Models\Orm\Faktury\FakturaEntity;
use App\Models\Tables\SmlOmTable;

/**
 * @property FakturaEntity $fa
 * @property AccountValue $cu
 *
 */
class DTOABOItem extends ArrayHash{
}

class LoadABODTOItemStrategy{

	/** @var Orm */
	private $orm;

	/** @var SmlOmTable */
	private $tbl;

	/** @var ArrayHash */
	private $kliCu;

	/** @var ArrayHash */
	private $fsCu;

	public function __construct(
		Orm $orm,
		SmlOmTable $tbl)
	{
		$this->orm = $orm;
		$this->tbl = $tbl;

		$this->kliCu = new ArrayHash();
		$this->fsCu = new ArrayHash();
	}

	/**
	 * @param [] $ids
	 * @return DTOABOItem[]
	 */
	public function get(
		$ids)
	{
		$c = collection($this->orm->faktury->findById($ids));

		$fakSkupsSel = new LoadFakskupUcetStrategy($this->orm, $this->tbl);
		$this->fsCu = $fakSkupsSel->get($c);

		$selUcts = new KlientsCisloUctuStrategy($this->orm);
		$kliIds = $c->extract('klientId')->toArray();
		$this->kliCu = $selUcts->get($kliIds);

		return $c->filter(
			function (
				FakturaEntity $v)
			{
				if(!$this->kliCu->offsetExists($v->klientId)){
					return false;
				}
				return $v->preplatek < 0 && !$v->uhrazenoDne;
			})
			->map(
			function (
				FakturaEntity $v)
			{
				$dto = new DTOABOItem();
				$dto->fa = $v;

				$cu = $this->kliCu[$v->klientId];
				if($this->fsCu->offsetExists($v->id)){
					$cu = $this->fsCu[$v->id];
				}

				$dto->cu = $cu;

				return $dto;
			})
			->toArray();
	}
}