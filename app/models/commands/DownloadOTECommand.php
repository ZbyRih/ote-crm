<?php

namespace App\Models\Commands;

use App\Extensions\Interfaces\ICommand;
use App\Models\DTO\ReaderReportDTO;
use App\Models\Enums\NotifyUserEnums;
use App\Models\Events\DBCommitEvent;
use App\Models\Events\NotifyUserEvent;
use Contributte\EventDispatcher\EventDispatcher;
use malkusch\lock\mutex\FlockMutex;

class DownloadOTECommand implements ICommand{

	/** @var ILegacyInitCommand */
	private $cmdLegacy;

	/** @var EventDispatcher */
	private $dispatcher;

	/** @var ReaderReportDTO */
	private $dto;

	public function __construct(
		ILegacyInitCommand $cmdLegacy,
		EventDispatcher $dispatcher)
	{
		$this->cmdLegacy = $cmdLegacy;
		$this->dispatcher = $dispatcher;
	}

	/**
	 * @param ReaderReportDTO $dto
	 */
	public function setDto(
		ReaderReportDTO $dto)
	{
		$this->dto = $dto;
	}

	public function execute()
	{
		$cmd = $this->cmdLegacy->create();
		$cmd->execute();

		$mutex = new FlockMutex(fopen(WWW_DIR . '/index.php', "r"));

		$mutex->synchronized(
			function ()
			{
				$reader = new \OTEMailBoxReader(\OBE_AppCore::LoadVar(\ModulOteacomsettings::store_key));
				$this->dto->msg = $reader->read($reader->checkUnprocessed());

				if($reader->hasErrors()){
					$this->dto->errors = $reader->getErrors();
				}

				if($reader->hasFails()){
					$this->dto->fails = $reader->getFails();
				}

				$reader->__destruct();

				$e = new NotifyUserEvent();
				$e->type = NotifyUserEnums::TYPE_OTE;
				$e->message = $this->dto->msg;
				$this->dispatcher->dispatch(NotifyUserEvent::NAME, $e);
				$this->dispatcher->dispatch(DBCommitEvent::NAME);
			});
	}
}