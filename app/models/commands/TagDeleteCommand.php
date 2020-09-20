<?php

namespace App\Models\Commands;

use App\Extensions\Interfaces\ICommand;
use App\Models\Orm\Orm;

class TagDeleteCommand implements ICommand{

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
		$tag = $this->orm->tags->getById($this->id);

		foreach($tag->objects as $o){
			$this->orm->tagsToObjects->remove($o);
		}

		$this->orm->tags->remove($tag);
	}
}