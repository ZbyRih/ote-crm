<?php

namespace App\Presenters;

use App\Extensions\Components\BaseForm;
use App\Extensions\Components\IFlashMessageConstants;
use App\Extensions\Components\TFlashMessage;
use App\Models\Services\FormFactoryService;

/**
 *

 * @property-read \Nette\Bridges\ApplicationLatte\Template|\stdClass $template
 */
class CorePresenter extends \Nette\Application\UI\Presenter implements IFlashMessageConstants{

	use TFlashMessage;

	/** @var FormFactoryService @inject */
	public $formFactory;

	public function getResource()
	{
		return $this->name;
	}

	/**
	 *
	 * @return BaseForm
	 */
	public function createForm()
	{
		return $this->formFactory->create();
	}

	public function flashMessage(
		$message,
		$type = self::FLASH_INFO)
	{
		$this->redrawControl('flashes');
		return $this->_flashMassage($message, $type);
	}

	public function silentFlashMessage(
		$message,
		$type = self::FLASH_INFO)
	{
		return $this->_flashMassage($message, $type);
	}

	private function _flashMassage(
		$message,
		$type)
	{
		return parent::flashMessage($message . (!$this->flashTypeExists($type) ? '[' . $type . ' - neexituje !!!]' : ''), $type);
	}

	public function checkStoredRequest(
		$key)
	{
		$session = $this->getSession('Nette.Application/requests');
		if(!isset($session[$key]) || ($session[$key][0] !== null && $session[$key][0] !== $this->getUser()->getId())){
			return false;
		}
		return true;
	}

	/**
	 *
	 * @return \Nette\Application\Request
	 */
	public function getStoredRequest(
		$key)
	{
		$session = $this->getSession('Nette.Application/requests');
		if(!isset($session[$key]) || ($session[$key][0] !== null && $session[$key][0] !== $this->getUser()->getId())){
			return;
		}
		$request = clone $session[$key][1];
		unset($session[$key]);
		$request->setFlag(\Nette\Application\Request::RESTORED, true);
		return $request;
	}
}