<?php

namespace App\Models\Strategies;

use App\Models\Repositories\ParametersRepository;
use App\Models\Repositories\BankBoxesParameters;
use App\Models\Services\ImapClientService;

class BankBoxesStrategy{

	/** @var ParametersRepository */
	private $params;

	/** @var ImapClientService */
	private $imap;

	public function __construct()
	{
	}

	public function setParams(
		ParametersRepository $params)
	{
		$this->params = $params;
	}

	/**
	 * @param ImapClientService $imap
	 */
	public function setImap(
		ImapClientService $imap)
	{
		$this->imap = $imap;
	}

	public function get(
		$root,
		$year)
	{
		$boxes = new BankBoxesParameters(
			[
				'others' => $root . '.' . $this->params->bankBoxes->others . '.' . $year,
				'incomes' => $root . '.' . $this->params->bankBoxes->incomes . '.' . $year
			]);

		$folders = collection($this->imap->getFolders())->extract('fullpath')->toArray();

		if(!in_array($boxes->incomes, $folders)){
			$this->imap->createFolder($boxes->incomes);
		}

		if(!in_array($boxes->others, $folders)){
			$this->imap->createFolder($boxes->others);
		}

		return $boxes;
	}
}