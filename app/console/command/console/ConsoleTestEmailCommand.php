<?php
namespace App\Console\Console;

use App\Extensions\App\Logger;
use Nette\DI\Container;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class ConsoleTestEmailCommand extends Command{

	/** @var Logger @inject */
	public $logger;

	/** @var IMailer @inject */
	public $mailer;

	/** @var Container @inject */
	public $container;

	protected function configure()
	{
		$this->setName('app:test-email')
			->setDescription('Test poslání emailu')
			->setDefinition([
			new InputArgument('email', InputArgument::REQUIRED, 'email address')
		]);
	}

	protected function execute(
		InputInterface $input,
		OutputInterface $output)
	{
		try{

			$to = $input->getArgument('email');

			$opts = $this->container->parameters['mail']['from'];

			$m = new Message();

			$m->setFrom($opts['email'], $opts['name'])
				->setSubject('[' . $opts['name'] . '] Testovací email')
				->setHtmlBody(
				'
					<div>
						<h4>Toto je testovací email</h4>
						<p>Test odeslán z ' . $this->getApplication()
					->getName() . '</p>
					</div>
				')
				->addTo($to);

			$this->mailer->send($m);

			$output->writeLn('Test email OK');
			$this->logger->getSection('console')->log('Test email OK');
			return 0; // zero return code means everything is ok
		}catch(\Exception $e){
			$output->writeLn('<error>' . $e->getMessage() . '</error>');
			return 1; // non-zero return code means error
		}
	}
}