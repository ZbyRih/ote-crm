<?php

namespace App\Modules\Platby\Presenters;

use App\Models\Commands\IBankaDownloadCommand;
use App\Models\Enums\InfoEnums;
use App\Models\Events\DBCommitEvent;
use App\Models\Services\InfoService;

class DownloadPresenter extends BasePresenter{

	/** @var InfoService @inject */
	public $serInfo;

	/** @var IBankaDownloadCommand @inject */
	public $cmdDownloadBanka;

	public function actionDefault()
	{
		$info = $this->serInfo->createObj(InfoEnums::TYPE_BANK);

		$cmd = $this->cmdDownloadBanka->create();
		$cmd->setInfo($info);
		$cmd->execute();

		$e = $this->serInfo->persist($info);

		$this->dispatcher->dispatch(DBCommitEvent::NAME);

		if($e){
			$this->redirect('Report:', $e->id, 'Stažení');
		}else{
			$this->redirect('Default:');
		}
	}

	public function renderDefault()
	{
	}
}