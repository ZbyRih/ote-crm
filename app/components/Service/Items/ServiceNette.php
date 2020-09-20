<?php
namespace App\Components\Service;

use App\Extensions\Utils\DateTime;
use Nette\Framework;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Nette\DI\Container;

class ServiceNette extends ServicePanel{

	private $container;

	private $tempDir;

	public function __construct(Container $container){
		$this->container = $container;

		$pars = $this->container->getParameters();

		$this->tempDir = $pars['tempDir'];

		$journal = $this->tempDir . '/cache/journal.s3db';

		$items = [
			'verze' => Framework::VERSION,
			'cache journal time' => file_exists($journal) ? DateTime::from(filectime($journal))->format('j.n. Y H:i:s') : 'neexistuje'
		];

		$actions = [
			'clearCache' => [
				'kind' => 'primary',
				'color' => 'red',
				'title' => 'Smazat Cache'
			],
			'clearLatte' => [
				'kind' => 'primary',
				'color' => 'red',
				'title' => 'Smazat Latte cache'
			]
		];

		parent::__construct('Nette', $items, $actions);
	}

	public function handleClearCache(){
		foreach(Finder::findDirectories('*cache*')->in($this->tempDir) as $k => $d){
			FileSystem::delete($d);
		}

		$this->presenter->flashSuccess('Cache smazána.');
		$this->presenter->redirect('default');
	}

	public function handleClearLatte(){
		foreach(Finder::findFiles('*')->in($this->tempDir . '/cache/latte') as $f){
			FileSystem::delete($f);
		}

		$this->translator = null;

		$this->presenter->flashSuccess('Cache latte smazána.');
		$this->presenter->redirect('default');
	}
}