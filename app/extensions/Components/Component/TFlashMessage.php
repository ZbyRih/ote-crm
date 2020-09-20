<?php

namespace App\Extensions\Components;

interface IFlashMessageConstants{

	const FLASH_INFO = 'info', FLASH_SUCCESS = 'success', FLASH_WARNING = 'warning', FLASH_DANGER = 'danger';
}

trait TFlashMessage{

	/** @var array */
	static $flashMessageTypes = [
		IFlashMessageConstants::FLASH_DANGER => true,
		IFlashMessageConstants::FLASH_INFO => true,
		IFlashMessageConstants::FLASH_WARNING => true,
		IFlashMessageConstants::FLASH_SUCCESS => true
	];

	public function flashInfo(
		$message)
	{
		$this->flashMessage($message, IFlashMessageConstants::FLASH_INFO);
	}

	public function flashSuccess(
		$message)
	{
		$this->flashMessage($message, IFlashMessageConstants::FLASH_SUCCESS);
	}

	public function flashWarning(
		$message)
	{
		$this->flashMessage($message, IFlashMessageConstants::FLASH_WARNING);
	}

	public function flashDanger(
		$message)
	{
		$this->flashMessage($message, IFlashMessageConstants::FLASH_DANGER);
	}

	protected function flashTypeExists(
		$type)
	{
		return array_key_exists($type, self::$flashMessageTypes);
	}
}