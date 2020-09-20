<?php

namespace App\Models\Strategies\Fakturace;

use App\Models\Orm\Orm;
use App\Models\Core\DateRange;
use App\Models\Repositories\SettingsRepository;
use App\Models\Strategies\ILoadPlatbyZalohByRangeStrategy;
use App\Models\Strategies\ILoadZalohyByRangeStrategy;
use App\Models\Strategies\Loaders\LoadPlatbyZalohByRangeStrategy;
use App\Models\Strategies\Zalohy\ZalohyMapPlatbyStrategy;
use App\Models\Strategies\Zalohy\PlatbyConvertToZalohy;
use Carbon\Carbon;

class ZalohyDoFakturyStrategy{

	/** @var Orm */
	private $orm;

	/** @var ILoadZalohyByRangeStrategy */
	private $facLoadZalohy;

	/** @var ILoadPlatbyZalohByRangeStrategy */
	private $facLoadPlatby;

	/** @var float */
	private $defaulfDphCoef;

	/** @var int */
	private $klientId;

	/** @var int */
	private $omId;

	/** @var \DateTimeInterface */
	private $from;

	/** @var \DateTimeInterface */
	private $to;

	public function __construct(
		Orm $orm,
		SettingsRepository $repSettings,
		ILoadZalohyByRangeStrategy $facLoadZalohy,
		ILoadPlatbyZalohByRangeStrategy $facLoadPlatby)
	{
		$this->orm = $orm;
		$this->facLoadZalohy = $facLoadZalohy;
		$this->facLoadPlatby = $facLoadPlatby;
		$this->defaulfDphCoef = $repSettings->dph_koef;
	}

	/**
	 * @param number $klientId
	 */
	public function setKlientId(
		$klientId)
	{
		$this->klientId = $klientId;
	}

	/**
	 * @param number $omId
	 */
	public function setOmId(
		$omId)
	{
		$this->omId = $omId;
	}

	/**
	 * @param \DateTimeInterface $from
	 */
	public function setFrom(
		\DateTimeInterface $from)
	{
		$this->from = Carbon::instance($from);
		$this->from->setTime(0, 0, 0);
	}

	/**
	 * @param \DateTimeInterface $to
	 */
	public function setTo(
		\DateTimeInterface $to)
	{
		$this->to = Carbon::instance($to);
		$this->to->setTime(23, 59, 59);
	}

	public function create()
	{
		$om = $this->orm->odberMist->getById($this->omId);

		if(!$smls = $this->orm->smlOm->getByOmIdAndRange($this->omId, $this->klientId, $this->from, $this->to)){
			throw new \FakturaException(
				'Nenalezena smlouva pro ' . $om->getAsInfo() . ' v rozsahu od: ' . $this->from->format('j.n. Y') . ' do: ' . $this->to->format('j.n. Y') . '.');
		}

		if(count($smls) > 1){
			throw new \FakturaException(
				'Nalezeno více smluv pro ' . $om->getAsInfo() . ' v rozsahu od: ' . $this->from->format('j.n. Y') . ' do: ' . $this->to->format('j.n. Y') . '.');
		}

		$sml = reset($smls);
		if($sml->fakSkupId){
			$smls = $this->orm->smlOm->getByFakSkupAndRange($sml->fakSkupId, $this->klientId, $this->from, $this->to);
		}

		$range = new DateRange($this->from, $this->to);

		$zalLoader = $this->facLoadZalohy->create();
		$zals = $zalLoader->load($this->klientId, $range, $smls);

		$type = $sml->fakSkupId ? LoadPlatbyZalohByRangeStrategy::TYPE_BY_FS : LoadPlatbyZalohByRangeStrategy::TYPE_BY_OM;
		$paramId = $sml->fakSkupId ? $sml->fakSkupId : $this->omId;

		$plaLoader = $this->facLoadPlatby->create();
		$plas = $plaLoader->load($this->klientId, $paramId, $range, $type);

		if(!$plas){
			return [];
		}

		if($type == LoadPlatbyZalohByRangeStrategy::TYPE_BY_FS){
			$strMap = new ZalohyMapPlatbyStrategy();
			$zalPlas = $strMap->map($zals, $plas, $this->defaulfDphCoef);
		}else{
			$strMap = new PlatbyConvertToZalohy();
			$zalPlas = $strMap->convert($zals, $plas, $this->omId, $this->defaulfDphCoef);
		}

		$map = collection($zalPlas);

		$map = $map->filter(
			function (
				$v,
				$k)
			{
				if($v->odber_mist_id != $this->omId){
					return false;
				}

				if($v->when < $this->from){
					return false;
				}

				if($v->when > $this->to){
					return false;
				}

				return true;
			});

		return $map->toArray();
	}
}