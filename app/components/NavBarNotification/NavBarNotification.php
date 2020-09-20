<?php

namespace App\Components;

use App\Extensions\Components\BaseComponent;

// use App\Models\Selections\InfoSelection;
// use App\Models\Repositories\InfoRepository;
class NavBarNotification extends BaseComponent{

// 	/** @var InfoSelection */
// 	private $selInfo;

	// 	/** @var InfoRepository */
// 	private $repInfo;

	/** @var int */
	private $userId;

	public function __construct()
// 		InfoSelection $selInfo,
// 		InfoRepository $repInfo
	{
// 		$this->selInfo = $selInfo;
// 		$this->repInfo = $repInfo;
	}

	/**
	 * @param number $userId
	 */
	public function setUserId(
		$userId)
	{
		$this->userId = $userId;
	}

	public function render()
	{
// 		$this->template->notifs = $this->selInfo->getNew($this->userId);
// 		$this->template->counts = $this->selInfo->getNewCount($this->userId);
		$this->template->setParameters([
			'notifs' => [],
			'counts' => 0
		]);
		parent::render();
	}

	public function handleMarkAsReaded()
	{
// 		$this->repInfo->updateViewed($this->userId);
	}
}