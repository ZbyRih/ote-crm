<?php

namespace App\Models\Events;

use Symfony\Component\EventDispatcher\Event;

class CiselnikChangedEvent extends Event{

	const NAME = 'ciselnik.changed';
}