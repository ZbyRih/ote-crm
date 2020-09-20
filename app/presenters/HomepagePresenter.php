<?php

namespace App\Presenters;

use App\Components\Homepage\ICertificateInfo;
use App\Components\Homepage\IInfoReport;
use App\Models\Enums\InfoEnums;

class HomepagePresenter extends BasePresenter{

	/** @var ICertificateInfo @inject */
	public $comCertificateInfo;

	/** @var IInfoReport @inject */
	public $comInfoReport;

	public function createComponentCertificateInfo()
	{
		$com = $this->comCertificateInfo->create();
		return $com;
	}

	public function renderDefault()
	{
		$this->template->setParameters([
			'cardOff' => '-'
		]);
	}

	public function createComponentBankaReport()
	{
		$i = $this->orm->info->getLastByType(InfoEnums::TYPE_BANK);
		$com = $this->comInfoReport->create();
		$com->setType(InfoEnums::TYPE_BANK);
		$com->setInfo($i);
		return $com;
	}

	public function createComponentOteReport()
	{
		$i = $this->orm->info->getLastByType(InfoEnums::TYPE_OTE);
		$com = $this->comInfoReport->create();
		$com->setType(InfoEnums::TYPE_OTE);
		$com->setInfo($i);
		return $com;
	}
}