<?php

namespace App\Models\Strategies;

use App\Models\Orm\OteMessages\OteMessageEntity;
use App\Models\Resources\OteEmlFileResource;

class OteMessageEmlFileStrategy{

	/** @var OteMessageEntity */
	private $oteMsg;

	public function __construct()
	{
	}

	/**
	 *
	 * @param OteMessageEntity $oteMsg
	 */
	public function setOteMsg(
		$oteMsg)
	{
		$this->oteMsg = $oteMsg;
	}

	public function getFormatedContent()
	{
		return $this->getFile()->getFormatedContent();
	}

	public function getFile()
	{
		$msgId = trim(trim($this->oteMsg->msgUid, '>'), '<');
		return new OteEmlFileResource($this->oteMsg->received->format('Y'), $this->oteMsg->oteKod, $msgId);
	}
}