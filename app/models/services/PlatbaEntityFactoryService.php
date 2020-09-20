<?php

namespace App\Models\Services;

use App\Models\Repositories\SettingsRepository;
use App\Models\Orm\Platby\PlatbaEntity;

class PlatbaEntityFactoryService{

	/** @var SettingsRepository */
	private $repSettings;

	public function __construct(
		SettingsRepository $repSettings)
	{
		$this->repSettings = $repSettings;
	}

	public function create()
	{
		$p = new PlatbaEntity();
		$p->man = true;
		$p->when = new \DateTime();
		$p->dphCoef = $this->repSettings->dph_koef;

		return $p;
	}
}