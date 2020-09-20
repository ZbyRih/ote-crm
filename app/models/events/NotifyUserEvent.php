<?php

namespace App\Models\Events;

use Symfony\Component\EventDispatcher\Event;

class NotifyUserEvent extends Event{

	const NAME = 'user.notify';

	/** @var string */
	public $message;

	/** @var string InfoEntity::TYPE_* */
	public $type;

	public function __construct()
	{
	}
}