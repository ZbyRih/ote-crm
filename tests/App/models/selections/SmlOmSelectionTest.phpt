<?php

namespace Tests\App\Models\Selections;

require_once __DIR__ . '/../../../bootstrap.php';

use Tests\Utils\IntegrationTestCase;
use App\Models\Selections\SmlOmSelection;
use Tester\Assert;

class SmlOmSelectionTest extends IntegrationTestCase{

	/** @var SmlOmSelection */
	private $tbl;

	public function __construct()
	{
		$c = $this->getContainer();
		$this->tbl = $c->getByType(SmlOmSelection::class);
	}

	public function testRange()
	{
		$years = $this->tbl->getYears();
		$min = min($years);
		$max = max($years);

		Assert::same(2012, $min);
		Assert::same(2020, $max);
	}
}

(new SmlOmSelectionTest())->run();