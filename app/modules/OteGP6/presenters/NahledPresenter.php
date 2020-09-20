<?php

namespace App\Modules\OteGP6\Presenters;

use App\Components\PreformatView;
use App\Models\Commands\ILegacyInitCommand;

class NahledPresenter extends BasePresenter{

	/** @var ILegacyInitCommand @inject */
	public $cmdLegacyInit;

	/** @var int @persistent */
	public $id;

	/** @var int @persistent */
	private $g;

	/**
	 *
	 * {@inheritdoc}
	 * @see \App\Presenters\BasePresenter::startup()
	 */
	protected function startup()
	{
		parent::startup();
		$this->g = $this->orm->oteGP6Head->getById($this->id);
	}

	public function actionDefault()
	{
		if(!$this->g){
			$this->flashWarning('Ote zpráva nenalezena.');
			$this->redirect('Default:');
		}
	}

	public function renderDefault()
	{
		$this->template->demoPdf = $this->user->isInRole('super');
	}

	public function createComponentView()
	{
		$cmd = $this->cmdLegacyInit->create();
		$cmd->execute();

		if($this->g->fakturaId){
			$v = (new \MFaktury())->getView($this->g->fakturaId);
		}else{
			$v = (new \OTEFaktura())->load($this->id)
				->setCislo('náhled')
				->build()
				->render()
				->getView();
		}

		$com = new PreformatView();
		$com->setTitle($this->g->oteId);
		$com->setType(PreformatView::TYPE_HTML);
		$com->setContent($v->data);
		return $com;
	}

	public function handleDemoPdf()
	{
		if(!$g = $this->orm->oteGP6Head->getById($this->id)){
			$this->flashWarning('Ote zpráva nenalezena.');
			$this->redirect('Default:');
		}

		try{
			$cmd = $this->cmdLegacyInit->create();
			$cmd->execute();

			ob_start();
			(new \OTEFaktura())->load($this->id)
				->setCislo('náhled')
				->build()
				->render()
				->sendPdf(true);
		}catch(\DownloadResponseException $e){
			echo ob_get_clean();

			$this->setLayout(null);
			$this->setView(null);
			$this->terminate();
		}
	}

	public function handleFakturovat()
	{
		if(!$head = $this->orm->oteGP6Head->getById($this->id)){
			$this->flashWarning('Ote zpráva nenalezena.');
			$this->redirect('Default:');
		}

		$cmd = $this->cmdLegacyInit->create();
		$cmd->execute();

		$v = (new \OTEFaktura())->load($this->id);

		$this->redirectUrl(
			'/admin/index.php?module=contacts&contactsv=edit&contactsr=' . $v->klientId . 'faktury=faktury&fakturyv=create2fak&selTab=faktury&selOte=' . $id);
	}
}