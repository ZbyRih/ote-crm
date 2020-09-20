<?php

namespace Tests\App\Modules\Settings\Components;

require_once __DIR__ . '/../../../../bootstrap.php';

use App\Modules\Settings\IComponentEditForm;
use Tester\Assert;
use Tests\Utils\IntegrationTestCase;
use Tests\Utils\TPresenterMock;
use App\Modules\Settings\Components\SettingsEditForm;

class SettingsEditFormTest extends IntegrationTestCase{

	use TPresenterMock;

	/** @var IComponentEditForm */
	private $facEdit;

	/** @var SettingsEditForm */
	private $com;

	public function __construct()
	{
		$this->init($this->getContainer());
		$this->facEdit = $this->getContainer()->getByType(IComponentEditForm::class);
	}

	public function setUp()
	{
		$this->buildMock();
		$this->com = $c = $this->facEdit->create();
		$this->com->setGroup('main');
		$this->addComponent($c, 'com');
	}

	public function tearDown()
	{
	}

	public function testCreate()
	{
		$html = $this->catchComponentRender($this->com);

		Assert::true(!empty($html));
	}
}

(new SettingsEditFormTest())->run();