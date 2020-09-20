<?php

namespace App\Models\Listeners;

use App\Models\Commands\IStatsLogCommand;
use Contributte\EventDispatcher\EventSubscriber;
use Contributte\Events\Extra\Application\Event\ApplicationEvents;
use Netpromotion\Profiler\Profiler;

class AppListeners implements EventSubscriber{

	/** @var IStatsLogCommand */
	private $cmdStatsLog;

	public function __construct(
		IStatsLogCommand $cmdStatsLog)
	{
		$this->cmdStatsLog = $cmdStatsLog;
	}

	public static function getSubscribedEvents()
	{
		return [
			ApplicationEvents::ON_STARTUP => 'onStartUp',
			ApplicationEvents::ON_SHUTDOWN => 'onShutDown'
		];
	}

	public function onStartUp()
	{
		Profiler::start('app-presenter');
	}

	public function onShutDown()
	{
// 		$cmd = $this->cmdStatsLog->create();
// 		$cmd->execute();
		Profiler::finish('app-presenter');
	}
}