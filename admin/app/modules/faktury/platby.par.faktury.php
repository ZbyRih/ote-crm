<?php
use Cake\Collection\Collection;

class PlatbyParZalohyException extends Exception{
}

class PlatbyParFaktury{

	private $year = NULL;

	private $pls;

	private $fs;

	private $result;

	private $cache = [
		'k' => [],
		'om' => [],
		'cu' => []
	];

	public function __construct(
		$year)
	{
		$this->year = $year;
	}

	public function load()
	{
		$P = new MPlatby();
		$F = new MFaktury();

		$pls = $P->FindAll(
			[
				'deprecated' => 0,
				'YEAR(when)' => [
					$this->year + 0,
					$this->year + 1
				],
				'!isLinkedPlatba(Platba.platba, Platba.platba_id) IS NOT TRUE'
			], [
				'platba_id',
				'platba',
				'when',
				'vs',
				'from_cu'
			], [
				'when' => 'ASC'
			]);

		$fs = $F->FindAll(
			[
				'storno' => 0,
				'!deleted IS NULL',
				'YEAR(vystaveno)' => [
					$this->year + 0,
					$this->year + 1
				],
				'preplatek > 0',
				'!getFakUhrDne(Faktura.preplatek, Faktura.id) IS NULL'
			], [
				'id',
				'cis',
				'preplatek',
				'splatnost',
				'om_id',
				'klient_id'
			], [
				'cis' => 'ASC'
			]);

		if($pls == null || $fs == null){
			throw new PlatbyParZalohyException('Nebyli nalezeny žádné faktury nebo platby k fakturám');
		}

		$this->fs = (new Collection($fs))->sortBy('Faktura.cis', SORT_ASC)->indexBy('Faktura.cis');
		$this->pls = (new Collection($pls))->map(function (
			$v,
			$k)
		{
			$v['Platba']['vs'] = trim(ltrim($v['Platba']['vs'], '0'));
			return $v['Platba'];
		})
			->filter(function (
			$v,
			$k)
		{
			return !empty($v['vs']);
		})
			->sortBy('vs', SORT_ASC);

		return $this;
	}

	public function spojit()
	{
		$this->fs->each(
			function (
				$v,
				$k)
			{
				$match = $this->pls->match([
					'vs' => trim($v['Faktura']['cis'])
				]);

				if(!$match->isEmpty()){

					$this->cache['k'][] = $v['Faktura']['klient_id'];
					$this->cache['om'][] = $v['Faktura']['om_id'];

					$this->cache['cu'][] = $match->extract('cu');

					$this->result[$v['Faktura']['id']] = [
						'f' => $v['Faktura'],
						'p' => $match
					];
				}
			});
		return $this;
	}

	public function getResult()
	{
		$O = new MOdberatel();
		$OM = new MOdberMist();

		$kliIds = array_unique($this->cache['k']);
		$omIds = array_unique($this->cache['om']);

		$klis = [];
		if(!empty($kliIds)){
			$klis = new Collection($O->FindAll([
				'klient_id' => $kliIds
			]));
		}

		$oms = [];
		if(!empty($omIds)){
			$oms = new Collection($OM->FindAll([
				'odber_mist_id' => $omIds
			]));
		}

		$cks = collection($klis)->map(
			function (
				$v)
			{
				return [
					'id' => $v['Contacts']['klient_id'],
					'name' => MContactDetails::sname($v['ContactDetails']),
					'cu' => $v['ContactDetails']['cu']
				];
			})->indexBy('id');

		$cu = $cks->combine('id', 'cu')->toArray();
		$klis = $cks->toArray();

		$oms = collection($oms)->map(function (
			$v)
		{
			return [
				'id' => $v['OdberMist']['odber_mist_id'],
				'name' => MOdberMist::identity($v)
			];
		})
			->indexBy('id')
			->toArray();

		$res = [];

		(new Collection($this->result ?: []))->each(
			function (
				$v,
				$k) use (
			&$res,
			$klis,
			$oms,
			$cu)
			{
				$v['p']->each(
					function (
						$vv,
						$kk) use (
					&$res,
					$v,
					$k,
					$klis,
					$oms,
					$cu)
					{
						$id = 'p_' . $vv['platba_id'] . '_f_' . $k;
						$res[$id] = [
							'id' => $id,
							'odb' => $klis[$v['f']['klient_id']]['name'],
							'om' => ($v['f']['om_id']) ? $oms[$v['f']['om_id']]['name'] : '',
							'vs' => $v['f']['cis'],
							'vyse' => $v['f']['preplatek'],
							'splatnost' => OBE_DateTime::getDBToDate($v['f']['splatnost'])->format('j.n. Y'),
							'when' => OBE_DateTime::getDBToDate($vv['when'])->format('j.n. Y'),
							'platba' => $vv['platba'],
							'cu' => $vv['from_cu'],
							'klient' => '',
							'preplatek' => (int) ($v['f']['preplatek'] == $vv['platba'])
						];
					});
			});

		return $res;
	}
}