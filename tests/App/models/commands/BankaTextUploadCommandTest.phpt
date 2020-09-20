<?php

namespace Tests\App\Models\Commands;

require_once __DIR__ . '/../../../bootstrap.php';

use App\Models\Commands\IBankaTextUploadCommand;
use App\Models\Events\DBBeginEvent;
use App\Models\Events\DBRollbackEvent;
use App\Models\Orm\Orm;
use Contributte\EventDispatcher\EventDispatcher;
use Tester\Assert;
use Tests\Utils\IntegrationTestCase;
use App\Models\DTO\InfoData;
use App\Models\Enums\InfoEnums;
use App\Models\Enums\PlatbyImportEnums;

class BankaTextUploadCommandTest extends IntegrationTestCase{

	/** @var Orm */
	private $orm;

	/** @var IBankaTextUploadCommand */
	private $facCmd;

	/** @var EventDispatcher */
	private $dispatcher;

	public function __construct()
	{
		$c = $this->getContainer();
		$this->orm = $c->getByType(Orm::class);
		$this->facCmd = $c->getByType(IBankaTextUploadCommand::class);
		$this->dispatcher = $c->getByType(EventDispatcher::class);
	}

	public function setUp()
	{
		$this->dispatcher->dispatch(DBBeginEvent::NAME);
	}

	public function tearDown()
	{
		$this->dispatcher->dispatch(DBRollbackEvent::NAME);
	}

	public function testRecreate()
	{
		$info = new InfoData(InfoEnums::TYPE_BANK);
		$file = DATA_DIR . '/banka/text/vypis.txt';

		$cmd = $this->facCmd->create();
		$cmd->setFile(file_get_contents($file));
		$cmd->setInfo($info);
		$cmd->setLimit(PlatbyImportEnums::FILTER_NONE);

		$cmd->execute();

		Assert::true(true);
	}
}

(new BankaTextUploadCommandTest())->run();