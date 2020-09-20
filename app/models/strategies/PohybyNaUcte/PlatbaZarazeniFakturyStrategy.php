<?php

namespace App\Models\Strategies;

use React\Promise\Deferred;
use App\Models\Orm\Orm;
use App\Models\Orm\Platby\PlatbaEntity;
use React\Promise\PromiseInterface;

class PlatbaZarazeniFakturyStrategy{

	/** @var Orm */
	private $orm;

	/**
	 * @param Orm $orm
	 */
	public function setOrm(
		$orm)
	{
		$this->orm = $orm;
	}

	/**
	 * @return PromiseInterface
	 */
	public function zaradit(
		PlatbaEntity $p,
		$fas)
	{
		$deferred = new Deferred();
		$date = $p->when->format('d.m. Y');

		if(count($fas) > 1){
			$deferred->reject(sprintf('Pro platbu s vs: %s dne %s nalezeno víc faktur.', $p->vs, $date));
			return $deferred->promise();
		}

		$fa = reset($fas);

		if(ceil($p->platba) != ceil($fa['preplatek'])){
			$deferred->reject(sprintf('Pro platbu s vs: %s dne %s %s CZK nesedí přeplatek faktury %s CZK.', $p->vs, $date, $p->platba, $fa['preplatek']));
			return $deferred->promise();
		}

		$kli = $this->orm->klients->getById($fa['klient_id']);

		// 		if($p->fromCu != $kli->klientDetailId->cu){
// 			$deferred->reject(sprintf('Pro platbu s vs: %s faktury dne %s nesedí č.ú. odběratele.', $p->vs, $date));
// 			return $deferred->promise();
// 		}

		$ret = new ZarazeniPlatbyDTO();
		$ret->p = $p;
		$ret->msg = sprintf('Platba vs: %s dne %s zařazena do faktur %s.', $p->vs, $date, $kli->klientDetailId->getSalutation());
		$ret->klientId = $fa['klient_id'];
		$ret->omId = $fa['om_id'];

		$deferred->resolve($ret);
		return $deferred->promise();
	}
}