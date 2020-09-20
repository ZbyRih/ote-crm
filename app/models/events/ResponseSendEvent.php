<?php

namespace App\Models\Events;

use Nette\Application\IResponse;
use Symfony\Component\EventDispatcher\Event;

class ResponseSendEvent extends Event{

	/** @var IResponse */
	public $response;

	const NAME = 'response.send';

	public function disp()
	{
		return [
			self::NAME,
			$this
		];
	}
}