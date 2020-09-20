<?php

namespace App\Modules\Faktury\Presenters;

use App\Modules\Faktury\IFakturyEditUserForm;
use App\Modules\Faktury\IFakturyEditGeneratedForm;
use App\Modules\Faktury\IFakturyOteGrid;
use App\Modules\Faktury\IFakturyOteGridDataSource;
use App\Modules\Faktury\IFakturyPlatbyGrid;
use App\Modules\Faktury\IFakturyPlatbyGridDataSource;
use App\Models\Orm\Faktury\FakturaEntity;

class EditPresenter extends BasePresenter{

	/** @var IFakturyEditGeneratedForm @inject */
	public $comGeneratedForm;

	/** @var IFakturyEditUserForm @inject */
	public $comUserForm;

	/** @var IFakturyOteGrid @inject */
	public $comOteGrid;

	/** @var IFakturyOteGridDataSource @inject */
	public $comOteGridDataSource;

	/** @var IFakturyPlatbyGrid @inject */
	public $comPlatbyGrid;

	/** @var IFakturyPlatbyGridDataSource @inject */
	public $comPlatbyGridDataSource;

	/** @var FakturaEntity */
	private $fa;

	/** @var int @persistent */
	public $id;

	/**
	 *
	 * {@inheritdoc}
	 * @see \App\Presenters\BasePresenter::startup()
	 */
	protected function startup()
	{
		parent::startup();

		$this->fa = $this->orm->faktury->getById($this->id);
	}

	public function renderDefault()
	{
		$this->template->cislo = $this->fa ? $this->fa->cis : null;
	}

	public function createComponentForm()
	{
		if($this->fa && !$this->fa->man){
			$com = $this->comGeneratedForm->create();
		}else{
			$com = $this->comUserForm->create();
		}

		$com->setUserId($this->user->id);
		$com->setFa($this->fa);

		$com->onSave[] = function (
			FakturaEntity $fa){
			$this->orm->flush();
			$this->flashSuccess('Faktura č. ' . $fa->cis . ' uložena.');
			$this->redirect('Default:');
		};

		$com->onCancel[] = function (){
			$this->redirect('Default:');
		};

		return $com;
	}

	public function createComponentOte()
	{
		$src = $this->comOteGridDataSource->create();
		$src->setFakturaId($this->fa ? $this->fa->id : null);

		$g = $this->comOteGrid->create();
		$g->setDataSource($src);

		return $g;
	}

	public function createComponentPlatby()
	{
		$src = $this->comPlatbyGridDataSource->create();
		$src->setFakturaId($this->fa ? $this->fa->id : null);

		$g = $this->comPlatbyGrid->create();
		$g->setDataSource($src);

		return $g;
	}
}