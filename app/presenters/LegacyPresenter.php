<?php
namespace App\Presenters;

use App\Models\Commands\ILegacyInitCommand;

class LegacyPresenter extends BasePresenter{

	/** @var ILegacyInitCommand @inject */
	public $cmdLegacyInit;

	protected function startup()
	{
		parent::startup();
	}

	public function actionDefault()
	{
		if($this->isAjax()){
			$this->setLayout(false);
		}

		$this->template->legacy = true;

		$cmd = $this->cmdLegacyInit->create();
		$cmd->execute();

		try{
			\AdminApp::init();
			ob_start();
			\AdminApp::run();
			$html = ob_get_clean();
		}catch(\DownloadResponseException $e){
			echo ob_get_clean();

			$this->setLayout(null);
			$this->setView(null);
			$this->terminate();
		}

		$this->template->subfolder = 'admin/';
		$this->template->html = $html;
	}
}
