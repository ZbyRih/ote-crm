<?php

namespace App\Models\Listeners;

use Nette\Database\Context;
use App\Models\Orm\Orm;
use Contributte\EventDispatcher\EventSubscriber;
use App\Models\Events\DBCommitEvent;
use App\Models\Events\DBRollbackEvent;
use Nextras\Orm\Model\IModel;
use App\Models\Events\DBBeginEvent;

class DBListeners implements EventSubscriber{

	/** @var Orm */
	private $orm;

	/** @var Context */
	private $db;

	/** @var bool */
	private $transaction;

	public function __construct(
		Context $db,
		Orm $orm)
	{
		$this->db = $db;
		$this->orm = $orm;
		$this->transaction = false;
	}

	public static function getSubscribedEvents()
	{
		return [
			DBBeginEvent::NAME => 'onBegin',
			DBCommitEvent::NAME => 'onCommit',
			DBRollbackEvent::NAME => 'onRollback'
		];
	}

	public function onBegin()
	{
		if($this->transaction){
			return;
		}
		$this->transaction = true;
		$this->db->beginTransaction();
	}

	public function onCommit()
	{
		if($this->transaction){
			$this->db->commit();
		}
		$this->transaction = false;
		$this->orm->flush();
	}

	public function onRollback()
	{
		if($this->transaction){
			$this->db->rollBack();
		}
		$this->transaction = false;
		$this->orm->address->getMapper()->rollback();
//     	$this->orm->refreshAll(); od verze 3.1
		$this->orm->clearIdentityMapAndCaches(IModel::I_KNOW_WHAT_I_AM_DOING);
	}
}