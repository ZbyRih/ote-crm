<?php
use Cake\Collection\Collection;

class PlatbyParZalohy{

	/** @var array */
	private $inZalohy;

	/** @var array */
	private $inPlatby;

	private $presne;

	private $links;

	private $year = NULL;

	private $vs = null;

	function __construct(
		$year)
	{
		$this->year = $year;
	}

	public function load(
		$vs)
	{
		AdminLogDBAccess::Stop();

		$this->vs = $vs;

		$this->inZalohy = null;
		$this->inPlatby = null;
		$this->presne = true;
		$this->links = [];

		$this->loadZalohy((new MZalohy())->getUnlink($this->year, $vs));
		$this->loadPlatby((new MPlatby())->getUnlinkVS($this->year, $vs));

		// 		dd($this->inZalohy);
// 		dd($this->inPlatby);

		AdminLogDBAccess::Start();
		return $this;
	}

	/**
	 * @param Collection $in
	 * @return PlatbyParZalohy
	 */
	public function loadZalohy(
		$in)
	{
		$this->inZalohy = $in->map(
			function (
				$v,
				$k){
				$z = $v['Zalohy'];
				$z['vs'] = trim(ltrim(trim($z['vs']), '0'));
				$z['vyse'] = (float) $z['vyse'];
				$z['uhrazeno'] = (float) $z['uhrazeno'];
				return $z;
			})
			->filter(function (
			$v){
			return !empty($v['vs']);
		})
			->indexBy('zaloha_id')
			->toArray();

		return $this;
	}

	/**
	 * @param Collection $in
	 * @return PlatbyParZalohy
	 */
	public function loadPlatby(
		$in)
	{
		$this->inPlatby = $in->map(
			function (
				$v,
				$k){
				$p = $v['Platba'];
				$p['vs'] = trim(ltrim(trim($p['vs']), '0'));
				$p['platba'] = (float) $p['platba'];
				return $p;
			})
			->filter(function (
			$v){
			return !empty($v['vs']);
		})
			->indexBy('platba_id')
			->toArray();

		return $this;
	}

	public function projit()
	{
		if(empty($this->inZalohy) && empty($this->inPlatby)){
			return $this;
		}

		$z = reset($this->inZalohy);
		$p = reset($this->inPlatby);
		$platba = 0;
		$zaloha = $z['vyse'] - $z['uhrazeno'];

		while($z && $p){
			$uhr = false;

			if($platba == 0){
				$platba = $p['platba'];
			}

			if($platba == $zaloha){
				$this->links[] = [
					'zaloha_id' => $z['zaloha_id'],
					'platba_id' => $p['platba_id'],
					'suma' => $zaloha,
					'e' => [
						'p' => $p,
						'z' => $z
					]
				];
				$uhr = true;
				$platba = 0;
				$zaloha = 0;
			}else if($platba > $zaloha){
				$this->links[] = [
					'zaloha_id' => $z['zaloha_id'],
					'platba_id' => $p['platba_id'],
					'suma' => $zaloha,
					'e' => [
						'p' => $p,
						'z' => $z
					]
				];
				$this->presne = false;
				$uhr = true;
				$platba -= $zaloha;
				$zaloha = 0;
			}else if($platba < $zaloha){
				$this->links[] = [
					'zaloha_id' => $z['zaloha_id'],
					'platba_id' => $p['platba_id'],
					'suma' => $platba,
					'e' => [
						'p' => $p,
						'z' => $z
					]
				];
				$this->presne = false;
				$zaloha -= $platba;
				$platba = 0;
			}

			if($uhr){
				$z = next($this->inZalohy);
				$zaloha = $z['vyse'] - $z['uhrazeno'];
			}

			if($platba == 0){
				$p = next($this->inPlatby);
			}
		}

		return $this;
	}

