<?php

namespace App\Models\Events;

use Symfony\Component\EventDispatcher\Event;
use App\Extensions\Components\IFlashMessageConstants;

class FlashMessageEvent extends Event{

	const NAME = 'application.flashmessage';

	/** @var string */
	public $message;

	/** @var string */
	public $type;

	/**
	 * @param string $message
	 * @param string $type
	 */
	public function __construct(
		$message,
		$type)
	{
		$this->message = $message;
		$this->type = $type;
	}

	public static function info(
		$message)
	{
		return new self($message, IFlashMessageConstants::FLASH_INFO);
	}

	public static function infoDisp(
		$message)
	{
		return [
			self::NAME,
			self::info($message)
		];
	}

	public static function warning(
		$message)
	{
		return new self($message, IFlashMessageConstants::FLASH_WARNING);
	}

	public static function warningDisp(
		$message)
	{
		return [
			self::NAME,
			self::warning($message)
		];
	}

	public static function danger(
		$message)
	{
		return new self($message, IFlashMessageConstants::FLASH_DANGER);
	}

	public static function dangerDisp(
		$message)
	{
		return [
			self::NAME,
			self::danger($message)
		];
	}

	public static function success(
		$message)
	{
		return new self($message, IFlashMessageConstants::FLASH_SUCCESS);
	}

	public static function successDisp(
		$message)
	{
		return [
			self::NAME,
			self::success($message)
		];
	}
}