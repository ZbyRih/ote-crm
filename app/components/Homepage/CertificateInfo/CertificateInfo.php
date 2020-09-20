<?php

namespace App\Components\Homepage;

use App\Extensions\Components\BaseComponent;
use App\Models\Repositories\SettingsRepository;
use Carbon\Carbon;

class CertificateInfo extends BaseComponent{

	/** @var SettingsRepository */
	private $settings;

	public function __construct(
		SettingsRepository $settings)
	{
		$this->settings = $settings;
	}

	public function render()
	{
		$state = 'success';
		$validTo = 'unknown';

		if($this->settings->ote_cert_valid){
			$validTo = Carbon::parse($this->settings->ote_cert_valid);
			$validToStr = $validTo->format('d.m. Y');
			$dif = Carbon::now()->diffInDays($validTo, false);

			$state = ($dif < 31) ? 'info' : $state;
			$state = ($dif < 14) ? 'warning' : $state;
			$state = ($dif < 7) ? 'danger' : $state;
		}

		$this->template->setParameters([
			'oteCertValidState' => $state,
			'oteCertValidTo' => $validToStr
		]);

		parent::render();
	}
}