<?php

namespace App\Modules\Platby\Presenters;

use App\Components\ITiskDanDoklad;
use App\Components\PreformatView;
use App\Extensions\Components\Tisk\TiskPdfResponse;
use App\Extensions\ITiskComponent;
use App\Extensions\Utils\Html;
use App\Models\Commands\IDokladCreateCommand;
use App\Models\Orm\Platby\PlatbaEntity;
use App\Models\Events\DBCommitEvent;
use App\Models\Strategies\Doklad\NezarazenaPlatbaException;
use App\Models\Strategies\Doklad\CreateDokladEntityStrategy;
use App\Models\Strategies\Doklad\CreateDokladStrategy;
use App\Models\Repositories\SettingsRepository;
use App\Models\Events\ResponseSendEvent;

class DokladPresenter extends BasePresenter{

	/** @var SettingsRepository @inject */
	public $repSettings;

	/** @var ITiskDanDoklad @inject */
	public $facTiskDoklad;

	/** @var ITiskComponent @inject */
	public $facTiskComponent;

	/** @var IDokladCreateCommand @inject */
	public $cmdCreateDoklad;

	/** @var int @persistent */
	public $id;

	/** @var PlatbaEntity */
	private $platba;

	/**
	 * {@inheritdoc}
	 * @see \App\Presenters\BasePresenter::startup()
	 */
	protected function startup()
	{
		parent::startup();

		if(!$this->id){
			$this->flashWarning('platba nenalezena.');
			$this->redirect('Default:');
		}

		if(!$this->platba = $this->orm->platby->getById($this->id)){
			$this->flashWarning('platba nenalezena.');
			$this->redirect('Default:');
		}
	}

	public function actionNahled(
		$id)
	{
		$str = new CreateDokladStrategy();
		$str->setCislo(function ()
		{
			return $this->platba->hasDoklad() ? $this->platba->doklad->cislo : ' - náhled - ';
		});

		try{
			if($this->platba->hasDoklad()){
				return;
			}

			$doklad = $str->create($this->platba);
			$this->platba->doklad = $doklad;
		}catch(NezarazenaPlatbaException $e){
			$this->flashDanger($e->getMessage());
			$this->redirect('Default:');
		}
	}

	public function createComponentNahled()
	{
		$str = new CreateDokladEntityStrategy();
		$str->setOrm($this->orm);
		$doklad = $str->create($this->platba);
		$title = $doklad->cislo;

		$page = $this->facTiskDoklad->create();
		$page->setDoklad($doklad);

		$tisk = $this->facTiskComponent->create();
		$tisk->setTitle($title);
		$tisk->addPage($page);
		// tlacitko zpet nad náhled mimo iframe :)

		$tisk->addButtons(
			Html::el('a')->class('btn btn-primary')
				->addHtml(Html::el('i')->class('md md-file-download'))
				->addText('Stáhnout')
				->href($this->link('download', $this->platba->id)));

		$com = new PreformatView();
		$com->setTitle($title);
		$com->setType(PreformatView::TYPE_HTML);
		$com->setContent((string) $tisk);
		return $com;
	}

	public function actionDownload(
		$id)
	{
		$cmd = $this->cmdCreateDoklad->create();
		$cmd->setPlatba($this->platba);

		$exit = false;
		try{
			$cmd->execute();
			$this->dispatcher->dispatch(DBCommitEvent::NAME);
		}catch(NezarazenaPlatbaException $e){
			$this->flashDanger($e->getMessage());
			$exit = true;
		}

		if($exit){
			$this->redirect('Default:');
		}

		$str = new CreateDokladEntityStrategy();
		$str->setOrm($this->orm);
		$doklad = $str->create($this->platba);

		$page = $this->facTiskDoklad->create();
		$page->setDoklad($doklad);

		$tisk = $this->facTiskComponent->create();
		$tisk->addPage($page);
		$tisk->setTitle($doklad->getFileName());

		$ev = new ResponseSendEvent();
		$ev->response = new TiskPdfResponse($tisk);

		$this->dispatcher->dispatch(ResponseSendEvent::NAME, $ev);
	}
}