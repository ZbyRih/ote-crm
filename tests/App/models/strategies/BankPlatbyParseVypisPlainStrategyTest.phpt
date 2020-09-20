<?php

namespace Tests\App\Models\Strategies;

require_once __DIR__ . '/../../../bootstrap.php';

use App\Models\Services\InfoService;
use App\Models\Strategies\BankPlatbyParseVypisPlainStrategy;
use App\Models\Strategies\VypisPlainPartToPlatbaEntityStrategy;
use App\Models\Orm\Orm;
use App\Models\Strategies\BankaFilterDuplicatesStrategy;
use App\Models\Tables\PlatbaTable;
use App\Models\Enums\InfoEnums;
use Tester\Assert;
use Tests\Utils\IntegrationTestCase;

class BankPlatbyParseVypisPlainStrategyTest extends IntegrationTestCase{

	/** @var PlatbaTable */
	private $tbl;

	/** @var InfoService */
	private $info;

	const DPH_COEF = 0.1458;

	public function __construct()
	{
		$this->getContainer()->getByType(Orm::class);
		$this->tbl = $this->getContainer()->getByType(PlatbaTable::class);
		$this->info = $this->getContainer()->getByType(InfoService::class);
	}

	public function testRead()
	{
		$info = $this->info->createObj(InfoEnums::TYPE_BANK);
		$cnt = file_get_contents(__DIR__ . '/data/vypis.txt');
		$str = new BankPlatbyParseVypisPlainStrategy($info);
		$parts = $str->read($cnt);

		Assert::count(449, $parts);

		$plas = [];
		$str = new VypisPlainPartToPlatbaEntityStrategy(self::DPH_COEF);
		foreach($parts as $p){
			$plas[] = $str->convert($p);
		}

		Assert::count(449, $plas);

		$str = new BankaFilterDuplicatesStrategy($this->tbl);
		$plas = $str->filter($plas);

		Assert::count(230, $plas);
	}
}

(new BankPlatbyParseVypisPlainStrategyTest())->run();