<?php
namespace App\Console\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleHelpCommand extends Command{

	protected function configure(){
		$this->setName('app:help')->setDescription('Help');
	}

	protected function execute(InputInterface $input, OutputInterface $output){
		try{
			$output->writeLn('This should help');
			return 0; // zero return code means everything is ok
		}catch(\Exception $e){
			$output->writeLn('<error>' . $e->getMessage() . '</error>');
			return 1; // non-zero return code means error
		}
	}
}