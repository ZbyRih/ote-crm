<?php

namespace App\Modules\OteZpravy\Presenters;

use App\Modules\OteZpravy\Factories\IOteMessageUploadForm;

class UploadPresenter extends BasePresenter{

	/** @var IOteMessageUploadForm @inject */
	public $comUploadForm;

	public function createComponentForm()
	{
		$com = $this->comUploadForm->create();

		$com->onError[] = function (
			$msg,
			$type){
			$this->flashMessage($msg, $type);
		};

		$com->onSuccess[] = function (
			$msg){
			$this->orm->flush();
			$this->flashSuccess($msg);
		};

		return $com;
	}
}