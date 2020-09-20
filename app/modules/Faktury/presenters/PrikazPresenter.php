<?php

namespace App\Modules\Faktury\Presenters;

use App\Models\Commands\ICreateABOPrikazCommand;
use App\Models\Events\DBCommitEvent;
use App\Extensions\App\StringStream;
use App\Extensions\App\StringResponse;
use App\Models\Events\ResponseSendEvent;
use App\Models\Strategies\Fakturace\LoadABODTOItemStrategy;
use App\Models\Tables\SmlOmTable;

class PrikazPresenter extends BasePresenter{

	/** @var ICreateABOPrikazCommand @inject */
	public $cmdABOCreate;

	/** @var SmlOmTable @inject */
	public $tblSmlOm;

	/** @var [] */
	private $items = [];

	public function actionDefault(
		$ids = [])
	{
		if(!$ids){
			$this->flashWarning('Nejsou vybrány žádné faktury.');
			return;
		}

		$this->items = $ids;
	}

	public function renderDefault()
	{
		$stg = new LoadABODTOItemStrategy($this->orm, $this->tblSmlOm);
		$items = $stg->get($this->items);

		$this->template->setParameters([
			'items' => $items,
			'suma' => collection($items)->sumOf('fa.preplatek'),
			'ids' => $this->items
		]);
	}

	public function handleRemove(
		$ids = [],
		$id)
	{
		if(!$ids){
			return;
		}

		if(($key = array_search($id, $ids)) !== false){
			unset($ids[$key]);
		}

		$this->redirect('default', [
			'ids' => $ids
		]);
	}

	public function handleCreate(
		$ids = [])
	{
		if(!$ids){
			$this->flashWarning('Nejsou vybrány žádné faktury.');
			$this->redirect('default');
		}

		$stg = new LoadABODTOItemStrategy($this->orm, $this->tblSmlOm);
		$items = $stg->get($this->items);

		$cmd = $this->cmdABOCreate->create();
		$cmd->setItems($items);
		$cmd->setStream($str = new StringStream());
		$cmd->execute();

		if($str->isEmpty()){
			$this->flashInfo('Z vybraných faktur nebyly vygenerovány žádné příkazy.');
			$this->redirect('default');
		}

		$this->dispatcher->dispatch(DBCommitEvent::NAME);

		$ev = new ResponseSendEvent();
		$ev->response = new StringResponse($str->dump(), 'prikazy_' . date('Y-m-d') . '.abo', 'application/octet-stream');

		$this->dispatcher->dispatch(...$ev->disp());
	}
}