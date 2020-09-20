<?php

namespace Tests\App\Models\Commands;

require_once __DIR__ . '/../../../bootstrap.php';

use App\Models\Commands\IFakturaRecreateCommand;
use App\Models\Orm\Orm;
use Tester\Assert;
use Tests\Utils\IntegrationTestCase;
use Contributte\EventDispatcher\EventDispatcher;
use App\Models\Events\DBBeginEvent;
use App\Models\Events\DBRollbackEvent;

class FakturaRecreateCommandTest extends IntegrationTestCase{

	/** @var Orm */
	private $orm;

	/** @var IFakturaRecreateCommand */
	private $facCmd;

	/** @var EventDispatcher */
	private $dispatcher;

	const USER_ID = 1;

	const FAKTURA_ID = 1;

	public function __construct()
	{
		$c = $this->getContainer();
		$this->orm = $c->getService('nextras.orm.model');
		$this->facCmd = $c->getByType(IFakturaRecreateCommand::class);
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
		$cmd = $this->facCmd->create();
		$cmd->setId(self::FAKTURA_ID);
		$cmd->setUserId(self::USER_ID);

		$cmd->execute();

		Assert::true(true);
	}
}

(new FakturaRecreateCommandTest())->run();