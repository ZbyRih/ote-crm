<?php

namespace Tests\App\Models\Strategies;

require_once __DIR__ . '/../../../bootstrap.php';

use App\Models\Enums\DatesRangeStepsEnums;
use App\Models\Strategies\SteppedDatesRangeStrategy;
use Tester\Assert;
use Tester\TestCase;
use Carbon\Carbon;

class SteppedDatesRangeStrategyTest extends TestCase{

	public function __construct()
	{
	}

	public function testCreate()
	{
		$str = new SteppedDatesRangeStrategy();
		$str->setFrom(new \DateTime('1970-01-01'));
		$str->setTo(new \DateTime('1970-12-31'));
		$str->setInterval(DatesRangeStepsEnums::STEP_QUATER);

		$items = $str->generate();

		Assert::same(4, count($items));

		$str = new SteppedDatesRangeStrategy();
		$str->setFrom(new \DateTime('1970-02-01'));
		$str->setTo(new \DateTime('1970-11-30'));
		$str->setInterval(DatesRangeStepsEnums::STEP_QUATER);

		$items = $str->generate();

		Assert::same(4, count($items));
		Assert::equal(new Carbon('1970-02-01'), $items[0]['od']);
		Assert::equal(new Carbon('1970-11-30'), $items[3]['do']);

		$str = new SteppedDatesRangeStrategy();
		$str->setFrom(new \DateTime('1970-04-01'));
		$str->setTo(new \DateTime('1970-11-30'));
		$str->setInterval(DatesRangeStepsEnums::STEP_QUATER);

		$items = $str->generate();

		Assert::same(3, count($items));
		Assert::equal(new Carbon('1970-04-01'), $items[0]['od']);
		Assert::equal(new Carbon('1970-11-30'), $items[2]['do']);

		$str = new SteppedDatesRangeStrategy();
		$str->setFrom(new \DateTime('1970-01-01'));
		$str->setTo(new \DateTime('1970-12-31'));
		$str->setInterval(DatesRangeStepsEnums::STEP_DEFAULT);

		$items = $str->generate();

		Assert::same(12, count($items));

		$str = new SteppedDatesRangeStrategy();
		$str->setFrom(new \DateTime('1970-02-01'));
		$str->setTo(new \DateTime('1970-11-30'));
		$str->setInterval(DatesRangeStepsEnums::STEP_DEFAULT);

		$items = $str->generate();

		Assert::same(10, count($items));
	}
}

(new SteppedDatesRangeStrategyTest())->run();