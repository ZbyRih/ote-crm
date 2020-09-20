<?php

namespace App\Models\Strategies\Ote;

use App\Models\Entities\DecryptTemps;
use App\Models\Entities\CertificateEntity;
use React\Promise\Deferred;
use React\Promise\Promise;

class NelzeDesifrovatExcepion extends \Exception{
}

class EmailDecryptStrategy{

	/** @var CertificateEntity[] */
	private $certs;

	/** @var DecryptTemps */
	private $temps;

	public function setCerts(
		$certs)
	{
		$this->certs = $certs;
	}

	/**
	 * @param Deferred $deferred
	 * @return Promise
	 */
	public function proccess(
		Deferred $deferred)
	{
		return $deferred->promise()
			->then(
			function (
				$raw)
			{
				$this->temps = new DecryptTemps();
				file_put_contents($this->temps->raw, $raw);
				file_put_contents($this->temps->decrypted, '');
				$this->decrypt($this->temps);
			})
			->then(function ()
		{
			$this->unsign($this->temps);
		})
			->then(function ()
		{
			return $this->extract($this->temps);
		});
	}

	private function decrypt(
		DecryptTemps $temps)
	{
		foreach($this->certs as $c){
			if(openssl_pkcs7_decrypt($temps->raw, $temps->decrypted, $c->public, $c->private)){
				return true;
			}
		}

		throw new NelzeDesifrovatExcepion($this->getOpenSslErr());
	}

	private function unsign(
		DecryptTemps $temps)
	{
		$ver = openssl_pkcs7_verify($temps->decrypted, PKCS7_NOVERIFY, $temps->recivedCert);

		if($ver !== true){

			throw new NelzeDesifrovatExcepion($this->getOpenSslErr());
		}

		$ver = openssl_pkcs7_verify($temps->decrypted, PKCS7_NOVERIFY, $temps->recivedCert, array(), $temps->recivedCert, $temps->unsigned);

		if($ver !== true){
			throw new NelzeDesifrovatExcepion($this->getOpenSslErr());
		}

		return true;
	}

	private function extract(
		DecryptTemps $temps)
	{
		$raw = file_get_contents($temps->unsigned);
		$parts = explode("\r\n\r\n", $raw);

		if(count($parts) < 2){
			return $raw;
		}

		if(strpos($parts[0], 'Content-Transfer-Encoding: quoted-printable')){
			return quoted_printable_decode($parts[1]);
		}

		if(strpos($parts[0], 'Content-Transfer-Encoding: base64')){
			return base64_decode($parts[1]);
		}

		if(strpos($parts[0], 'Content-Transfer-Encoding: 7bit')){
			return $parts[1];
		}

		return $raw;
	}

	private function getOpenSslErr()
	{
		$es = [];
		while($e = openssl_error_string()){
			$es[] = $e;
		}
		return implode(', ', $es);
	}
}