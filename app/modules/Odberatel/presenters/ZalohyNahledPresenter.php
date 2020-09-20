<?php

namespace App\Modules\Odberatel\Presenters;

use App\Components\PreformatView;
use App\Extensions\ITiskComponent;
use App\Extensions\Utils\Html;
use App\Components\ITiskRozpisZaloh;
use App\Models\Commands\IDownloadRozpisZalohCommand;
use App\Models\DTO\TiskRospisZalohDTO;
use App\Models\Strategies\ICreateRozpisZalohEntityStrategy;

class ZalohyNahledPresenter extends BasePresenter{

	const TYPE_FS = 'fs';

	const TYPE_OM = 'om';

	/** @var ICreateRozpisZalohEntityStrategy @inject */
	public $facRozpisEntity;

	/** @var ITiskRozpisZaloh @inject */
	public $facRozpis;

	/** @var ITiskComponent @inject */
	public $facTisk;

	/** @var IDownloadRozpisZalohCommand @inject */
	public $facRozpisCommand;

	/** @var TiskRospisZalohDTO */
	private $ps;

	/** @var string */
	private $type;

	public function actionDefault()
	{
	}

	public function actionNahledOM(
		$id,
		$year,
		$klientId)
	{
		$this->type = self::TYPE_OM;

		$this->ps = new TiskRospisZalohDTO([
			'year' => $year,
			'klientId' => $klientId,
			'omId' => $id,
			'fakSkupId' => null
		]);
	}

	public function renderNahledOM()
	{
	}

	public function actionNahledFS(
		$id,
		$year,
		$klientId)
	{
		$this->type = self::TYPE_FS;

		$this->ps = new TiskRospisZalohDTO([
			'year' => $year,
			'klientId' => $klientId,
			'omId' => null,
			'fakSkupId' => $id
		]);
	}

	public function renderNahledFS()
	{
	}

	public function createComponentNahled()
	{
		$str = $this->facRozpisEntity->create();
		$str->setParams($this->ps);
		$rozpis = $str->create();

		$page = $this->facRozpis->create();
		$page->setZalohy($rozpis);

		$title = $rozpis->odberatel->faIdent . ' - ' . $this->ps->year . ' - ' . ($rozpis->faSkupCis ?: $rozpis->odberMist->com);

		$tisk = $this->facTisk->create();
		$tisk->setTitle($title);
		$tisk->addPage($page);

		$tisk->addButtons(
			Html::el('a')->class('btn btn-primary')
				->addHtml(Html::el('i')->class('md md-file-download'))
				->addText('Stáhnout')
				->href(
				$this->link('download' . strtoupper($this->type),
					[
						'id' => $this->type == self::TYPE_OM ? $this->ps->omId : $this->ps->fakSkupId,
						'year' => $this->ps->year,
						'klientId' => $this->ps->klientId
					])));

		$com = new PreformatView();
		$com->setTitle($title);
		$com->setType(PreformatView::TYPE_HTML);
		$com->setContent((string) $tisk);
		return $com;
	}

	public function actionDownloadOM(
		$id,
		$year,
		$klientId)

	{
		$params = new TiskRospisZalohDTO([
			'year' => $year,
			'klientId' => $klientId,
			'omId' => $id,
			'fakSkupId' => null
		]);

		$cmd = $this->facRozpisCommand->create();
		$cmd->setParams($params);
		$cmd->execute();
	}

	public function actionDownloadFS(
		$id,
		$year,
		$klientId)
	{
		$params = new TiskRospisZalohDTO([
			'year' => $year,
			'klientId' => $klientId,
			'omId' => null,
			'fakSkupId' => $id
		]);

		$cmd = $this->facRozpisCommand->create();
		$cmd->setParams($params);
		$cmd->execute();
	}
}