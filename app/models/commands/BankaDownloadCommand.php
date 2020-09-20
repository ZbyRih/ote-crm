<?php

namespace App\Models\Commands;

use App\Extensions\Interfaces\ICommand;
use App\Models\DTO\InfoData;
use App\Models\Enums\InfoEnums;
use App\Models\Orm\Orm;
use App\Models\Repositories\ParametersRepository;
use App\Models\Repositories\SettingsRepository;
use App\Models\Strategies\ExtractImapSettingsStrategy;
use App\Models\Selections\FakturaSelection;
use App\Models\Selections\ZalohaSelection;
use App\Models\Services\InfoService;
use App\Models\Strategies\BankReadEmailsFromMailboxStrategy;
use App\Models\Strategies\EmailPlainPartToPlatbaEntityStrategy;
use App\Models\Strategies\PlatbyZaraditStrategy;
use Contributte\EventDispatcher\EventDispatcher;
use malkusch\lock\mutex\FlockMutex;

class BankaDownloadCommand implements ICommand{

	/** @var Orm */
	private $orm;

	/** @var EventDispatcher */
	private $dispatcher;

	/** @var FakturaSelection */
	private $selFaks;

	/** @var ZalohaSelection */
	private $selZals;

	/** @var ParametersRepository */
	private $params;

	/** @var SettingsRepository */
	private $settings;

	/** @var float */
	private $dphCoef;

	/** @var InfoData */
	private $info;

	public function __construct(
		Orm $orm,
		InfoService $info,
		ZalohaSelection $selZals,
		FakturaSelection $selFaks,
		EventDispatcher $dispatcher,
		ParametersRepository $params,
		SettingsRepository $repSettings)
	{
		$this->orm = $orm;
		$this->info = $info;
		$this->params = $params;
		$this->selFaks = $selFaks;
		$this->selZals = $selZals;
		$this->settings = $repSettings;
		$this->dispatcher = $dispatcher;
		$this->dphCoef = $repSettings->dph_koef;
	}

	/**
	 * @param InfoData $info
	 */
	public function setInfo(
		$info)
	{
		$this->info = $info;
	}

	public function execute()
	{
		$mutex = new FlockMutex($h = fopen(WWW_DIR . '/index.php', "r"));

		$mutex->synchronized(function ()
		{
			$this->read();
		});

		fclose($h);
	}

	private function read()
	{
		$extr = new ExtractImapSettingsStrategy();
		$settings = $extr->get($this->settings, SettingsRepository::BOX_PLATBY);

		$str = new BankReadEmailsFromMailboxStrategy();
		$str->setInfo($this->info);
		$str->setParams($this->params);
		$str->setSettings($settings);
		$parts = $str->read();

		$this->info->addInfo(sprintf('Z emailu načteno %d plateb', count($parts)));

		$platby = [];

		$str = new EmailPlainPartToPlatbaEntityStrategy($this->dphCoef);

		foreach($parts as $par){
			$platby[] = $str->get($par);
		}

		$platby = array_filter($platby);

		$this->info->addInfo(sprintf('Založeno %d plateb', count($platby)));

		$str = new PlatbyZaraditStrategy($this->orm, $this->info, $this->selFaks, $this->selZals);
		$platby = $str->zaradit($platby);

		foreach($platby as $p){
			$this->orm->persist($p);
		}
	}
}