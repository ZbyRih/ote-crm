<?php

namespace App\Models\Events;

use Symfony\Component\EventDispatcher\Event;

class DBBeginEvent extends Event{

	const NAME = 'db.begin';
}