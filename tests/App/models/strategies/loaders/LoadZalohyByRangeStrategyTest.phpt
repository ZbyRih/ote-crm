<?php

namespace Tests\App\Models\Strategies\Loaders;

use App\Models\Strategies\ILoadZalohyByRangeStrategy;
use Tests\Utils\IntegrationTestCase;
use Tester\Assert;
use App\Models\Core\DateRange;
use App\Extensions\Utils\DateTime;
use App\Models\Orm\Orm;

require_once __DIR__ . '/../../../../bootstrap.php';

class LoadZalohyByRangeStrategyTest extends IntegrationTestCase{

	/** @var Orm */
	private $orm;

	/** @var ILoadZalohyByRangeStrategy */
	private $fac;

	const YEAR = 2018;

	const YEAR_COUNT = 300;

	const KLIENT_ID = 97;

	const FA_SKUP_ID = 1;

	const OM_ID = 217;

	public function __construct()
	{
		$c = $this->getContainer();
		$this->orm = $c->getByType(Orm::class);
		$this->fac = $c->getByType(ILoadZalohyByRangeStrategy::class);
	}

	public function testLoad()
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

		$str = $this->fac->create();
		$zals = $str->load(self::KLIENT_ID, $range, $smls);

		Assert::true($zals->count() == self::YEAR_COUNT);
	}
}

(new LoadZalohyByRangeStrategyTest())->run();