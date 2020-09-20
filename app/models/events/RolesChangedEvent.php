<?php

namespace App\Models\Events;

use Symfony\Component\EventDispatcher\Event;

class RolesChangedEvent extends Event{

	const NAME = 'roles.changed';
}