<?php
namespace App\Modules\Info\Presenters;

use App\Components\Homepage\IInfoReport;
use App\Models\Enums\InfoEnums;
use App\Modules\Info\Factories\IInfoGrid;
use App\Modules\Info\Factories\IInfoGridDataSource;

class DefaultPresenter extends BasePresenter{

	/** @var IInfoGrid @inject */
	public $comGrid;

	/** @var IInfoGridDataSource @inject */
	public $facGridDataSource;

	/** @var IInfoReport @inject */
	public $comInfoReport;

	/** @var int @persistent */
	public $id;

	public function actionDefault()
	{
	}

	public function createComponentGrid()
	{
		$src = $this->facGridDataSource->create();

		$g = $this->comGrid->create();
		$g->setDataSource($src);

		return $g;
	}

	public function actionView(
		$id)
	{
	}

	public function renderView()
	{
	}

	public function createComponentBankaInfo()
	{
		$i = $this->orm->info->getById($this->id);
		$com = $this->comInfoReport->create();
		$com->setType(InfoEnums::TYPE_BANK);
		$com->setInfo($i);
		return $com;
	}
}