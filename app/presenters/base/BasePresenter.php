<?php

namespace App\Presenters;

use App\Components\IComponentMenuFactory;
use App\Components\IComponentNavBarFactory;
use App\Extensions\Components\Breadcrumbs;
use App\Extensions\Utils\Helpers\ClassNames;
use App\Models\Orm\Orm;
use Contributte\EventDispatcher\EventDispatcher;
use Nette\Bridges\ApplicationLatte\Template;

/**
 * @property-read \Nette\Bridges\ApplicationLatte\Template|\stdClass $template
 * @property \App\Models\User\User $user
 */
abstract class BasePresenter extends AppPresenter{
	use TUserLoggedInPresenter;

	/** @var IComponentMenuFactory @inject */
	public $comMenu;

	/** @var IComponentNavBarFactory @inject */
	public $comNavBar;

	/** @var EventDispatcher @inject */
	public $dispatcher;

	/** @var Orm @inject */
	public $orm;

	protected function startup()
	{
		$this->parentStartup();

		$resource = $this->getResource();

		if($resource == 'Error' || $resource == 'Denied'){
			return;
		}

		try{
			$this->userInitiate($resource, $this->context);
		}catch(UnableInitiateUserException $e){
			$this->redirect(':Sign:In:', $this->storeRequest());
		}
	}

	public function beforeRender()
	{
		$t = $this->template;

		$bc = new Breadcrumbs($this->options->title);
		$bodyClasses = new ClassNames([]);

		if($this->isLogged()){
			$menu = $this['menu'];
			$bc->addTitle($menu->getBreadcrumbs());
			$collapsed = $menu->isCollapsed();
			$bodyClasses->add(
				[
					'menubar-hoverable' => true,
					'header-fixed' => true,
					'menubar-pin' => !$collapsed,
					'menubar-visible' => !$collapsed,
					'debug' => !\Tracy\Debugger::$productionMode
				]);
		}

		$t->setParameters([
			'bodyClasses' => $bodyClasses,
			'headTitle' => $bc->getTitle()
		]);
	}

	/**
	 * @return Template
	 */
	public function createTemplate()
	{
		$t = parent::createTemplate();

		$i = $this->isLogged() ? $this->getIdentity() : null;

		$t->setParameters([
			'cardOff' => '',
			'legacy' => false,
			'identity' => $i,
			'theme' => $i ? ($i->row['theme'] ? 'viol' : 'def') : 'def'
		]);

		return $t;
	}

	public function createComponentMenu()
	{
		return $this->comMenu->create();
	}

	public function createComponentNavBar()
	{
		return $this->comNavBar->create();
	}

	protected function shutdown(
		$response)
	{
		$this->parentShutdown($response);
	}

	protected function parentStartup()
	{
		parent::startup();
	}

	protected function parentShutdown(
		$response)
	{
		parent::shutdown($response);
	}
}