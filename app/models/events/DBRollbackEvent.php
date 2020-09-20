<?php

namespace App\Models\Events;

use Symfony\Component\EventDispatcher\Event;

class DBRollbackEvent extends Event{

	const NAME = 'db.rollback';
}