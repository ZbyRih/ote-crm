<?php
namespace App\Console\Console;

use Nette\DI\Container;
use Nette\Utils\Finder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleBuildCommand extends Command{

	/** @var Container @inject */
	public $container;

	protected function configure(){
		$this->setName('app:build')->setDescription('Zkompiluje vsechny sablony');
	}

	protected function execute(InputInterface $input, OutputInterface $output){
		try{

			$ps = $this->container->getParameters();

			$presenter = $this->getHelper('presenter')->getPresenter();

			$template = $presenter->getTemplateFactory()
				->createTemplate()
				->getLatte();

			$output->writeLn('Building:');

			$output->writeLn(' app-files:');
			foreach(Finder::findFiles('*.latte')->from($ps['appDir']) as $k => $f){
				$output->writeLn("\t- " . $f->getRealPath());
				$template->warmupCache($f->getRealPath());
			}

			$output->writeLn(' libs-files:');
			foreach(Finder::findFiles('*.latte')->from(LIBS_DIR) as $k => $f){
				$output->writeLn("\t- " . $f->getRealPath());
				$template->warmupCache($f->getRealPath());
			}

			$output->writeLn('Done.');
			return 0; // zero return code means everything is ok
		}catch(\Exception $e){
			$output->writeLn('<error>' . $e->getMessage() . '</error>');
			return 1; // non-zero return code means error
		}
	}
}