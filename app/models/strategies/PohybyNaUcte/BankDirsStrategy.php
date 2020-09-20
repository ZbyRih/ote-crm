<?php

namespace App\Models\Strategies;

use App\Models\Repositories\ParametersRepository;
use App\Models\Repositories\BankDirsParameters;
use Nette\Utils\FileSystem;

class BankDirsStrategy{

	/** @var ParametersRepository */
	private $params;

	public function __construct()
	{
	}

	public function setParams(
		ParametersRepository $params)
	{
		$this->params = $params;
	}

	public function get(
		$year)
	{
		$dirs = new BankDirsParameters(
			[
				'root' => DATA_DIR . $this->params->bankDirs->root,
				'incomes' => DATA_DIR . $this->params->bankDirs->incomes . '/' . $year,
				'others' => DATA_DIR . $this->params->bankDirs->others . '/' . $year
			]);

		FileSystem::createDir($dirs->root);
		FileSystem::createDir($dirs->incomes);
		FileSystem::createDir($dirs->others);

		return $dirs;
	}
}