<?php

namespace App\Modules\Platby\Presenters;

use App\Components\Homepage\IInfoReport;
use App\Extensions\Utils\Strings;
use App\Models\Commands\IBankaGPCUploadCommand;
use App\Models\Commands\IBankaTextUploadCommand;
use App\Models\DTO\InfoData;
use App\Models\Enums\InfoEnums;
use App\Models\Strategies\BankDataImportStrategy;

class UploadNahledPresenter extends BasePresenter{

	/** @var IInfoReport @inject */
	public $comInfoReport;

	/** @var IBankaTextUploadCommand @inject */
	public $cmdImportText;

	/** @var IBankaGPCUploadCommand @inject */
	public $cmdImportGPC;

	/** @var InfoData */
	private $info;

	public function actionDefault(
		$file,
		$limit)
	{
		$this->info = new InfoData(InfoEnums::TYPE_BANK);
		$ext = Strings::upper(pathinfo($file, PATHINFO_EXTENSION));

		$str = new BankDataImportStrategy();
		$full = $str->full($file);

		if($ext == 'GPC'){
			$cmd = $this->cmdImportGPC->create();
			$cmd->setFile($full);
		}else{
			$cmd = $this->cmdImportText->create();
			$cmd->setFile(file_get_contents($full));
		}

		$cmd->setInfo($this->info);
		$cmd->setLimit($limit);
		$cmd->execute();
	}

	public function createComponentReport()
	{
		$info = $this->info->getEntity();
		$com = $this->comInfoReport->create();
		$com->setType(InfoEnums::TYPE_BANK);
		$com->setInfo($info);
		return $com;
	}
}