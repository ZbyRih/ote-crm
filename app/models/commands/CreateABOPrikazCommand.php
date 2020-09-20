<?php

namespace App\Models\Commands;

use App\Extensions\Interfaces\ICommand;
use App\Extensions\Interfaces\IStream;
use App\Models\Orm\Orm;
use App\Models\Repositories\SettingsRepository;
use App\Models\Strategies\Fakturace\DTOABOItem;
use App\Models\Values\AccountValue;
use App\Models\ABO\ABO;
use App\Models\ABO\ABOGroup;
use Nette\Utils\DateTime;
use App\Models\ABO\ABOItem;

class CreateABOPrikazCommand implements ICommand{

	/** @var Orm */
	private $orm;

	/** @var SettingsRepository */
	private $repSettings;

	/** @var DTOABOItem[] */
	private $items;

	/** @var IStream */
	private $stream;

	public function __construct(
		Orm $orm,
		SettingsRepository $repSettings)
	{
		$this->orm = $orm;
		$this->repSettings = $repSettings;
	}

	/**
	 * @param DTOABOItem[] $items
	 */
	public function setItems(
		$items)
	{
		$this->items = $items;
	}

	/**
	 * @param mixed $stream
	 */
	public function setStream(
		IStream $stream)
	{
		$this->stream = $stream;
	}

	public function execute()
	{
		$tcu = new AccountValue($this->repSettings->cislo_uctu);

		$abo = new ABO();
		$abo->setBank($tcu->toBank());

		$g = new ABOGroup();
		$g->setDate(new DateTime());

		$c = collection($this->items)->each(
			function (
				DTOABOItem $v,
				$k) use (
			$tcu,
			$g)
			{
				$i = new ABOItem();
				$i->setAmount(abs($v->fa->preplatek));
				$i->srcAccount = $tcu;
				$i->destAccount = $v->cu;
				$i->variableSym = $v->fa->cis;

				$g->addItem($i);
			});

		$abo->addGroup($g);

		$this->stream->put($abo->create());

		$c->each(function (
			DTOABOItem $v,
			$k)
		{
			$v->fa->uhrazenoDne = new DateTime();
			$this->orm->persist($v->fa);
		});
	}
}