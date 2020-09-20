<?php

namespace Tests\App\Models\Strategies;

require_once __DIR__ . '/../../../bootstrap.php';

use App\Models\Strategies\IZalohyDoRozpisuZalohStrategy;
use Tester\Assert;
use Tests\Utils\IntegrationTestCase;
use App\Models\DTO\TiskRospisZalohDTO;

class ZalohyDoRozpisuZalohStrategyTest extends IntegrationTestCase{

	/** @var IZalohyDoRozpisuZalohStrategy */
	private $fac;

	const YEAR = 2018;

	const KLIENT_ID = 97;

	const ODBER_MIST_ID = 236;

	const SKUP_ODBER_MIST_ID = 216;

	const FA_SKUP_ID = 1;

	public function __construct()
	{
		$c = $this->getContainer();

		$this->fac = $c->getByType(IZalohyDoRozpisuZalohStrategy::class);
	}

	public function testOm()
	{
		$params = new TiskRospisZalohDTO([
			'year' => self::YEAR,
			'klientId' => self::KLIENT_ID,
			'omId' => self::ODBER_MIST_ID,
			'fakSkupId' => null
		]);

		$str = $this->fac->create();
		$str->setParams($params);
		$ret = $str->create();

		Assert::true(count($ret) > 0);
	}

	public function testSkupOm()
	{
		$params = new TiskRospisZalohDTO([
			'year' => self::YEAR,
			'klientId' => self::KLIENT_ID,
			'omId' => self::SKUP_ODBER_MIST_ID,
			'fakSkupId' => null
		]);

		$str = $this->fac->create();
		$str->setParams($params);
		$ret = $str->create();

		Assert::true(count($ret) > 0);
	}

	public function testFS()
	{
		$params = new TiskRospisZalohDTO([
			'year' => self::YEAR,
			'klientId' => self::KLIENT_ID,
			'omId' => null,
			'fakSkupId' => self::FA_SKUP_ID
		]);

		$str = $this->fac->create();
		$str->setParams($params);
		$ret = $str->create();

		Assert::true(count($ret) > 9);
	}
}

(new ZalohyDoRozpisuZalohStrategyTest())->run();