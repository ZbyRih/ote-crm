<?php

namespace Tests\App\Models\Strategies;

require_once __DIR__ . '/../../../bootstrap.php';

use App\Extensions\Utils\DateTime;
use App\Models\Orm\Orm;
use App\Models\Strategies\ILoadPlatbyZalohByRangeStrategy;
use App\Models\Strategies\ILoadZalohyByRangeStrategy;
use App\Models\Strategies\Loaders\LoadPlatbyZalohByRangeStrategy;
use Tests\Utils\IntegrationTestCase;
use App\Models\Strategies\Zalohy\ZalohyMapPlatbyStrategy;
use Tester\Assert;
use App\Models\Core\DateRange;

class ZalohyMapPlatbyStrategyTest extends IntegrationTestCase{

	/** @var Orm */
	private $orm;

	/** @var ILoadZalohyByRangeStrategy */
	private $facLoadZalohy;

	/** @var ILoadPlatbyZalohByRangeStrategy */
	private $facLoadPlatby;

	const YEAR = 2018;

	const KLIENT_ID = 97;

	const FA_SKUP_ID = 1;

	public function __construct()
	{
		$c = $this->getContainer();
		$this->orm = $c->getByType(Orm::class);
		$this->facLoadZalohy = $c->getByType(ILoadZalohyByRangeStrategy::class);
		$this->facLoadPlatby = $c->getByType(ILoadPlatbyZalohByRangeStrategy::class);
	}

	public function testMap()
	{
		$od = DateTime::firstDayOfYear(self::YEAR);
		$od->setTime(0, 0);
		$do = DateTime::lastDayOfYear(self::YEAR);
		$do->setTime(23, 59, 59);

		$range = new DateRange($od, $do);

		$smls = $this->orm->smlOm->findBy([
			'klientId' => self::KLIENT_ID,
			'fakSkupId' => self::FA_SKUP_ID
		]);

		$zalLoader = $this->facLoadZalohy->create();
		$zals = $zalLoader->load(self::KLIENT_ID, $range, $smls);

		$plaLoader = $this->facLoadPlatby->create();
		$plas = $plaLoader->load(self::KLIENT_ID, self::FA_SKUP_ID, $range, LoadPlatbyZalohByRangeStrategy::TYPE_BY_FS);

		$sumZal = $zals->sumOf('vyse');
		$sumPla = $plas->sumOf('platba');

		$str = new ZalohyMapPlatbyStrategy();
		$res = $str->map($zals, $plas, 0);

		$sumUhr = collection($res)->sumOf('uhrazeno');

		Assert::true($sumUhr == $sumPla);
	}
}

(new ZalohyMapPlatbyStrategyTest())->run();