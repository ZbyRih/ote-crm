<?php

namespace Tests\Utils;

use Nette\Application\IPresenter;
use Nette\Application\IPresenterFactory;
use Nette\Application\Request;
use Nette\DI\Container;
use Nette\Http\Response;
use Nette\ComponentModel\Component;
use Nette\SmartObject;

trait TPresenterMock{
	use SmartObject;

	/** @var IPresenterFactory */
	private $presenterFac;

	/** @var Container */
	private $container;

	private $components = [];

	/** @var IPresenter */
	protected $presenter;

	/**  @var [] */
	public $onBeforeRun = [];

	public function init(
		Container $container)
	{
		$this->container = $container;
		$this->presenterFac = $container->getByType(IPresenterFactory::class);
	}

	public function buildMock(
		$resource = 'Mock')
	{
		$this->presenter = $this->presenterFac->createPresenter('Mock');
		$this->presenter->setResource($resource);
		$this->presenter->autoCanonicalize = false;
		$this->presenter->createComponentCallBack = function (
			$name)
		{
			return $this->components[$name];
		};

		$identity = new IdentityBuilder();
		$identity->setAllowObj(true);

		$this->presenter->initUser($identity->create());
	}

	/**
	 * @param Request $request
	 * @return Response
	 */
	public function runPresenter(
		Request $request,
		$userId)
	{
		$presenter = $request->getPresenterName();
		$this->presenter = $this->presenterFac->createPresenter($presenter);
		$this->presenter->autoCanonicalize = false;

		$this->presenter->onStartup[] = function () use (
		$userId)
		{
			$this->setUser($userId);
		};

		$this->onBeforeRun($this->presenter);

		return $this->presenter->run($request);
	}

	private function setUser(
		$userId)
	{
		if($userId instanceof IdentityBuilder){
			$identity = $userId;
		}else{
			$identity = new IdentityBuilder();
			$identity->setUserId($userId);
		}

		$user = $this->container->getService('user');
		$user->login($identity->create());
	}

	public function getResource()
	{
		return $this->presenter->getResource();
	}

	public function addComponent(
		$com,
		$name)
	{
		$this->components[$name] = $com;
	}

	public function setIdentityValue(
		$key,
		$value)
	{
		$i = $this->presenter->user->getIdentity();
		$i->$key = $value;
	}

	public function setPrivilege(
		$resource,
		$privilege,
		$allow = true)
	{
		$auth = $this->user->getAuthorizator();

		if($allow){
			$auth->allow($this->user->getRoles(), $resource, $privilege);
		}else{
			$auth->deny($this->user->getRoles(), $resource, $privilege);
		}
	}

	public function catchComponentRender(
		Component $com)
	{
		ob_start();

		$name = array_search($com, $this->components);

		$this->presenter[$name]->render();

		return ob_get_clean();
	}

	public function catchTemplateRender(
		$com)
	{
		ob_start();
		$com->render();
		return ob_get_clean();
	}
}