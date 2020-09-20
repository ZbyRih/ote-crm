<?php

namespace App\Models\Commands;

use App\Models\Orm\Orm;
use App\Extensions\Abstracts\DatabaseDataNotFoundException;

class OteGP6DeleteCommand{

	/** @var int */
	private $id;

	/** @var Orm */
	private $orm;

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
		if(!$head = $this->orm->oteGP6Head->getById($this->id)){
			throw new DatabaseDataNotFoundException();
		}
		$head->depricated = true;
		$this->orm->persist($head);
	}
}