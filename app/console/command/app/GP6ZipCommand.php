<?php
namespace App\Console\App;

use Nette\DI\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ZipStream\ZipStream;
use App\Models\Selections\OteGP6FakturovaneByYearSelection;
use App\Models\Resources\OteXmlFile;

class GP6ZipCommand extends Command{

	/** @var Container @inject */
	public $container;

	private $cert1;

	private $cert2;

	protected function configure()
	{
		$this->setName('app:gp6zip')->setDescription('gp6 to zip');
	}

	protected function execute(
		InputInterface $input,
		OutputInterface $output)
	{
		try{
			$year = 2019;

			ob_start();

			$output->writeLn('Packing');

			$sel = $this->container->getByType(OteGP6FakturovaneByYearSelection::class);

			$gp6s = $sel->get($year);

			$zip = new ZipStream();

			$errs = 0;
			$files = 0;

			foreach($gp6s->fetchAll() as $g){
				try{

					$file = new OteXmlFile($g->received->format('Y'), $g->ote_kod, $g->ote_id);
					if(file_exists($file)){
						$files++;
						$res = new \SplFileObject($file);
						$zip->addFile($res->getFilename(), $res->fread($res->getSize()));
					}else{
						$errs++;
						$output->writeLn('<error>file not exists: ' . $file . '</error>');
					}
				}catch(\Exception $e){
					$errs++;
					$output->writeLn('<error>' . $e->getMessage() . '</error>');
					continue;
				}
			}

			$zip->finish();
			$cnt = ob_get_clean();
			file_put_contents('gp6zip.zip', $cnt);

			$output->writeLn('Errors: ' . $errs);
			$output->writeLn('Files: ' . $files);
			$output->writeLn('Done.');
			return 0; // zero return code means everything is ok
		}catch(\Exception $e){
			$output->writeLn('<error>' . $e->getMessage() . '</error>');
			return 1; // non-zero return code means error
		}
	}
}