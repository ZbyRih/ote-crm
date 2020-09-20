<?php

namespace App\Presenters;

use Nette\Application\UI\Presenter;
use Nette\Security\IAuthorizator;
use Nette\Security\IIdentity;
use Nette\Utils\Callback;
use Nette\Application\Responses\RedirectResponse;
use App\Extensions\Components\BaseForm;

class MockPresenter extends Presenter{

	/** @var IAuthorizator @inject */
	public $authorizator;

	public $createComponentCallBack;

	private $resource;

	public function initUser(
		IIdentity $identity,
		$authenticated = true)
	{
		$userStorage = $this->getUser()->getStorage();
		$userStorage->setIdentity($identity);
		$userStorage->setAuthenticated($authenticated);

		$roles = $this->getUser()->getRoles();
		$this->authorizator->setRoleRules($roles, $identity->rPerms);
		$this->authorizator->setRoleRules($roles, $identity->uPerms);

		$this->saveGlobalState();
	}

	public function getResource()
	{
		return $this->resource;
	}

	public function setResource(
		$resource)
	{
		$this->resource = $resource;
	}

	public function isAllowed(
		$priviledge)
	{
		return $this->user->isAllowed($this->resource, $priviledge);
	}

	protected function createComponent(
		$name)
	{
		return Callback::invokeArgs($this->createComponentCallBack, [
			$name
		]);
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nette\Application\UI\Component::redirect()
	 */
	public function redirect(
		$code,
		$destination = null,
		$args = array())
	{
		$res = new RedirectResponse($code . $destination);
		$this->sendResponse($res);
	}

	/**
	 *
	 * @return BaseForm
	 */
	public function createForm()
	{
		return new BaseForm();
	}

	public function logHistory()
	{
	}

	public function flashInfo()
	{
	}

	public function flashSuccess()
	{
	}

	public function flashWarning()
	{
	}

	public function flashDanger()
	{
	}
}