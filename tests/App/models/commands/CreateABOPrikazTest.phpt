<?php

namespace Tests\App\Models\Commands;

require_once __DIR__ . '/../../../bootstrap.php';

use App\Extensions\App\StringStream;
use App\Models\Commands\ICreateABOPrikazCommand;
use App\Models\Events\DBBeginEvent;
use App\Models\Events\DBRollbackEvent;
use App\Models\Orm\Orm;
use App\Models\Strategies\Fakturace\LoadABODTOItemStrategy;
use Contributte\EventDispatcher\EventDispatcher;
use Tester\Assert;
use Tests\Utils\IntegrationTestCase;
use App\Models\Tables\SmlOmTable;

class CreateABOPrikazTest extends IntegrationTestCase{

	public static $FA_IDS = [
		996,
		993,
		992,
		988,
		985,
		975,
		696
	];

	/** @var Orm */
	private $orm;

	/** @var SmlOmTable */
	private $tbl;

	/** @var ICreateABOPrikazCommand */
	private $aboCmd;

	/** @var EventDispatcher */
	private $dispatcher;

	public function __construct()
	{
		$c = $this->getContainer();
		$this->orm = $c->getByType(Orm::class);
		$this->tbl = $c->getByType(SmlOmTable::class);
		$this->aboCmd = $c->getByType(ICreateABOPrikazCommand::class);
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

	public function testCreate()
	{
		$str = new StringStream();

		$stg = new LoadABODTOItemStrategy($this->orm, $this->tbl);
		$items = $stg->get(self::$FA_IDS);

		$cmd = $this->aboCmd->create();
		$cmd->setItems($items);
		$cmd->setStream($str);

		$cmd->execute();

		Assert::true(true);
	}
}

(new CreateABOPrikazTest())->run();