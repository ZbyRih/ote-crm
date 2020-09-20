<?php

namespace Tests\App\Models\Commands;

require_once __DIR__ . '/../../../bootstrap.php';

use App\Models\Commands\IBankaGPCUploadCommand;
use App\Models\Events\DBBeginEvent;
use App\Models\Events\DBRollbackEvent;
use App\Models\Orm\Orm;
use Contributte\EventDispatcher\EventDispatcher;
use Tester\Assert;
use Tests\Utils\IntegrationTestCase;
use App\Models\DTO\InfoData;
use App\Models\Enums\InfoEnums;
use App\Models\Enums\PlatbyImportEnums;

class FakturaRecreateCommandTest extends IntegrationTestCase{

	/** @var Orm */
	private $orm;

	/** @var IBankaGPCUploadCommand */
	private $facCmd;

	/** @var EventDispatcher */
	private $dispatcher;

	public function __construct()
	{
		$c = $this->getContainer();
		$this->orm = $c->getByType(Orm::class);
		$this->facCmd = $c->getByType(IBankaGPCUploadCommand::class);
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
		$file = DATA_DIR . '/banka/abo/20200106_42740393_DE_100104.GPC';

		$cmd = $this->facCmd->create();
		$cmd->setFile($file);
		$cmd->setInfo($info);
		$cmd->setLimit(PlatbyImportEnums::FILTER_NONE);

		$cmd->execute();

		Assert::true(true);
	}
}

(new FakturaRecreateCommandTest())->run();