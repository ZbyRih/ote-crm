<?php

namespace App\Modules\OteZpravy\Presenters;

use App\Extensions\Interfaces\ICommand;
use App\Modules\OteZpravy\Factories\IOteMessageGrid;
use App\Modules\OteZpravy\Factories\IOteMessageGridDataSource;
use App\Models\Commands\IDownloadOteCommand;
use App\Models\Commands\IOteUndecryptedCommand;
use App\Models\Commands\IOteUnprocessedCommand;
use App\Models\DTO\ReaderReportDTO;
use Nette\Http\SessionSection;

class DefaultPresenter extends BasePresenter{

	/** @var IOteMessageGrid @inject */
	public $comGrid;

	/** @var IOteMessageGridDataSource @inject */
	public $facGridDataSource;

	/** @var IDownloadOteCommand @inject */
	public $cmdDownloadOte;

	/** @var IOteUndecryptedCommand @inject */
	public $cmdOteUndecrypted;

	/** @var IOteUnprocessedCommand @inject */
	public $cmdOteUnprocessed;

	/** @var SessionSection */
	private $section;

	/**
	 *
	 * {@inheritdoc}
	 * @see \App\Presenters\BasePresenter::startup()
	 */
	protected function startup()
	{
		parent::startup();
		$this->section = $this->session->getSection('result');
	}

	public function createComponentGrid()
	{
		$src = $this->facGridDataSource->create();
		$com = $this->comGrid->create();
		$com->setDataSource($src);

		return $com;
	}

	public function actionDownload()
	{
		$this->execute($this->cmdDownloadOte->create());
	}

	public function actionUndecrypted()
	{
		$this->execute($this->cmdOteUndecrypted->create());
	}

	public function actionUnproccessed()
	{
		$this->execute($this->cmdOteUnprocessed->create());
	}

	public function actionReport()
	{
		if($this->section->offsetExists('report')){

			$dto = $this->section->report;

			$this->template->msg = $dto->msg;
			$this->template->fails = $dto->fails;
			$this->template->errors = $dto->errors;

			$this->section->offsetUnset('report');
		}else{
			$this->flashInfo('Není co reportovat.');
			$this->redirect('Default:');
		}
	}

	private function execute(
		ICommand $cmd)
	{
		$dto = new ReaderReportDTO();

		$cmd->setDto($dto);
		$cmd->execute();

		$this->section->report = $dto;

		$this->redirect(':report');
	}
}