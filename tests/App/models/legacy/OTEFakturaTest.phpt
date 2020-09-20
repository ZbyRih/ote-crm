<?php

namespace Tests\App\Models\Legacy;

use App\Models\Commands\ILegacyInitCommand;
use Tests\Utils\IntegrationTestCase;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

class OTEFakturaTest extends IntegrationTestCase{

	/** @var ILegacyInitCommand */
	private $cmdLegacy;

	const GP6_ID = 4320;

	public function __construct()
	{
		$c = $this->getContainer();
		$this->cmdLegacy = $c->getByType(ILegacyInitCommand::class);
	}

	public function testBuild()
	{
		$cmd = $this->cmdLegacy->create();
		$cmd->execute();

		$F = (new \OTEFaktura(true))->load([
			self::GP6_ID
		])->build();

		Assert::true(true);
	}
}

(new OTEFakturaTest())->run();