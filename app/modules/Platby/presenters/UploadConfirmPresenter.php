<?php

namespace App\Modules\Platby\Presenters;

use App\Extensions\Utils\Strings;
use App\Models\Commands\IBankaGPCUploadCommand;
use App\Models\Commands\IBankaTextUploadCommand;
use App\Models\Enums\InfoEnums;
use App\Models\Services\InfoService;
use App\Models\Strategies\BankDataImportStrategy;
use App\Models\Events\DBCommitEvent;

class UploadConfirmPresenter extends BasePresenter{

	/** @var InfoService @inject */
	public $serInfo;

	/** @var IBankaTextUploadCommand @inject */
	public $cmdImportText;

	/** @var IBankaGPCUploadCommand @inject */
	public $cmdImportGPC;

	public function actionDefault(
		$file,
		$limit)
	{
		$info = $this->serInfo->createObj(InfoEnums::TYPE_BANK);
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

		$cmd->setInfo($info);
		$cmd->setLimit($limit);
		$cmd->execute();

		$e = $this->serInfo->persist($info);

		$this->dispatcher->dispatch(DBCommitEvent::NAME);

		if($e){
			$this->redirect('Report:', $e->id, 'Nahrání');
		}else{
			$this->redirect('Default:');
		}
	}
}