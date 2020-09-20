<?php

namespace App\Modules\Helper\Presenters;

use App\Models\Commands\IHelpDeleteCommand;
use App\Modules\Helper\Factories\IHelperGrid;
use App\Modules\Helper\Factories\IHelperGridDataSource;
use Nette\Application\Responses\TextResponse;

class DefaultPresenter extends BasePresenter{

	/** @var IHelperGrid @inject */
	public $comGrid;

	/** @var IHelperGridDataSource @inject */
	public $comGridDataSOurce;

	/** @var IHelpDeleteCommand @inject */
	public $cmdDelete;

	public function createComponentGrid()
	{
		$src = $this->comGridDataSOurce->create();

		$g = $this->comGrid->create();

		$g->setDataSource($src);

		return $g;
	}

	public function handleDelete(
		$id)
	{
		if(!$h = $this->orm->helps->getById($id)){
			$this->flashWarning('Nešlo smazat.');
			$this->redirect('Default:');
		}

		$cmd = $this->cmdDelete->create();
		$cmd->setHelp($h);
		$cmd->execute();

		$this->orm->flush();

		$this->flashSuccess('Nápověda smazána.');
		$this->redirect('Default:');
	}

	public function actionHelp(
		$key)
	{
		$e = $this->orm->helps->getBy([
			'resource' => $key
		]);

		$r = new TextResponse($e ? $e->desc : 'Nápověda není vytvořena.');

		$this->sendResponse($r);
	}
}