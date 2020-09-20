<?php

namespace App\Models\Commands;

class OteGP6FakturovatCommand{

	/** @var int */
	private $id;

	public function __construct()
	{
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
	}
}