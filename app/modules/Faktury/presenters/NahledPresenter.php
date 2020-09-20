<?php

namespace App\Modules\Faktury\Presenters;

use App\Components\PreformatView;
use App\Extensions\ITiskComponent;
use App\Extensions\Utils\Html;
use App\Models\Orm\Faktury\FakturaEntity;
use App\Modules\Faktury\IFakturyOteGrid;
use App\Modules\Faktury\IFakturyPlatbyGrid;
use App\Modules\Faktury\IFakturyOteGridDataSource;
use App\Modules\Faktury\IFakturyPlatbyGridDataSource;
use App\Extensions\Components\Tisk\TiskHtmlPage;
use App\Models\Strategies\Fakturace\CreateHtmlFakturaStrategy;

class NahledPresenter extends BasePresenter{

	/** @var IFakturyOteGrid @inject */
	public $comOteGrid;

	/** @var IFakturyOteGridDataSource @inject */
	public $comOteGridDataSource;

	/** @var IFakturyPlatbyGrid @inject */
	public $comPlatbyGrid;

	/** @var IFakturyPlatbyGridDataSource @inject */
	public $comPlatbyGridDataSource;

	/** @var ITiskComponent @inject */
	public $facTiskComponent;

	/** @var int @persistent */
	public $id;

	/** @var FakturaEntity */
	private $fa;

	/**
	 * {@inheritdoc}
	 * @see \App\Presenters\BasePresenter::startup()
	 */
	protected function startup()
	{
		parent::startup();

		if(!$this->id){
			$this->redirect('Default:');
		}

		if(!$this->fa = $this->orm->faktury->getById($this->id)){
			$this->flashWarning('Faktura nenalezena.');
			$this->redirect('Default:');
		}
	}

	public function renderDefault()
	{
		$this->template->setParameters([
			'id' => $this->id,
			'cislo' => $this->fa->cis,
			'showPregenerovat' => $this->user->isSuper()
		]);
	}

	public function createComponentView()
	{
		$strHtml = new CreateHtmlFakturaStrategy();
		$page = new TiskHtmlPage();
		$page->setHtml($strHtml->get($this->fa));

		$tisk = $this->facTiskComponent->create();
		$tisk->setTitle($this->fa->cis);
		$tisk->addPage($page);

		$tisk->addButtons(
			Html::el('a')->class('btn btn-primary')
				->addHtml(Html::el('i')->class('md md-file-download'))
				->addText('Stáhnout')
				->href($this->link('Default:download', $this->fa->id)));

		$com = new PreformatView();
		$com->setTitle('Faktura č. ' . $this->fa->cis);
		$com->setType(PreformatView::TYPE_HTML);
		$com->setContent((string) $tisk);
		return $com;
	}

	public function createComponentOte()
	{
		$src = $this->comOteGridDataSource->create();
		$src->setFakturaId($this->fa->id);

		$g = $this->comOteGrid->create();
		$g->setDataSource($src);

		return $g;
	}

	public function createComponentPlatby()
	{
		$src = $this->comPlatbyGridDataSource->create();
		$src->setFakturaId($this->fa->id);

		$g = $this->comPlatbyGrid->create();
		$g->setDataSource($src);

		return $g;
	}
}