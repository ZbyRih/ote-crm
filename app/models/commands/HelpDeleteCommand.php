<?php

namespace App\Models\Commands;

use App\Extensions\Interfaces\ICommand;
use App\Models\Orm\Orm;
use App\Models\Orm\Helps\HelpEntity;

class HelpDeleteCommand implements ICommand{

	/** @var Orm */
	private $orm;

	/** @var HelpEntity */
	private $help;

	public function __construct(
		Orm $orm)
	{
		$this->orm = $orm;
	}

	/**
	 *
	 * @param \App\Models\Orm\Helps\HelpEntity $help
	 */
	public function setHelp(
		$help)
	{
		$this->help = $help;
	}

	public function execute()
	{
		$this->orm->helps->remove($this->help);
	}
}