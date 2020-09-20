<?php

namespace App\Models\Strategies;

use App\Models\Orm\Orm;
use App\Models\Orm\Platby\PlatbaEntity;
use React\Promise\PromiseInterface;
use React\Promise\Deferred;

class PlatbaZarazeniZalohyStrategy{

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
	 * @param PlatbaEntity $p
	 * @param [] $zals
	 * @return PromiseInterface
	 */
	public function zaradit(
		PlatbaEntity $p,
		$zals)
	{
		$deferred = new Deferred();
		$date = $p->when->format('d.m. Y');

		if(count($zals) < 2){
			$za = reset($zals);

			$kli = $this->orm->klients->getById($za['klient_id']);
// 			if($p->fromCu != $kli->klientDetailId->cu){
// 				$deferred->reject(sprintf('Pro platbu s vs: %s dne %s nesedí č.ú. odběratele.', $p->vs, $date));
// 				return $deferred->promise();
// 			}

			$ret = new ZarazeniPlatbyDTO();
			$ret->p = $p;
			$ret->msg = sprintf('Platba vs: %s dne %s zařazena do záloh %s.', $p->vs, $date, $kli->klientDetailId->getSalutation());
			$ret->klientId = $za['klient_id'];
			$ret->omId = $za['fak_skup_id'] ? null : $za['odber_mist_id'];

			$deferred->resolve($ret);
			return $deferred->promise();
		}

		if(count($zals) > 1){
			$faks = collection($zals)->extract('fak_skup_id')->toList();
			$faks = array_unique($faks);

			if(count($faks) > 1){
				$deferred->reject(sprintf('Pro platbu s vs: %s zálohy dne %s není jednoznačná fakturační skupina.', $p->vs, $date));
				return $deferred->promise();
			}

			$fakSkupId = null;
			if(count($faks) == 1){
				$fakSkupId = reset($faks);
			}

			$za = reset($zals);

			$kli = $this->orm->klients->getById($za['klient_id']);
			if($p->fromCu != $kli->klientDetailId->cu){
				$deferred->reject(sprintf('Pro platbu s vs: %s zálohy dne %s nesedí č.ú. odběratele.', $p->vs, $date));
				return $deferred->promise();
			}

			$ret = new ZarazeniPlatbyDTO();
			$ret->p = $p;
			$ret->msg = sprintf('Platba vs: %s dne %s zařazena do záloh %s.', $p->vs, $date, $kli->klientDetailId->getSalutation());
			$ret->klientId = $za['klient_id'];
			$ret->fakSkupId = $fakSkupId;
			$ret->omId = null;

			$deferred->resolve($ret);
			return $deferred->promise();
		}
	}
}