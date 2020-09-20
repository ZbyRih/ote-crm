<?php

namespace Tests\App\Modules;

require_once __DIR__ . '/../../bootstrap.php';

use Nette\Application\Request;
use Tester\Assert;
use Tester\DomQuery;
use Tests\Utils\IntegrationTestCase;
use Tests\Utils\TPresenterMock;

class PlatbyTest extends IntegrationTestCase{

	use TPresenterMock;

	const USER_ID = 1;

	public function __construct()
	{
		$this->init($this->getContainer());
	}

	public function testPlatbyDefault()
	{
		$req = new Request('Platby:Default', 'GET', [
			'action' => 'default'
		]);

		$res = $this->runPresenter($req, self::USER_ID);

		Assert::type('Nette\Application\Responses\TextResponse', $res);

		$html = (string) $res->getSource();
	}

	public function testPlatbyUpload()
	{
		$req = new Request('Platby:Upload', 'GET', [
			'action' => 'default'
		]);

		$res = $this->runPresenter($req, self::USER_ID);

		Assert::type('Nette\Application\Responses\TextResponse', $res);

		$dom = DomQuery::fromHtml($res->getSource());

		Assert::true($dom->has('input[type="file"]'));
	}
}

(new PlatbyTest())->run();