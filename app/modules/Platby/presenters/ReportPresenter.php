<?php

namespace App\Modules\Platby\Presenters;

use App\Components\InfoReport;
use App\Models\Enums\InfoEnums;
use App\Models\Orm\Info\InfoEntity;

class ReportPresenter extends BasePresenter{

	/** @var int @persistent */
	public $id;

	/** @var InfoEntity */
	private $info;

	/** @var string */
	private $title;

	public function actionDefault(
		$id,
		$title = '')
	{
		$this->title = $title;
		if(!$this->info = $this->orm->info->getById($id)){
			$this->flashWarning('Report nenalezen.');
			$this->redirect('Default:');
		}
	}

	public function createComponentReport()
	{
		$rep = new InfoReport();

		$date = $this->info->created->format('m.d. Y H:m');
		$name = InfoEnums::$TYPE_LABELS[$this->info->type];

		$rep->setHeader($this->title . ' ' . $name . ' ' . $date);
		$rep->setItems(json_decode($this->info->data));

		return $rep;
	}
}