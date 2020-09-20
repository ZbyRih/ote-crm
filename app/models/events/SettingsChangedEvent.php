<?php

namespace App\Models\Events;

use Symfony\Component\EventDispatcher\Event;

class SettingsChangedEvent extends Event{

	const NAME = 'settings.changed';
}