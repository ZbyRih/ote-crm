<?php

namespace App\Models\Listeners;

use App\Models\Commands\IChangedCertificateCommand;
use App\Models\Events\SettingsChangedEvent;
use App\Models\Storages\SettingsCacheStorage;
use Contributte\EventDispatcher\EventSubscriber;

class SettingsChangedListener implements EventSubscriber{

	/** @var IChangedCertificateCommand */
	private $cmdChangedCertificate;

	/** @var SettingsCacheStorage */
	private $stoSettings;

	public function __construct(
		SettingsCacheStorage $stoSettings,
		IChangedCertificateCommand $cmdChangedCertificate)
	{
		$this->stoSettings = $stoSettings;
		$this->cmdChangedCertificate = $cmdChangedCertificate;
	}

	public static function getSubscribedEvents()
	{
		return [
			SettingsChangedEvent::NAME => 'onChanged'
		];
		// 		-10 do píče, tu budu potřebovat potom prioritu asi
	}

	public function onChanged()
	{
		$cmd = $this->cmdChangedCertificate->create();
		$cmd->execute();

		$this->stoSettings->invalidate();
	}
}