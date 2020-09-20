<?php

namespace Tests\App\Models\Strategies;

require_once __DIR__ . '/../../../../bootstrap.php';

use App\Models\Orm\Orm;
use App\Models\Strategies\Fakturace\LoadABODTOItemStrategy;
use App\Models\Tables\SmlOmTable;
use Tester\Assert;
use Tests\Utils\IntegrationTestCase;

class LoadABODTOItemStrategyTest extends IntegrationTestCase{

	/** @var Orm */
	public $orm;

	/** @var SmlOmTable */
	public $tbl;

	const FAK_IDS = [
		1234,
		1252,
		701
	];

	public function __construct()
	{
		$c = $this->getContainer();
		$this->orm = $c->getByType(Orm::class);
		$this->tbl = $c->getByType(SmlOmTable::class);
	}

	public function testFakSkupCisloUctu()
	{
		$str = new LoadABODTOItemStrategy($this->orm, $this->tbl);
		$items = $str->get(self::FAK_IDS);

		$cus = collection($items)->indexBy('fa.id')
			->extract('cu')
			->toArray();

		Assert::true((string) $cus[701] == '7386456001/5500');
	}
}

(new LoadABODTOItemStrategyTest())->run();