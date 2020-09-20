<?php

namespace App\Modules\OteZpravy\Presenters;

use App\Models\Orm\Orm;
use App\Models\Orm\OteMessages\OteMessageEntity;
use App\Components\PreformatView;
use App\Models\Strategies\OteMessageAvaibleFileStrategy;

class ViewPresenter extends BasePresenter{

	/** @var Orm @inject */
	public $orm;

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

		$this->msg = $this->orm->oteMessages->getById($this->id);
	}

	public function actionDefault()
	{
		if(!$this->msg){
			$this->redirect('Default:');
		}
	}

	public function renderDefault()
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