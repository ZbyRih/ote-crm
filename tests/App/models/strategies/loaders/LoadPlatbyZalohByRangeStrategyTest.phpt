<?php

/**
 * @author: Zbynek Riha
 * @copyright: 2019 Neutral Solution s.r.o., http://www.neutral-solution.cz/
 */
namespace Tests\App\Models\Strategies\Loaders;

use App\Extensions\Utils\DateTime;
use App\Models\Core\DateRange;
use App\Models\Strategies\ILoadPlatbyZalohByRangeStrategy;
use App\Models\Strategies\Loaders\LoadPlatbyZalohByRangeStrategy;
use Tester\Assert;
use Tests\Utils\IntegrationTestCase;

require_once __DIR__ . '/../../../../bootstrap.php';

class LoadPlatbyZalohByRangeStrategyTest extends IntegrationTestCase{

	/** @var ILoadPlatbyZalohByRangeStrategy */
	private $fac;

	const YEAR = 2018;

	const YEAR_COUNT = 10;

	const KLIENT_ID = 97;

	const FA_SKUP_ID = 1;

	const OM_ID = 217;

	public function __construct()
	{
		$c = $this->getContainer();
		$this->fac = $c->getByType(ILoadPlatbyZalohByRangeStrategy::class);
	}

	public function testLoadFS()
	{
		$od = DateTime::firstDayOfYear(self::YEAR);
		$od->setTime(0, 0);
		$do = DateTime::lastDayOfYear(self::YEAR);
		$do->setTime(23, 59, 59);

		$range = new DateRange($od, $do);

		$str = $this->fac->create();

		$plas = $str->load(self::KLIENT_ID, self::FA_SKUP_ID, $range, LoadPlatbyZalohByRangeStrategy::TYPE_BY_FS);

		Assert::true($plas->count() == self::YEAR_COUNT);
	}

	public function testLoadOM()
	{
		$od = DateTime::firstDayOfYear(self::YEAR);
		$od->setTime(0, 0);
		$do = DateTime::lastDayOfYear(self::YEAR);
		$do->setTime(23, 59, 59);

		$range = new DateRange($od, $do);

		$str = $this->fac->create();

		$plas = $str->load(self::KLIENT_ID, self::OM_ID, $range, LoadPlatbyZalohByRangeStrategy::TYPE_BY_OM);

		Assert::true($plas->count() == 12);
	}
}

(new LoadPlatbyZalohByRangeStrategyTest())->run();