<?php

namespace App\Models\Commands;

use App\Extensions\Interfaces\ICommand;
use App\Models\Utils\CertException;
use App\Models\Utils\CertUtils;
use App\Models\Orm\Orm;
use App\Models\Resources\ConfigFileResource;
use Contributte\EventDispatcher\EventDispatcher;
use Nette\FileNotFoundException;
use App\Models\Events\FlashMessageEvent;
use App\Models\Orm\Cert\CertEntity;
use App\Models\Services\NowService;

class ChangedCertificateCommand implements ICommand{

	/** @var Orm */
	private $orm;

	/** @var NowService */
	private $serNow;

	/** @var EventDispatcher */
	private $dispatcher;

	public function __construct(
		Orm $orm,
		NowService $serNow,
		EventDispatcher $dispatcher)
	{
		$this->orm = $orm;
		$this->serNow = $serNow;
		$this->dispatcher = $dispatcher;
	}

	public function execute()
	{
		$file = $this->orm->settings->findBy([
			'key' => 'ote_cert_file'
		])->fetch();

		$validTo = $this->orm->settings->findBy([
			'key' => 'ote_cert_valid'
		])->fetch();

		try{
			$cf = new ConfigFileResource($file->value);
		}catch(FileNotFoundException $e){
			$this->dispatcher->dispatch(...FlashMessageEvent::warningDisp('Soubor s certifikátem nenalezen.'));
			return;
		}

		$cnt = $cf->getContent();
		$hash = md5($cnt);

		$utl = new CertUtils($cnt);

		try{
			$utl->test();

			if($to = $utl->getTo()){
				$validTo->value = $to->format('Y-m-d');
			}
		}catch(CertException $e){
			$validTo->value = null;

			$this->dispatcher->dispatch(...FlashMessageEvent::warningDisp(sprintf('Došlo k chybě při validaci certifikátu `%s`.', $e->getMessage())));
		}

		$this->orm->persist($validTo);

		if(!$validTo){
			return;
		}

		$any = $this->orm->certs->findBy([
			'hash' => $hash
		])->fetch();

		if($any){
			return;
		}

		$cert = new CertEntity();
		$cert->file = (string) $cf;
		$cert->hash = $hash;
		$cert->created = $this->serNow->get();
		$cert->validTo = $to;

		$this->orm->persist($cert);
	}
}