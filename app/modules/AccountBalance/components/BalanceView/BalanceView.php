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

class BalanceView extends BaseComponent{

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
		$zals = $this->tblZalohy->select('vs, od, SUM(vyse) AS `vyse`')
			->where('YEAR(od)', $this->year)
			->where('do < NOW()')
			->where('klient_id', $this->klient->klientId)
			->group('vs')
			->group('od')
			->fetchAll();

		$cz = collection($zals)->map(
			function (
				$v,
				$k)
			{
				return [
					'id' => null,
					't' => 1,
					'date' => $v->od,
					'druh' => 'Záloha',
					'sum' => $v->vyse,
					'type' => null,
					'vs' => $v->vs
				];
			});

		$faks = $this->tblFaktury->select('id, vystaveno, preplatek, cis')
			->where('klient_id', $this->klient->klientId)
			->where('YEAR(vystaveno)', $this->year)
			->where('storno', 0)
			->fetchAll();

		$cf = collection($faks)->map(
			function (
				$v,
				$k)
			{
				return [
					'id' => $v->id,
					't' => 2,
					'date' => $v->vystaveno,
					'druh' => 'Faktura',
					'sum' => $v->preplatek,
					'type' => null,
					'vs' => $v->cis
				];
			});

		$plas = $this->tblPlatby->select('platba_id, when, vs, platba, type')
			->where('YEAR(when)', $this->year)
			->where('platba_id IN (SELECT `platba_id` FROM `platby_zarazeni` WHERE `klient_id` = ?)', $this->klient->klientId)
			->fetchAll();

		$cp = collection($plas)->map(
			function (
				$v,
				$k)
			{
				return [
					'id' => $v->platba_id,
					't' => 3,
					'date' => $v->when,
					'druh' => 'Platba',
					'sum' => $v->platba,
					'type' => $v->type ? $this->typyPlateb->byVal($v->type) : null,
					'vs' => $v->vs
				];
			});

		$full = $cz->append($cf)
			->append($cp)
			->sortBy(function (
			$v)
		{
			return $v['date']->getTimestamp() . '-' . $v['t'];
		}, SORT_ASC);

		$sum = $full->sumOf(function (
			$v)
		{
			return $v['sum'] * ($v['t'] == 3 ? -1.0 : 1);
		});

		$items = $full->map(function (
			$v)
		{
			$v['date'] = $v['date']->format('d.m. Y');
			$v['sum'] = Formaters::price($v['sum']);
			return $v;
		})
			->toArray();

		$this->template->setParameters(
			[
				'year' => $this->year,
				'klient' => $this->klient->klientDetailId->getSalutation(),
				'items' => $items,
				'sum' => Formaters::price($sum)
			]);

		parent::render();
	}
}