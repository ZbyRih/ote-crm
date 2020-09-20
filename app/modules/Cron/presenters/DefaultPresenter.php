<?php

namespace App\Modules\Cron\Presenters;

use App\Presenters\AppPresenter;
use App\Models\Commands\IBankaDownloadCommand;
use App\Models\Commands\IDownloadOteCommand;
use Nette\Http\Response;
use App\Models\DTO\ReaderReportDTO;
use App\Models\Enums\InfoEnums;
use App\Models\Events\DBCommitEvent;
use Nette\Application\Responses\TextResponse;
use App\Models\Services\InfoService;
use Contributte\EventDispatcher\EventDispatcher;
use App\Models\Events\DBBeginEvent;

class DefaultPresenter extends AppPresenter{

	/** @var InfoService @inject */
	public $serInfo;

	/** @var IBankaDownloadCommand @inject */
	public $cmdDownloadBanka;

	/** @var IDownloadOteCommand @inject */
	public $cmdDownloadOte;

	/** @var EventDispatcher @inject */
	public $dispatcher;

	public function actionDefault()
	{
		$this->dispatcher->dispatch(DBBeginEvent::NAME);

		$info = $this->serInfo->createObj(InfoEnums::TYPE_BANK);

		$cmd = $this->cmdDownloadBanka->create();
		$cmd->setInfo($info);
		$cmd->execute();

		$this->serInfo->persist($info);

		$this->dispatcher->dispatch(DBCommitEvent::NAME);

		// ote dame na konec

		$this->dispatcher->dispatch(DBBeginEvent::NAME);

		$dto = new ReaderReportDTO();

		$cmd = $this->cmdDownloadOte->create();
		$cmd->setDto($dto);
		$cmd->execute();

		$this->dispatcher->dispatch(DBCommitEvent::NAME);

		$this->getHttpResponse()->setCode(Response::S200_OK);

		$resp = new TextResponse('OK');

		$this->sendResponse($resp);
	}
}