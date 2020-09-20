<?php
namespace App\Console\Console;

use Nette\DI\Container;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleCleanCommand extends Command{

	/** @var Container @inject */
	public $container;

	protected function configure(){
		$this->setName('app:clean')->setDescription('Smaže keše');
	}

	protected function execute(InputInterface $input, OutputInterface $output){
		try{

			$ps = $this->container->getParameters();

			$output->writeLn('Deleting:');
			foreach(Finder::findDirectories('*cache*')->in($ps['tempDir']) as $k => $d){
				$output->writeLn("\t- " . $d . '/');
				FileSystem::delete($d . '/');
			}

			$output->writeLn('Done.');
			return 0; // zero return code means everything is ok
		}catch(\Exception $e){
			$output->writeLn('<error>' . $e->getMessage() . '</error>');
			return 1; // non-zero return code means error
		}
	}
}