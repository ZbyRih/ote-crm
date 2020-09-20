<?php

namespace App\Models\Listeners;

use App\Models\Events\NotifyUserEvent;
use App\Models\Orm\Orm;
use App\Models\Selections\UserSelection;
use Contributte\EventDispatcher\EventSubscriber;

class NotifyUserListener implements EventSubscriber{

	/** @var Orm */
	private $orm;

	/** @var UserSelection */
	private $selUser;

	public function __construct(
		Orm $orm,
		UserSelection $selUser)
	{
		$this->orm = $orm;
		$this->selUser = $selUser;
	}

	public static function getSubscribedEvents()
	{
		return [
			NotifyUserEvent::NAME => 'onNotify'
		];
	}

	public function onNotify(
		NotifyUserEvent $ev)
	{
// 		if(!$ev->message){
// 			return;
// 		}

		// 		if(!$usrs = $this->selUser->getForInfo($ev->type)){
// 			return;
// 		}

		// 		foreach($usrs as $u){
// 			$i = new InfoEntity();
// 			$i->userId = $u->id;
// 			$i->message = (string) $ev->message;
// 			$i->type = $ev->type;

		// 			$this->orm->persist($i);
// 		}
	}
}