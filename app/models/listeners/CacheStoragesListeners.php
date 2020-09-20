<?php

namespace App\Models\Listeners;

use App\Models\Events\CiselnikChangedEvent;
use App\Models\Events\RolesChangedEvent;
use App\Models\Storages\CiselnikyCacheStorage;
use App\Models\Storages\RolesCacheStorage;
use Contributte\EventDispatcher\EventSubscriber;

class CacheStoragesListeners implements EventSubscriber{

	/** @var RolesCacheStorage */
	private $stoRole;

	/** @var CiselnikyCacheStorage */
	private $stoCiselnik;

	public function __construct(
		RolesCacheStorage $stoRole,
		CiselnikyCacheStorage $stoCiselnik)
	{
		$this->stoRole = $stoRole;
		$this->stoCiselnik = $stoCiselnik;
	}

	public static function getSubscribedEvents()
	{
		return [
			RolesChangedEvent::NAME => 'onChangedRole',
			CiselnikChangedEvent::NAME => 'onChangedCiselnik'
		];
	}

	public function onChangedRole()
	{
		$this->stoRole->invalidate();
	}

	public function onChangedCiselnik()
	{
		$this->stoCiselnik->invalidate();
	}
}