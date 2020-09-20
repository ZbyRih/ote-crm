<?php

namespace App\Extensions\Components;

use App\Presenters\BasePresenter;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Security\User;

/**
 *
 * @property BasePresenter $presenter
 * @property Template $template
 */
class BaseComponent extends Control{
	use TTemplateDefaultResolver;
	use TFlashMessage;

	/** @var User */
	public $user;

	/**
	 *
	 * @param BasePresenter $presenter
	 * {@inheritdoc}
	 * @see \Nette\Application\UI\Component::attached()
	 */
	protected function attached($presenter){
		parent::attached($presenter);
		$this->user = $presenter->getUser();
	}

	/**
	 * Returns the presenter where this component belongs to.
	 * @param bool $throw exception if presenter doesn't exist?
	 * @return BasePresenter|null
	 */
	public function getPresenter($throw = TRUE){
		return $this->lookup(Presenter::class, $throw);
	}

	/**
	 *
	 * @return Template
	 */
	public function createTemplate(){
		return parent::createTemplate();
	}

	/**
	 *
	 * @return BaseForm
	 */
	public function createForm(){
		return $this->presenter->createForm();
	}

	public function render(){
		$t = $this->template;
		if(!$t->getFile()){
			$t->setFile($this->getTemplateFile());
		}
		$t->render();
	}

	/**
	 *
	 * @return string
	 */
	protected function getTemplateFile(){
		return $this->getTemplateDefaultFile();
	}

	/**
	 *
	 * @return boolean
	 */
	public function isAjax(){
		return $this->presenter->isAjax();
	}

	/**
	 *
	 * @return boolean
	 */
	public function isAllowed($priv){
		return $this->presenter->isAllowed($priv);
	}
}