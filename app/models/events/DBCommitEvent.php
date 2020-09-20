<?php

namespace App\Models\Events;

use Symfony\Component\EventDispatcher\Event;

class DBCommitEvent extends Event{

	const NAME = 'db.commit';
}