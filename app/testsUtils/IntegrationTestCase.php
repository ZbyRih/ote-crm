<?php

namespace Tests\Utils;

use Nette\DI\Container;
use Tester\TestCase;
use App\Booting;
use Contributte\EventDispatcher\EventDispatcher;

abstract class IntegrationTestCase extends TestCase{

	/** @var Container */
	private $container;

	protected function getContainer()
	{
		if($this->container === null){
			$this->container = $this->createContainer();
			$this->removeListeners($this->container);
		}

		return $this->container;
	}

	private function createContainer()
	{
		return Booting::boot(TestsConfig::$config)->createContainer();
	}

	private function removeListeners(
		Container $c)
	{
		$d = $c->getByType(EventDispatcher::class);

		foreach(TestsConfig::$disableEvents as $e){
			foreach($d->getListeners($e::NAME) as $l){
				$d->removeListener($e::NAME, $l);
			}
		}
	}
}