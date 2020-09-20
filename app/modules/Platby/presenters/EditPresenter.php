<?php

namespace App\Modules\Platby\Presenters;

use App\Modules\Platby\Factories\IPlatbaEdit;
use App\Models\Orm\Platby\PlatbaEntity;
use App\Extensions\Helpers\UuidHelper;
use Ramsey\Uuid\Uuid;
use App\Models\Services\PlatbaEntityFactoryService;
use App\Models\Storages\UuidSessionStorage;
use Nette\Http\Session;
use App\Models\Events\DBCommitEvent;

class EditPresenter extends BasePresenter{

	/** @var IPlatbaEdit @inject */
	public $comEdit;

	/** @var Session @inject */
	public $session;

	/** @var PlatbaEntityFactoryService @inject */
	public $serPlatba;

// 	/** @var string @persistent */
// 	public $year;

	/** @var string @persistent */
	public $id;

	/** @var string @persistent */
	public $uuid;

	/** @var UuidSessionStorage */
	private $store;

	/** @var PlatbaEntity */
	private $platba;

	/**
	 * {@inheritdoc}
	 * @see \App\Presenters\BasePresenter::startup()
	 */
	protected function startup()
	{
		parent::startup();

		$this->store = new UuidSessionStorage($this->session, 'platby-edit');
	}

	public function actionDefault(
		$id = null,
		$uuid = null)
	{
		if(!$id && !$uuid){
			$uuid = UuidHelper::get();
			$this->platba = $this->serPlatba->create();
			$this->platba->uuid = $uuid->toString();
			$this->store->put($uuid, $this->platba);
			$this->redirect('default', null, $uuid->toString());
		}

		if($uuid && Uuid::isValid($uuid)){
			$uuid = Uuid::fromString($uuid);
			if(!$this->platba = $this->store->get($uuid)){
				$this->redirect('default', null, null);
			}
			return;
		}

		if(!$this->platba = $this->orm->platby->getById($id)){
			$this->flashWarning('platba nenalezena.');
			$this->redirect('Default:');
		}
	}

	public function createComponentEdit()
	{
		$com = $this->comEdit->create();
		$com->setPlatba($this->platba);

		$com->onCancel[] = function ()
		{
			$this->redirect('Default:');
		};

		$com->onSave[] = function (
			PlatbaEntity $p)
		{
			$this->orm->persist($p);
			$this->dispatcher->dispatch(DBCommitEvent::NAME);
			$this->flashSuccess('Platba uložena');
			$this->redirect('Default:');
		};

		return $com;
	}
}