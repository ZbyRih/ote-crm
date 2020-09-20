<?php
namespace App\Console\Console;

use App\Extensions\App\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleTestCommand extends Command{

	/** @var Logger @inject */
	public $logger;

	protected function configure()
	{
		$this->setName('app:test')->setDescription('Test console');
	}

	protected function execute(
		InputInterface $input,
		OutputInterface $output)
	{
		try{
			$output->writeLn('Test OK');
			$this->logger->getSection('console')->log('Test OK');
			return 0; // zero return code means everything is ok
		}catch(\Exception $e){
			$output->writeLn('<error>' . $e->getMessage() . '</error>');
			return 1; // non-zero return code means error
		}
	}
}