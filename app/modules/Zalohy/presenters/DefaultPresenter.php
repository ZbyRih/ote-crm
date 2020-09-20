<?php


namespace App\Modules\Zalohy\Presenters;

use App\Components\YearControl;
use App\Extensions\Components\NavTabs;
use App\Models\Selections\ZalohaSelection;
use App\Models\Selections\ZalohaExportSelection;
use App\Modules\Zalohy\IZalohyGrid;
use App\Modules\Zalohy\IZalohyGridDataSource;
use App\Modules\Zalohy\Grids\ZalohyGridDataSource;
use Ublaboo\Responses\CSVResponse;

class DefaultPresenter extends BasePresenter
{


	/** @var ZalohaSelection @inject */
	public $selZalohy;

	/** @var ZalohaExportSelection @inject */
	public $selZalohyExport;

	/** @var IZalohyGrid @inject */
	public $comGrid;

	/** @var IZalohyGridDataSource @inject */
	public $comGridDataSource;

	/** @var string @persistent */
	public $year;

	/** @var string @persistent */
	public $view;

	/**
	 *
	 * {@inheritdoc}
	 * @see \App\Presenters\BasePresenter::startup()
	 */
	protected function startup()
	{
		parent::startup();

		if (!$this->year) {
			$years = $this->selZalohy->getYears();
			$this->year = end($years);
		}

		$this->view = (!$this->view) ? 'all' : $this->view;
	}

	public function createComponentYear()
	{
		$yc = new YearControl();

		$yc->setItems($this->selZalohy->getYears());
		$yc->setCurrent($this->year);

		$yc->onChange[] = function (
			$year
		) {
			$this->year = $year;
			$this->redirect('this');
		};

		return $yc;
	}

	public function createComponentGrid()
	{
		$src = $this->comGridDataSource->create();
		$src->setYear($this->year);
		$src->setView($this->view);
		$src->setUserId(!$this->user->isAllowed('Klients', 'view_all') ? $this->user->id : null);

		$g = $this->comGrid->create();
		$g->setDataSource($src);

		$g->onOdberatel[] = function (
			$id
		) {
			$sm = $this->orm->smlOm->getById($id);
			$this->redirectUrl('/admin/index.php?module=contacts&contactsv=edit&contactsr=' . $sm->klientId . '&selTab=edit');
		};

		$g->onZalohy[] = function (
			$id
		) {
			$sm = $this->orm->smlOm->getById($id);
			$this->redirectUrl(
				'/admin/index.php?module=contacts&contactsv=edit&contactsr=' . $sm->klientId . '&czalohyr=' . $sm->odberMistId . '&czalohya=edit&year=' . $this->year . '&selTab=zalohy'
			);
		};

		return $g;
	}

	public function createComponentView()
	{
		$n = new NavTabs($this->dispatcher);
		$n->setItems(
			[
				ZalohyGridDataSource::VIEW_ALL => 'Vše',
				ZalohyGridDataSource::VIEW_UHRAZENE => 'Vše uhrazené',
				ZalohyGridDataSource::VIEW_VPORADKU => 'Splacené',
				ZalohyGridDataSource::VIEW_PO_SPLATNOSTI => 'Po splatnosti'
			]
		);

		$n->setTab($this->view);
		$n->onChange[] = function (
			$tc,
			$tab
		) {
			$this->view = $tab;
			$this->redirect('this');
		};

		return $n;
	}

	public function handleExport()
	{
		$data = $this->selZalohyExport->get($this->year);

		$head = [
			'V.s.',
			'Suma',
			'Počet',
			'Od', 'Do',
			'COM',
			'EIC',
			'Ulice',
			'Č.p.',
			'č.o.',
			'Město',
			'PSČ',
			'Č.b.',
			'OM. ID.',
			'Suma plateb',
		];

		array_unshift($data, $head);


		$r = new CSVResponse($data);
		$this->sendResponse($r);
	}
}
