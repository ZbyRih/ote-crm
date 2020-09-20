<?php

namespace App\Models\Commands;

use App\Models\Orm\Orm;
use App\Models\Orm\PlatbyParZalohy\PlatbaParZalohaEntity;

class FakturaParPlatbaCommand{

	/** @var Orm */
	private $orm;

	/** @var int */
	private $faktura;

	/** @var int */
	private $platba;

	public function __construct(
		Orm $orm)
	{
		$this->orm = $orm;
	}

	/**
	 *
	 * @param number $faktura
	 */
	public function setFaktura(
		$faktura)
	{
		$this->faktura = $faktura;
	}

	/**
	 *
	 * @param number $platba
	 */
	public function setPlatba(
		$platba)
	{
		$this->platba = $platba;
	}

	public function execute()
	{
		$p = $this->orm->platby->getById($this->platba);
		$f = $this->orm->faktury->getById($this->faktura);

		if(!$p || !$f){
			return;
		}

		$ppz = new PlatbaParZalohaEntity();
		$ppz->platbaId = $this->platba;
		$ppz->fakturaId = $this->faktura;
		$ppz->suma = ($p->platba > $f->preplatek) ? $f->preplatek : $p->platba;
		$ppz->dne = $p->when;

		$this->orm->persistAndFlush($ppz);
	}
}