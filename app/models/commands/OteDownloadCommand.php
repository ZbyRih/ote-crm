<?php

namespace App\Models\Commands;

use App\Extensions\Interfaces\ICommand;
use App\Models\DTO\InfoData;
use App\Models\Enums\InfoEnums;
use App\Models\Orm\Orm;
use App\Models\Repositories\ParametersRepository;
use App\Models\Repositories\SettingsRepository;
use App\Models\Services\InfoService;
use App\Models\Strategies\ExtractImapSettingsStrategy;
use Contributte\EventDispatcher\EventDispatcher;
use malkusch\lock\mutex\FlockMutex;
use App\Models\Strategies\Ote\OteReadEmailsFromMailboxStrategy;

class OteDownloadCommand implements ICommand{

	/** @var Orm */
	private $orm;

	/** @var EventDispatcher */
	private $dispatcher;

	/** @var ParametersRepository */
	private $params;

	/** @var SettingsRepository */
	private $settings;

	/** @var InfoData */
	private $info;

	public function __construct(
		Orm $orm,
		InfoService $info,
		EventDispatcher $dispatcher,
		ParametersRepository $params,
		SettingsRepository $repSettings)
	{
		$this->orm = $orm;
		$this->info = $info;
		$this->params = $params;
		$this->settings = $repSettings;
		$this->dispatcher = $dispatcher;
	}

	/**
	 * @param InfoData $info
	 */
	public function setInfo(
		InfoData $info)
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
		$settings = $extr->get($this->settings, SettingsRepository::BOX_OTE);

		$str = new OteReadEmailsFromMailboxStrategy();
		$str->setInfo($this->info);
		$str->setParams($this->params);
		$str->setSettings($settings);
		$str->read();

	// 		$this->info->add(sprintf('Z emailu načteno %d zpráv', count($parts)), InfoEnums::INFO);

		// 		$platby = [];

		// 		$str = new EmailPlainPartToPlatbaEntityStrategy($this->dphCoef);

		// 		foreach($parts as $par){
// 			$platby[] = $str->get($par);
// 		}

		// 		$platby = array_filter($platby);

		// 		$this->info->add(sprintf('Založeno %d plateb', count($platby)), InfoEnums::INFO);

		// 		$str = new PlatbyZaraditStrategy($this->orm, $this->info, $this->selFaks, $this->selZals);
// 		$platby = $str->zaradit($platby);

		// 		foreach($platby as $p){
// 			$this->orm->persist($p);
// 		}
	}
}