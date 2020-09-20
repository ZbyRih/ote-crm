<?php

namespace App\Console\App;

use App\Models\Repositories\SettingsRepository;
use App\Models\Strategies\ExtractImapSettingsStrategy;
use App\Models\Services\ImapClientService;
use App\Models\Entities\CertificateEntity;
use App\Models\Strategies\Ote\EmailDecryptStrategy;
use App\Models\Strategies\Ote\EmailToXmlStrategy;
use App\Models\Strategies\Ote\OteXmlDTO;
use App\Models\Strategies\OTEDirsStrategy;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Nette\Utils\FileSystem;
use Nette\DI\Container;
use App\Models\Repositories\ParametersRepository;
use React\Promise\Deferred;

class OteDownloadCommand extends Command{

	/** @var Container @inject */
	public $container;

	protected function configure()
	{
		$this->setName('app:otedown')->setDescription('download ote');
	}

	protected function execute(
		InputInterface $input,
		OutputInterface $output)
	{
		$output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);

		$repSettings = $this->container->getByType(SettingsRepository::class);
		$repParams = $this->container->getByType(ParametersRepository::class);

		$extr = new ExtractImapSettingsStrategy();
		$settings = $extr->get($repSettings, SettingsRepository::BOX_OTE);

		$imap = new ImapClientService();
		$imap->connect($settings);

		$output->writeln('imap open');

		$root = $settings->server . $settings->folder;

		$imap->switchFolder($root);

		$boxes = [
			2019 => $root . '.Precteno.2019',
			2020 => $root . '.Precteno.2020'
		];

		$certs = [
			new CertificateEntity(file_get_contents(APP_DIR . 'config/files/xxx.pem'), ''),
			new CertificateEntity(file_get_contents(APP_DIR . 'config/files/xxx_private.pem'), '')
		];

		$str = new EmailDecryptStrategy();
		$str->setCerts($certs);

		foreach($boxes as $year => $b){

			$output->writeln('year: ' . $year);

			$strDirs = new OTEDirsStrategy();
			$strDirs->setParams($repParams);
			$dirs = $strDirs->get($year);

			$output->writeln('folder: ' . $b);
			$imap->switchFolder($b);
			$mailIds = $imap->getMailIds();

			$output->writeln('mails: ' . count($mailIds));

			$count = 0;

			foreach($mailIds as $mId){
				$count++;

				$m = $imap->getMail($mId);
				$raw = $imap->getRaw($mId);

				$deferred = new Deferred();

				$str->proccess($deferred)
					->then(function (
					$mailPart) use (
				$m)
				{
					$str = new EmailToXmlStrategy();
					return $str->convert($mailPart, $m->subject);
				})
					->then(
					function (
						OteXmlDTO $xml = null) use (
					$dirs)
					{
						if(!$xml){
							return;
						}

						$dir = sprintf('%s/%s', $dirs->xmlMessages, strtolower($xml->oteKod));
						FileSystem::createDir($dir);
						file_put_contents(sprintf('%s/%s.xml', $dir, $xml->oteId), $xml->raw);
					})
					->otherwise(
					function (
						$e) use (
					$output)
					{
						$name = (new \ReflectionClass($e))->getShortName();
						$output->writeln(sprintf('<error>%s (%s)</error>', $name, $e->getMessage()));
					})
					->done();

				$output->writeln($m->subject);
				$deferred->resolve($raw);

				if($count % 100 == 0){
					$output->writeln($count / 100);
					continue;
				}

				if($count % 1000 == 0){
					$output->writeln($count / 1000);
				}
			}
		}

		$imap->close();

		return 0; // zero return code means everything is ok
	}
}