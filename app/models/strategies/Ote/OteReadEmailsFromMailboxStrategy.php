<?php

namespace App\Models\Strategies\Ote;

use App\Models\DTO\ImapSettings;
use App\Models\DTO\InfoData;
use App\Models\Enums\InfoEnums;
use App\Models\Repositories\OTEDirsParameters;
use App\Models\Repositories\OTEBoxesParameters;
use App\Models\Repositories\ParametersRepository;
use App\Models\Services\ImapClientService;
use App\Models\Strategies\OTEDirsStrategy;
use Nette\IOException;
use Nette\Utils\ArrayHash;

class OteReadEmailsFromMailboxStrategy{

	/** @var ImapSettings */
	private $settings;

	/** @var ParametersRepository */
	private $params;

	/** @var InfoData */
	private $info;

	/** @var OTEDirsParameters */
	private $dirs;

	/** @var OTEBoxesParameters */
	private $boxes;

	/** @var ImapClientService */
	private $imap;

	/**
	 * @param ImapSettings $settings
	 */
	public function setSettings(
		$settings)
	{
		$this->settings = $settings;
	}

	/**
	 * @param ParametersRepository $params
	 */
	public function setParams(
		ParametersRepository $params)
	{
		$this->params = $params;
	}

	/**
	 * @param InfoData $info
	 */
	public function setInfo(
		InfoData $info)
	{
		$this->info = $info;
	}

	public function read()
	{
		$year = date('Y');

		$str = new OTEDirsStrategy();
		$str->setParams($this->params);
		try{
			$this->dirs = $str->get($year);
		}catch(IOException $e){
			$this->info->add($e->getMessage(), InfoEnums::ERROR);
			return [];
		}

		$this->imap = new ImapClientService();
		$this->imap->connect($this->settings);

		$root = $this->settings->server . $this->settings->folder;

		$this->imap->switchFolder($root);

		$str = new OteBoxesStrategy();
		$str->setImap($this->imap);
		$str->setParams($this->params);

		try{
			$this->boxes = $str->get($root, $year);
		}catch(\Exception $e){
			$this->info->add($e->getMessage(), InfoEnums::ERROR);
			return [];
		}

		$stats = ArrayHash::from([
			'emails' => 0,
			'platby' => 0,
			'ostatni' => 0
		]);

		$str = new EmailProcessStrategy();
		$str->setCerts($certs);

		foreach($this->imap->getMailIds() as $mId){
			$str->process($mId, $year, $this->imap);
		}
	}
}