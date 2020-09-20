<?php

namespace App\Modules\OteGP6\Presenters;

use App\Models\Commands\ILegacyInitCommand;
use App\Models\Commands\IOteGP6DeleteCommnad;
use App\Models\Commands\IOteGP6FakturovatCommand;
use App\Models\Commands\IOteGP6UndeleteCommnad;
use App\Modules\OteGP6\Factories\IOteGP6Grid;
use App\Modules\OteGP6\Factories\IOteGP6GridDataSource;

class DefaultPresenter extends BasePresenter{

	/** @var IOteGP6Grid @inject */
	public $comGrid;

	/** @var IOteGP6GridDataSource @inject */
	public $comGridDataSource;

	/** @var IOteGP6DeleteCommnad @inject */
	public $comDelete;

	/** @var IOteGP6UndeleteCommnad @inject */
	public $comUndelete;

	/** @var IOteGP6FakturovatCommand @inject */
	public $comVyfakturovat;

	/** @var ILegacyInitCommand @inject */
	public $cmdLegacyInit;

	public function createComponentGrid()
	{
		$src = $this->comGridDataSource->create();
		$com = $this->comGrid->create();
		$com->setDataSource($src);

		return $com;
	}

	public function handleVyfakturovat(
		$id)
	{
		if(!$head = $this->orm->oteGP6Head->getById($id)){
			$this->flashWarning('Ote zpráva nenalezena.');
			$this->redirect('Default:');
		}

		$cmd = $this->cmdLegacyInit->create();
		$cmd->execute();

		$v = (new \OTEFaktura())->load($id);

		$this->redirectUrl(
			'/admin/index.php?module=contacts&contactsv=edit&contactsr=' . $v->klientId . 'faktury=faktury&fakturyv=create2fak&selTab=faktury&selOte=' . $id);

		// 		$cmd = $this->comVyfakturovat->create();
		// 		$cmd->setId($id);
		// 		$cmd->execute();

		// 		$this->flashSuccess('Vytvořena faktura.');
		// 		$this->redirect('Default:');
	}

	public function handleDelete(
		$id)
	{
		$cmd = $this->comDelete->create();
		$cmd->setId($id);
		$cmd->execute();

		$this->orm->flush();

		$this->flashSuccess('Zahozeno.');
		$this->redirect('Default:');
	}

	public function handleUndelete(
		$id)
	{
		$cmd = $this->comUndelete->create();
		$cmd->setId($id);
		$cmd->execute();

		$this->orm->flush();

		$this->flashSuccess('Obnoveno.');
		$this->redirect('Default:');
	}
}