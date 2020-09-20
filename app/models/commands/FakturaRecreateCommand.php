<?php

namespace App\Models\Commands;

use Nette\InvalidStateException;

class FakturaRecreateCommand{

	/** @var int */
	private $id;

	/** @var int */
	private $userId;

	/** @var ILegacyInitCommand */
	private $cmdLegacy;

	public function __construct(
		ILegacyInitCommand $cmdLegacy)
	{
		$this->cmdLegacy = $cmdLegacy;
	}

	/**
	 * @param number $id
	 */
	public function setId(
		$id)
	{
		$this->id = $id;
	}

	/**
	 * @param number $userId
	 */
	public function setUserId(
		$userId)
	{
		$this->userId = $userId;
	}

	public function execute()
	{
		$cmd = $this->cmdLegacy->create();
		$cmd->execute();

		$fak = new \MFaktury();
		if(!$f = $fak->FindOneById($this->id)){
			return;
		}
		if($f[$fak->name]['man']){
			throw new InvalidStateException('Uživatelská faktura (' . $f[$fak->name]['cis'] . ') nelze přegenerovat');
		}

		$gp6 = new \GP6Full();
		$gs = $gp6->FindBy('faktura_id', $this->id, [], [], [
			'GP6Head.from' => 'ASC'
		]);

		(new \OTEFaktura())->setCislo($f[$fak->name]['cis'])
			->setExts(unserialize($f[$fak->name]['params']))
			->setDzp(\OBE_DateTime::getDBToDate($f[$fak->name]['dzp']))
			->setSplatnost(\OBE_DateTime::getDBToDate($f[$fak->name]['splatnost']))
			->setVystaveni(\OBE_DateTime::getDBToDate($f[$fak->name]['vystaveno']))
			->load(\MArray::getKeyValsFromModels($gs, $gp6->name, 'id'), false)
			->build()
			->render()
			->udpate($this->userId, $this->id)
			->sendPdf();
	}
}