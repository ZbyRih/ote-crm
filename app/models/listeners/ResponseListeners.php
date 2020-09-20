<?php


namespace App\Models\Listeners;

use Contributte\EventDispatcher\EventSubscriber;
use App\Models\Events\ResponseSendEvent;
use Nette\Application\Application;

class ResponseListeners implements EventSubscriber
{

	/** @var Application */
	private $app;

	public function __construct(
		Application $app
	) {
		$this->app = $app;
	}

	public static function getSubscribedEvents()
	{
		return [
			ResponseSendEvent::NAME => 'onSend'
		];
	}

	public function onSend(
		ResponseSendEvent $ev
	) {
		$p = $this->app->getPresenter();
		$p->sendResponse($ev->response);
	}
}
