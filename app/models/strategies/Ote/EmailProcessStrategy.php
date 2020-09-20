<?php

namespace App\Models\Strategies\Ote;

use App\Models\Services\ImapClientService;
use App\Models\Entities\CertificateEntity;
use App\Models\Orm\OteMessages\OteMessageEntity;
use Nette\Utils\FileSystem;
use React\Promise\Deferred;
use App\Models\Repositories\OTEDirsParameters;

class EmailProcessStrategy{

	/** @var CertificateEntity[] */
	private $certs;

	/** @var OTEDirsParameters  */
	private $dirs;

	public function __construct()
	{
	}

	public function setCerts(
		$certs)
	{
		$this->certs = $certs;
	}

	/**
	 * @param OTEDirsParameters $dirs
	 */
	public function setDirs(
		OTEDirsParameters $dirs)
	{
		$this->dirs = $dirs;
	}

	public function process(
		$mId,
		$year,
		ImapClientService $imap)
	{
		$m = $imap->getMail($mId);
		$raw = $imap->getRaw($mId);

		$entity = new OteMessageEntity();
		$entity->received = $m->date;
		$entity->msgUid = trim($m->messageId, '<>');

		$deferred = new Deferred();

		$str = new EmailDecryptStrategy();
		$str->setCerts($this->certs);

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
				OteXmlDTO $xml) use (
			$entity)
			{
				if(!$xml){
					return;
				}

				$entity->oteId = $xml->oteId;
				$entity->oteKod = $xml->oteKod;

				$this->saveXml($this->dirs->xmlMessages, $xml->oteKod, $xml->oteId, $xml->raw);
			})
			->otherwise(
			function (
				$e) use (
			$entity)
			{
				$entity->decrypted = false;
				$entity->processed = false;

				if($e instanceof NelzeDesifrovatExcepion){
				}

				if($e instanceof NeniValidniXml){
					$entity->decrypted = true;
				}
			})
			->done();

		$deferred->resolve($raw);

		return $entity;
	}

	private function saveXml(
		$root,
		$kod,
		$id,
		$xml)
	{
		$dir = sprintf('%s/%s', $this->dirs->xmlMessages, strtolower($kod));
		FileSystem::createDir($dir);
		file_put_contents(sprintf('%s/%s.xml', $dir, $id), $xml);
	}
}