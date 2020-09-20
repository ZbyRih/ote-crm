<?php

namespace App\Models\Listeners;

use App\Models\Events\FlashMessageEvent;
use Contributte\EventDispatcher\EventSubscriber;
use Nette\Application\IPresenter;
use Nette\Application\Application;

class FlashMessageListener implements EventSubscriber{

	/** @var IPresenter */
	private $presenter;

	public function __construct(
		Application $app)
	{
		$this->presenter = $app->getPresenter();
	}

	public static function getSubscribedEvents()
	{
		return [
			FlashMessageEvent::NAME => 'handle'
		];
	}

	public function handle(
		FlashMessageEvent $ev)
	{
		$this->presenter->flashMessage($ev->message, $ev->type);
	}
}