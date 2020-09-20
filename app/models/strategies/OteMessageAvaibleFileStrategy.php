<?php

namespace App\Models\Strategies;

use App\Models\Orm\OteMessages\OteMessageEntity;

class OteMessageAvaibleFileStrategy{

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
		$strg = $this->getStrg();
		$strg->setOteMsg($this->oteMsg);
		return $strg->getFile();
	}

	public function getStrg()
	{
		if($this->oteMsg->fileXml){
			return new OteMessageXmlFileStrategy();
		}else{
			return new OteMessageEmlFileStrategy();
		}
	}
}