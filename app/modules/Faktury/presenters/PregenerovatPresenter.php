<?php

namespace App\Modules\Faktury\Presenters;

use App\Models\Commands\IFakturaRecreateCommand;

class PregenerovatPresenter extends BasePresenter{

	/** @var IFakturaRecreateCommand @inject */
	public $cmdPregenerovat;

	public function actionDefault(
		$id)
	{
		$cmd = $this->cmdPregenerovat->create();
		$cmd->setId($id);
		$cmd->setUserId($this->user->id);
		$cmd->execute();
	}
}