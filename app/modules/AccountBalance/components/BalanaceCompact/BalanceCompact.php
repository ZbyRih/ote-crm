<?php

namespace App\Modules\AccountBalance\Components;

use App\Extensions\App\Ciselnik;
use App\Extensions\Components\BaseComponent;
use App\Extensions\Helpers\Formaters;
use App\Models\Orm\Klients\KlientEntity;
use App\Models\Repositories\CiselnikyValuesRepository;
use App\Models\Tables\ZalohaTable;
use App\Models\Tables\FakturaTable;
use App\Models\Tables\PlatbaTable;
use App\Models\Orm\Orm;
use App\Models\Enums\PlatbyEnums;

class BalanceCompact extends BaseComponent{

	/** @var Orm */
	private $orm;

	/** @var ZalohaTable */
	private $tblZalohy;

	/** @var FakturaTable */
	private $tblFaktury;

	/** @var PlatbaTable */
	private $tblPlatby;

	/** @var Ciselnik */
	private $typyPlateb;

	/** @var int */
	private $year;

	/** @var KlientEntity */
	private $klient;

	public function __construct(
		Orm $orm,
		ZalohaTable $tblZalohy,
		FakturaTable $tblFaktury,
		PlatbaTable $tblPlatby,
		CiselnikyValuesRepository $repCisl)
	{
		$this->orm = $orm;
		$this->tblZalohy = $tblZalohy;
		$this->tblPlatby = $tblPlatby;
		$this->tblFaktury = $tblFaktury;

		$this->typyPlateb = $repCisl->getCiselnik('typy_pohybu');
	}

	/**
	 * @param number $year
	 */
	public function setYear(
		$year)
	{
		$this->year = $year;
	}

	/**
	 * @param KlientEntity $klient
	 */
	public function setKlient(
		$klient)
	{
		$this->klient = $klient;
	}

	/**
	 * {@inheritdoc}
	 * @see \App\Extensions\Components\BaseComponent::render()
	 */
	public function render()
	{
		$zalsDefs = $this->tblZalohy->select('vs, od, SUM(vyse) AS `vyse`')
			->where('YEAR(od)', $this->year)
			->where('do < NOW()')
			->where('klient_id', $this->klient->klientId)
			->group('vs')
			->group('od')
			->fetchAll();

		$zalsDefs = collection($zalsDefs)->map(function (
			$v,
			$k)
		{
			return [
				'date' => Formaters::date($v->od),
				'vs' => $v->vs,
				'vyse' => $v->vyse
			];
		});

		$zalsSum = $zalsDefs->sumOf('vyse');

		$faDefs = $this->tblFaktury->select('id, vystaveno, preplatek, cis')
			->where('klient_id', $this->klient->klientId)
			->where('YEAR(vystaveno)', $this->year)
			->where('storno', 0)
			->fetchAll();

		$faDefs = collection($faDefs)->map(
			function (
				$v,
				$k)
			{
				return [
					'cis' => $v->cis,
					'date' => Formaters::date($v->vystaveno),
					'preplatek' => $v->preplatek
				];
			});

		$fasSum = $faDefs->sumOf('preplatek');

		$zalsPlas = $this->tblPlatby->select('platba_id, when, vs, platba, type')
			->where('type', PlatbyEnums::USE_ZALOHA)
			->where('YEAR(when)', $this->year)
			->where('platba_id IN (SELECT `platba_id` FROM `platby_zarazeni` WHERE `klient_id` = ?)', $this->klient->klientId)
			->fetchAll();

		$zalsPlas = collection($zalsPlas)->map(function (
			$v,
			$k)
		{
			return [
				'date' => Formaters::date($v->when),
				'platba' => $v->platba
			];
		});

		$faPlas = $this->tblPlatby->select('platba_id, when, vs, platba, type')
			->where('type', PlatbyEnums::USE_FAKTURA)
			->where('YEAR(when)', $this->year)
			->where('platba_id IN (SELECT `platba_id` FROM `platby_zarazeni` WHERE `klient_id` = ?)', $this->klient->klientId)
			->fetchAll();

		$faPlas = collection($faPlas)->map(function (
			$v,
			$k)
		{
			return [
				'date' => Formaters::date($v->when),
				'platba' => $v->platba
			];
		});

		$zalBalance = $zalsSum - $zalsPlas->sumOf('platba');
		$faBalance = $fasSum - $faPlas->sumOf('platba');

		$this->template->setParameters(
			[
				'year' => $this->year,
				'klient' => $this->klient->klientDetailId->getSalutation(),
				'zalsDefs' => $zalsDefs->toArray(),
				'zalsPlas' => $zalsPlas->toArray(),
				'faDefs' => $faDefs->toArray(),
				'faPlas' => $faPlas->toArray(),
				'zalBalance' => Formaters::price($zalBalance),
				'faBalance' => Formaters::price($faBalance),
				'balance' => Formaters::price($zalBalance + $faBalance)
			]);

		parent::render();
	}
}