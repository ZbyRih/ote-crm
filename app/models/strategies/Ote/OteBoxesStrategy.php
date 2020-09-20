<?php

namespace App\Models\Strategies\Ote;

use App\Models\Repositories\ParametersRepository;
use App\Models\Services\ImapClientService;
use App\Models\Repositories\OTEBoxesParameters;

class OteBoxesStrategy{

	/** @var ParametersRepository */
	private $params;

	/** @var ImapClientService */
	private $imap;

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
		$boxes = new OTEBoxesParameters(
			[
				'others' => $root . '.' . $this->params->oteBoxes->others . '.' . $year,
				'readed' => $root . '.' . $this->params->oteBoxes->readed . '.' . $year
			]);

		$folders = collection($this->imap->getFolders())->extract('fullpath')->toArray();

		if(!in_array($boxes->readed, $folders)){
			$this->imap->createFolder($boxes->readed);
		}

		if(!in_array($boxes->others, $folders)){
			$this->imap->createFolder($boxes->others);
		}

		return $boxes;
	}
}