	public function getResultList()
	{
		if(empty($this->links)){
			return [];
		}

		$klient_ids = collection($this->inZalohy)->extract('klient_id')->toList();
		$klient_ids = array_unique($klient_ids);

		$Contacts = new MContacts();
		if($cons = $Contacts->FindAll([
			'klient_id' => array_values($klient_ids)
		])){
			$cons = MArray::MapModelItemToKey($cons, 'Contacts', 'klient_id');
		}

		$res = [];

		$links = collection($this->links);

		$zals = $links->extract('e.z')
			->indexBy('zaloha_id')
			->groupBy('vs')
			->toArray();

		$plas = $links->extract('e.p')
			->indexBy('platba_id')
			->groupBy('vs')
			->toArray();

		$links->each(
			function (
				$v,
				$k) use (
			&$res,
			$cons,
			$zals,
			$plas){

				$vs = $v['e']['z']['vs'];
				$z = $v['e']['z'];
				$p = $v['e']['p'];

				if(!isset($res[$vs])){
					$res[$vs] = [
						'odb' => MContactDetails::sname($cons[$z['klient_id']]['ContactDetails']),
						'vs' => $vs,
						'vyse' => 0,
						'uhrazeno' => 0,
						'stav' => '',
						'platby' => 0,
						'sedi' => (int) $this->presne,
						'color' => 2
					];
					if(isset($zals[$vs])){
						$zs = collection($zals[$vs]);
						$res[$vs]['vyse'] = $zs->sumOf('vyse') - $zs->sumOf('uhrazeno');
					}
					if(isset($plas[$vs])){
						$ps = collection($plas[$vs]);
						$res[$vs]['platby'] = $ps->sumOf('platba');
					}
				}

				$res[$vs]['uhrazeno'] += $v['suma'];
			});

		return collection($res)->map(
			function (
				$v){
				if($v['vyse'] == $v['uhrazeno']){
					$v['stav'] = '=';
					$v['color'] = 1;
				}else{
					$v['color'] = 2;
					if($v['uhrazeno'] < $v['vyse']){
						$v['stav'] = '<';
					}else{
						$v['stav'] = '>';
					}
				}
				$v['stav'] = '<b style="font-size: 14px">' . $v['stav'] . '</b>';
				return $v;
			})->toArray();
	}

	public function getMidResult()
	{
		if(empty($this->links)){
			return [];
		}

		$zals = [];
		$plas = [];

		collection($this->links)->each(
			function (
				$v,
				$k) use (
			&$zals,
			&$plas){
				if(isset($zals[$v['zaloha_id']])){
					$zals[$v['zaloha_id']]['uhrazeno'] += $v['suma'];
				}else{
					$z = $v['e']['z'];

					$z['d'] = OBE_DateTime::getDBToDate($z['od']);
					$z['od'] = OBE_DateTime::convertFromDB($z['od']);

					$zals[$v['zaloha_id']] = $z;
				}

				if(!isset($plas[$v['platba_id']])){
					$p = $v['e']['p'];
					$p['d'] = OBE_DateTime::getDBToDate($p['when']);
					$p['p_od'] = OBE_DateTime::convertFromDB($p['when']);
					$p['p_vs'] = $this->vs;

					unset($p['vs']);
					unset($p['preplatek']);

					$plas[$v['platba_id']] = $p;
				}
			});

		return array_merge($zals, $plas);
	}

	public function save()
	{
		AdminLogDBAccess::Stop();
		$Links = new MPVParFZ();

		$zalohy_vs = array_unique(collection($this->links)->extract('e.z.vs')->toList());
		$zalohy_count = count(array_unique(collection($this->links)->extract('zaloha_id')->toList()));
		$platby_count = count(array_unique(collection($this->links)->extract('platba_id')->toList()));

		foreach($this->links as $l){
			$l['dne'] = $l['e']['p']['when'];
			unset($l['e']);
			$s = [
				'PVParFZ' => $l
			];
			$Links->Save($s);
		}

		$zalohy_vs = array_unique($zalohy_vs);

		AdminApp::$mainModule->activityLog('Spárováno',
			'Spárováno: ' . $platby_count . ' plateb|Připsány do: ' . $zalohy_count . ' záloh (' . implode(',', $zalohy_vs) . ')', null, 'info');

		AdminLogDBAccess::Start();
	}
}