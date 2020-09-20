<?php

namespace App\Models\Commands;

use App\Models\DTO\ReaderReportDTO;
use malkusch\lock\mutex\FlockMutex;
use App\Extensions\Interfaces\ICommand;

class OteUnprocessedCommand implements ICommand{

	/** @var ILegacyInitCommand */
	private $cmdLegacy;

	/** @var ReaderReportDTO */
	private $dto;

	public function __construct(
		ILegacyInitCommand $cmdLegacy)
	{
		$this->cmdLegacy = $cmdLegacy;
	}

	/**
	 *
	 * @param ReaderReportDTO $dto
	 */
	public function setDto(
		$dto)
	{
		$this->dto = $dto;
	}

	public function execute()
	{
		$cmd = $this->cmdLegacy->create();
		$cmd->execute();

		$mutex = new FlockMutex(fopen(WWW_DIR . '/index.php', "r"));

		$mutex->synchronized(
			function (){
				$reader = new \OTEMailBoxReader(\OBE_AppCore::LoadVar(\ModulOteacomsettings::store_key));
				$reader->checkUnprocessed();

				if($reader->hasErrors()){
					$this->dto->errors = $reader->getErrors();
				}

				if($reader->hasFails()){
					$this->dto->fails = $reader->getFails();
				}

				$reader->__destruct();

				$this->dto->msg = 'Zkontrolovány nezprocesované zprávy';
			});
	}
}