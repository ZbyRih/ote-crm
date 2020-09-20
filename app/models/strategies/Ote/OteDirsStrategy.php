<?php

namespace App\Models\Strategies;

use App\Models\Repositories\ParametersRepository;
use App\Models\Repositories\OTEDirsParameters;
use Nette\Utils\FileSystem;

class OTEDirsStrategy{

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
		$dirs = new OTEDirsParameters(
			[
				'root' => DATA_DIR . $this->params->oteDirs->root,
				'backup' => DATA_DIR . $this->params->oteDirs->backup . '/' . $year,
				'others' => DATA_DIR . $this->params->oteDirs->others . '/' . $year,
				'undecrypted' => DATA_DIR . $this->params->oteDirs->undecrypted . '/' . $year,
				'xmlMessages' => DATA_DIR . $this->params->oteDirs->xmlMessages . '/' . $year,
				'xmlUnknown' => DATA_DIR . $this->params->oteDirs->xmlUnknown . '/' . $year
			]);

		FileSystem::createDir($dirs->root);
		FileSystem::createDir($dirs->backup);
		FileSystem::createDir($dirs->others);
		FileSystem::createDir($dirs->undecrypted);
		FileSystem::createDir($dirs->xmlMessages);
		FileSystem::createDir($dirs->xmlUnknown);

		return $dirs;
	}
}