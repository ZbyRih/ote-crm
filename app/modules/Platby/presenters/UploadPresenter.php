<?php

namespace App\Modules\Platby\Presenters;

use App\Models\Commands\IBankaGPCUploadCommand;
use App\Models\Commands\IBankaTextUploadCommand;
use App\Models\Services\InfoService;
use App\Modules\Platby\Factories\IVypisUploadForm;
use Nette\Http\FileUpload;
use App\Models\Strategies\BankDataImportStrategy;

class UploadPresenter extends BasePresenter{

	/** @var InfoService @inject */
	public $serInfo;

	/** @var IVypisUploadForm @inject */
	public $comUpload;

	/** @var IBankaTextUploadCommand @inject */
	public $cmdImportText;

	/** @var IBankaGPCUploadCommand @inject */
	public $cmdImportGPC;

	public function createComponentUpload()
	{
		$com = $this->comUpload->create();

		$com->onUpload[] = function (
			FileUpload $file,
			$limit)
		{
			$str = new BankDataImportStrategy();
			$name = $str->file($file->sanitizedName);
			$full = $str->full($name);

			$file->move($full);

			$this->redirect('UploadNahled:', $name, $limit);
		};

		return $com;
	}
}