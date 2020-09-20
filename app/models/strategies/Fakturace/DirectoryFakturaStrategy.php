<?php

namespace App\Models\Strategies\Fakturace;

use App\Models\Repositories\ParametersRepository;
use Nette\Utils\FileSystem;

class DirectoryFakturaStrategy{

	/** @var ParametersRepository  */
	private $params;

	public function __construct(
		ParametersRepository $params)
	{
		$this->params = $params;
	}

	public function get(
		FileInfoFaktura $fif)
	{
		$year = $fif->from->format('Y');

		$dir = OLD_DATA_DIR . '/' . $this->params->fakturyDir;

		FileSystem::createDir($dir);
		FileSystem::createDir($dir . '/' . $year);

		return $dir . '/' . $year . '/';
	}
}