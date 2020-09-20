<?php
namespace App\Console\Console;

use Nette\DI\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Nette\Neon\Neon;

class ConsoleUpdateSchemeCommand extends Command{

	/** @var Container @inject */
	public $container;

	protected function configure(){
		$this->setName('app:update')->setDescription('Zkontroluje show tables a vytvoří a doplní cybějící do config.tables.neon');
	}

	protected function execute(InputInterface $input, OutputInterface $output){
		try{

			$tablesConfig = APP_DIR . '/config/app/services/config.tabels.neon';

			$ps = $this->container->getParameters();

			/** @var \Nette\Database\Context $db */
			$db = $this->container->getByType(\Nette\Database\Context::class);

			$tbls = $db->query('SHOW TABLES');

			$neon = Neon::decode(file_get_contents($tablesConfig));

			// musim projit servisy a zjistit na jaky tabulky jsou navazany protected $table properta tridy
			// z toho zjistim ktera tabulka nema tridu
			// tridu vytvorim ze sablony
			// do neonu se prida nazev tridy

			file_put_contents($tablesConfig, Neon::encode($neon, Neon::BLOCK));

			$output->writeLn('Done.');
			return 0; // zero return code means everything is ok
		}catch(\Exception $e){
			$output->writeLn('<error>' . $e->getMessage() . '</error>');
			return 1; // non-zero return code means error
		}
	}
}