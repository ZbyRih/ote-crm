<?php

namespace App\Modules\OteGP6\Presenters;

use App\Models\Orm\OteMessages\OteMessageEntity;
use App\Models\Strategies\OteMessageAvaibleFileStrategy;
use App\Components\PreformatView;

class XmlPresenter extends BasePresenter{

	/** @var int @persistent */
	public $id;

	/** @var OteMessageEntity */
	private $msg;

	/**
	 *
	 * {@inheritdoc}
	 * @see \App\Presenters\BasePresenter::startup()
	 */
	protected function startup()
	{
		parent::startup();

		if(!$head = $this->orm->oteGP6Head->getById($this->id)){
			$this->flashWarning('Zpráva nenalezena.');
			$this->redirect('Default:');
		}

		if(!$msg = $this->orm->oteMessages->findBy([
			'oteId' => $head->oteId
		])->fetch()){
			$this->flashWarning('Neexistuje email s danou správou.');
			$this->redirect('Default:');
		}

		$this->msg = $msg;
	}

	public function actionDefault()
	{
	}

	public function createComponentView()
	{
		$strg = new OteMessageAvaibleFileStrategy();
		$strg->setOteMsg($this->msg);
		$strg->getFormatedContent();

		$com = new PreformatView();
		$com->setTitle($this->msg->fileXml ? $this->msg->fileXml : $this->msg->fileEml);
		$com->setType(PreformatView::TYPE_PRE);
		$com->setContent($strg->getFormatedContent());
		return $com;
	}
}