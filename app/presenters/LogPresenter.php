<?php

namespace App\Presenters;

class LogPresenter extends \Nette\Application\UI\Presenter{

	public function actionGet(
		$file)
	{
		echo file_get_contents(APP_DIR . '../log/' . $file);
		$this->terminate();
	}
}