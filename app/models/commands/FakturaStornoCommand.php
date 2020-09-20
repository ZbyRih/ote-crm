<?php


namespace App\Models\Commands;

use App\Models\Events\DBCommitEvent;
use App\Models\Orm\Orm;
use Contributte\EventDispatcher\EventDispatcher;

class FakturaStornoCommand
{

	/** @var Orm */
	private $orm;

	/** @var EventDispatcher */
	private $dispatcher;



	/** @var int */
	private $id;

	public function __construct(
		Orm $orm,
		EventDispatcher $dispatcher
	) {
		$this->orm = $orm;
		$this->dispatcher = $dispatcher;
	}

	/**
	 *
	 * @param number $id
	 */
	public function setId(
		$id
	) {
		$this->id = $id;
	}

	public function execute()
	{
		$fa = $this->orm->faktury->getById($this->id);

		$fa->storno = true;

		$this->orm->persist(($fa));

		$otes = $this->orm->oteGP6Head->findBy(['fakturaId' => $fa->id]);
		foreach ($otes as $o) {
			$o->fakturaId = null;
			$this->orm->persist($o);
		}

		$this->dispatcher->dispatch(DBCommitEvent::NAME);
	}
}
