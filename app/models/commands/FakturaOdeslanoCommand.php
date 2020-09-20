<?php

namespace App\Models\Commands;

use App\Models\Orm\Orm;
use Nette\InvalidStateException;

class FakturaOdeslanoCommand{

	/** @var Orm */
	private $orm;

	/** @var int */
	private $id;

	public function __construct(
		Orm $orm)
	{
		$this->orm = $orm;
	}

	/**
	 *
	 * @param number $id
	 */
	public function setId(
		$id)
	{
		$this->id = $id;
	}

	public function execute()
	{
		$fa = $this->orm->faktury->getById($this->id);

		if($fa->odeslano){
			throw new InvalidStateException('Faktura ' . $fa->cis . ' byla již odeslána.');
		}

		$fa->odeslano = new \DateTime();

		$this->orm->persistAndFlush($fa);
	}
}