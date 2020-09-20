<?php

namespace App\Models\Strategies\Zalohy;

use App\Extensions\Utils\DateTime;
use App\Components\Tisk\Zaloha\ZalohaItemEntry;
use App\Components\Tisk\Zaloha\ZalohaSestavaEntry;
use App\Models\Core\DateRange;
use App\Models\DTO\TiskRospisZalohDTO;
use App\Models\Orm\Orm;
use App\Models\Strategies\ILoadPlatbyZalohByRangeStrategy;
use App\Models\Strategies\ILoadZalohyByRangeStrategy;
use App\Models\Strategies\Loaders\LoadPlatbyZalohByRangeStrategy;
use App\Models\Values\PlatbaValue;
use App\Models\Repositories\SettingsRepository;
use App\ZalohaSestavaSumEntry;
use Cake\Collection\Collection;
use Carbon\Carbon;
use App\Models\Orm\FakSkups\FakSkupEntity;
use App\Models\Orm\OdberMists\OdberMistEntity;

class ZalohyDoRozpisuZalohStrategy{

	/** @var Orm */
	private $orm;

	/** @var ILoadZalohyByRangeStrategy */
	private $facLoadZalohy;

	/** @var ILoadPlatbyZalohByRangeStrategy */
	private $facLoadPlatby;

	/** @var TiskRospisZalohDTO */
	private $params;

	/** @var int */
	private $splaDay;

	/** @var float */
	private $defaulfDphCoef;

	/** @var FakSkupEntity|NULL */
	private $fk;

	/** @var FakSkupEntity|NULL */
	private $fkIn;

	/** @var OdberMistEntity|NULL */
	private $om;

	public function __construct(
		Orm $orm,
		SettingsRepository $repSettings,
		ILoadZalohyByRangeStrategy $facLoadZalohy,
		ILoadPlatbyZalohByRangeStrategy $facLoadPlatby)
	{
		$this->orm = $orm;
		$this->splaDay = $repSettings->splatnost;
		$this->facLoadZalohy = $facLoadZalohy;
		$this->facLoadPlatby = $facLoadPlatby;
		$this->defaulfDphCoef = $repSettings->dph_koef;
	}

	/**
	 * @param TiskRospisZalohDTO $params
	 */
	public function setParams(
		TiskRospisZalohDTO $params)
	{
		$this->params = $params;
	}

	/**
	 * @throws RopisZalohException
	 * @return []
	 */
	public function create()
	{
		$from = DateTime::firstDayOfYear($this->params->year);
		$to = DateTime::lastDayOfYear($this->params->year);
		$to->setTime(23, 59, 59);

		$range = new DateRange($from, $to);

		$fakSkupId = null;

		if($this->params->omId){
			$smls = $this->orm->smlOm->getByOmIdAndRange($this->params->omId, $this->params->klientId, $range->od, $range->do);
			$csmls = collection($smls)->filter(function (
				$v)
			{
				return $v->fakSkupId;
			});

			if($csmls->count() > 0){
				$fakSkups = array_unique($csmls->extract('fakSkupId')->toList());

				if(count($fakSkups) > 1 && !$this->params->fakSkupId){
					throw new RopisZalohException('V roce ' . $this->params->year . ' je odběrné místo ve více fakturačních skupinách.');
				}

				$fakSkupId = reset($fakSkups);
			}
		}

		if($this->params->fakSkupId){
			$fakSkupId = $this->params->fakSkupId;
			$smls = $this->orm->smlOm->getByFakSkupInYear($this->params->klientId, $this->params->year, $this->params->fakSkupId);
		}

		$zalLoader = $this->facLoadZalohy->create();
		$zals = $zalLoader->load($this->params->klientId, $range, $smls);

		$type = $fakSkupId ? LoadPlatbyZalohByRangeStrategy::TYPE_BY_FS : LoadPlatbyZalohByRangeStrategy::TYPE_BY_OM;
		$paramId = $fakSkupId ? $fakSkupId : $this->params->omId;

		$plaLoader = $this->facLoadPlatby->create();
		$plas = $plaLoader->load($this->params->klientId, $paramId, $range, $type);

		$this->fk = $this->orm->fakSkups->getById($fakSkupId);
		$this->fkIn = $this->orm->fakSkups->getById($this->params->fakSkupId);
		$this->om = $this->orm->fakSkups->getById($this->params->omId);

		$strMap = new ZalohyMapPlatbyStrategy();

		$map = collection($strMap->map($zals, $plas, $this->defaulfDphCoef));

		$map = $map->map(function (
			$v,
			$k)
		{
			return $this->convertToEntry($v);
		});

		if($this->params->omId){
			$map = $map->filter(function (
				$v)
			{
				return $v->omId == $this->params->omId;
			});
		}

		if($this->params->fakSkupId){
			$map = $this->transformByFs($map);
		}else{
			$map = $this->transformByOm($map);
		}
		return $map->toArray();
	}

	private function transformByFs(
		Collection $map)
	{
		return $map->groupBy(function (
			$v)
		{
			return $v->month;
		})->map(
			function (
				$v,
				$k)
			{
				$r = $this->convertItem($this->reduce($v));
				$sum = new ZalohaSestavaSumEntry($r);
				return new ZalohaSestavaEntry([
					'fk' => true,
					'vs' => $this->fkIn ? $this->fkIn->cis : null,
					'month' => $k,
					'items' => $v,
					'sum' => $sum
				]);
			});
	}

	private function transformByOm(
		Collection $map)
	{
		return $map->groupBy('omId')->map(
			function (
				$v,
				$k)
			{
				$r = $this->convertItem($this->reduce($v));
				$sum = new ZalohaSestavaSumEntry($r);
				return new ZalohaSestavaEntry([
					'fk' => false,
					'vs' => '',
					'month' => 0,
					'items' => $v,
					'sum' => $sum
				]);
			});
	}

	private function reduce(
		$items)
	{
		return collection($items)->reduce(
			function (
				$reduced,
				$i)
			{
				$reduced = $this->convertItem($reduced);

				$reduced['full'] += $i->vyse;
				$reduced['zakl'] += $i->zakl;
				$reduced['dph'] += $i->dph;
				$reduced['uhr'] += $i->uhrazeno;
				return $reduced;
			});
	}

	private function convertItem(
		$item)
	{
		if(!$item instanceof ZalohaItemEntry){
			return $item;
		}

		return [
			'full' => $item->vyse,
			'zakl' => $item->zakl,
			'dph' => $item->dph,
			'uhr' => $item->uhrazeno
		];
	}

	private function convertToEntry(
		$item)
	{
		$od = Carbon::instance($item->od);
		$do = Carbon::instance($item->do);

		$m = $item->od->format('m');
		$spl = Carbon::instance($item->od);

		if($od->diffInMonths($do) > 1){
			$spl->addDays((int) ($od->diffInDays($do) / 2));
		}else{
			$spl->day = $this->splaDay;
		}

		$om = $this->orm->odberMist->getById($item->odber_mist_id);
		$val = new PlatbaValue($item->vyse, $item->dcoef);

		return new ZalohaItemEntry(
			[
				'omId' => $item->odber_mist_id,
				'month' => $m,
				'splatno' => $spl,
				'name' => $om->addressId->getUlCpCo(),
				'com' => $om->com,
				'zakl' => $val->zaklad,
				'dph' => $val->dph,
				'vyse' => $item->vyse,
				'uhrazeno' => $item->uhrazeno
			]);
	}
}