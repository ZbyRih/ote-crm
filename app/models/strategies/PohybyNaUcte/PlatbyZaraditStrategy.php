<?php

namespace App\Models\Strategies;

use App\Models\DTO\InfoData;
use App\Models\Enums\PlatbyEnums;
use App\Models\Orm\Orm;
use App\Models\Orm\Platby\PlatbaEntity;
use App\Models\Orm\PlatbyZarazeni\PlatbaZarazeniEntity;
use App\Models\Selections\FakturaSelection;
use App\Models\Selections\ZalohaSelection;

class ZarazeniPlatbyDTO{

	/** @var PlatbaEntity */
	public $p;

	/** @var string */
	public $msg;

	/** @var int|NULL */
	public $klientId;

	/** @var int|NULL */
	public $fakSkupId;

	/** @var int|NULL */
	public $omId;
}

class PlatbyZaraditStrategy{

	/** @var Orm */
	private $orm;

	/** @var FakturaSelection */
	private $selFaks;

	/** @var ZalohaSelection */
	private $selZals;

	/** @var InfoData */
	private $info;

	/**
	 * @param Orm $orm
	 * @param FakturaSelection $selFaks
	 * @param ZalohaSelection $selZals
	 * @param InfoData $info
	 */
	public function __construct(
		Orm $orm,
		InfoData $info,
		FakturaSelection $selFaks,
		ZalohaSelection $selZals)
	{
		$this->orm = $orm;
		$this->info = $info;
		$this->selFaks = $selFaks;
		$this->selZals = $selZals;
	}

	/**
	 * @param InfoData $info
	 */
	public function setInfo(
		InfoData $info)
	{
		$this->info = $info;
	}

	/**
	 * @param PlatbaEntity[] $plas
	 * @return PlatbaEntity[] $plas
	 */
	public function zaradit(
		$plas)
	{
		foreach($plas as $p){
			$vs = ltrim($p->vs, '0');

			if($this->tryFaktura($p, $vs)){
				continue;
			}

			if($this->tryZaloha($p, $vs)){
				continue;
			}

			$this->tryOthers($p, $vs);
		}

		return $plas;
	}

	private function tryFaktura(
		PlatbaEntity $p,
		$vs)
	{
		if(!$fas = $this->selFaks->lookByVs($vs)){
			return false;
		}

		$str = new PlatbaZarazeniFakturyStrategy();
		$str->setOrm($this->orm);
		$str->zaradit($p, $fas)
			->then(function (
			ZarazeniPlatbyDTO $res)
		{
			$this->info->addInfo($res->msg);
			$this->setUp($res, PlatbyEnums::USE_FAKTURA);
		})
			->otherwise(function (
			$error)
		{
			$this->info->addWarning($error);
		});

		return true;
	}

	private function tryZaloha(
		PlatbaEntity $p,
		$vs)
	{
		if(!$zals = $this->selZals->lookByVs($vs, $p->when)){
			return false;
		}

		$str = new PlatbaZarazeniZalohyStrategy();
		$str->setOrm($this->orm);
		$str->zaradit($p, $zals)
			->then(function (
			ZarazeniPlatbyDTO $res)
		{
			$this->info->addInfo($res->msg);
			$this->setUp($res, PlatbyEnums::USE_ZALOHA);
		})
			->otherwise(function (
			$error)
		{
			$this->info->addWarning($error);
		});

		return true;
	}

	private function tryOthers(
		PlatbaEntity $p,
		$vs)
	{
		if($p->platba < 0){
			$p->type = PlatbyEnums::USE_OTHERS;
			$this->info->addInfo(sprintf('Platba `%s` s vs: `%s` od `%s` zařazena do Ostatní', $p->platba, $p->vs, $p->subject));
		}
	}

	private function setUp(
		ZarazeniPlatbyDTO $res,
		$type)
	{
		$z = new PlatbaZarazeniEntity();
		$z->klientId = $res->klientId;
		$z->fakskupId = $res->fakSkupId;
		$z->omId = $res->omId;
		$z->platba = $res->p;

		$res->p->type = $type;
		$res->p->zarazeni = $z;
	}
}