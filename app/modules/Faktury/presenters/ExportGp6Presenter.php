<?php

namespace App\Modules\Faktury\Presenters;

use App\Models\Commands\IOteGP6YearExportCommand;

class ExportGp6Presenter extends BasePresenter{

	/** @var IOteGP6YearExportCommand @inject */
	public $facZip;

	/** @var string @persistent */
	public $year;

	public function actionDefault()
	{
		$cmd = $this->facZip->create();
		$cmd->setYear($this->year);
		$cmd->execute();
	}
}