<?php
use App\Models\Orm\Orm;
use App\Models\Selections\PlatbaSelection;
use App\Models\Selections\ZalohaSelection;
use Cake\Collection\Collection;
use App\Models\Strategies\IZalohyDoFakturyStrategy;
use App\Models\Selections\SpotrebaByOmYearSelection;
use App\Models\Tables\SpotrebaTable;
use App\Models\Tables\FakturaTable;

class FakturaException extends Exception{
}

class OTEFaktura{

	public $params = [
		'dph' => 0,
		'dphKoef' => 0,
		'dnzpSazba' => 0
	];

	public $cislo = null;

	public $dzp = null;

	public $type = '';

	public $datumVystaveni = null;

	public $datumSplatnosti = null;

	public $splatnost = 14;

	/**
	 * @var DateTime
	 */
	public $from = null;

	/**
	 * @var DateTime
	 */
	public $to = null;

	public $omId = null;

	public $om = null;

	public $klientId = null;

	public $klient = null;

	public $fakSkup = null;

	public $cmwh = 0;

	public $danZP = 0;

	public $total = 0;

	public $totalDph = 0;

	public $gs = [];

	public $zalohy = null;

	public $distribuce = [];

	public $ote = [];

	public $head = [];

	public $history = [];

	public $exts = [
		'title' => null,
		'z' => 0,
		'd' => 0,
		'c' => 0
	];

	public $v = [
		'ccd_z' => 0,
		'ccd_d' => 0,
		'ccd_c' => 0,
		'dzp_z' => 0,
		'dzp_d' => 0,
		'dzp_c' => 0,
		'cco_z' => 0,
		'cco_d' => 0,
		'cco_c' => 0,
		'cpm_z' => 0,
		'cpm_d' => 0,
		'cpm_c' => 0,
		'zz_z' => 0,
		'zz_d' => 0,
		'zz_c' => 0,
		'c_z' => 0,
		'c_d' => 0,
		'c_c' => 0
	];

	public $html = null;

	private $ignoreCheck = false;

	public function __construct(
		$ignoreCheck = false)
	{
		$this->ignoreCheck = $ignoreCheck;
		$this->params = [
			'dph' => OBE_AppCore::getDBVar('front', 'DPH'),
			'dphKoef' => OBE_AppCore::getDBVar('front', 'DPH_KOEF'),
			'dnzpSazba' => OBE_AppCore::getDBVar('front', 'DAN_Z_PLN')
		];
	}

	public function getHtml()
	{
		return $this->html;
	}

	public function getView()
	{
		return FakturaPreview::make($this->html);
	}

	public function setDzp(
		$dzp)
	{
		$this->dzp = ($dzp) ? $dzp : $this->dzp;
		return $this;
	}

	public function setCislo(
		$cislo)
	{
		$this->cislo = $cislo;
		return $this;
	}

	public function setVystaveni(
		$vyst)
	{
		$this->datumVystaveni = $vyst;
		return $this;
	}

	public function setSplatnost(
		$splat)
	{
		if($splat instanceof DateTime){
			$this->datumSplatnosti = $splat;
		}else{
			$this->splatnost = $splat;
		}
		return $this;
	}

	public function setExts(
		$params)
	{
		if(is_array($params) && isset($params['title'])){
			$this->exts = $params;
		}else{
			// 			throw new FakturaException('Předané další parametry faktury nejsou správné');
		}
		return $this;
	}

	public function load(
		$gp6Ids,
		$checkFaktured = true)
	{
		if(empty($gp6Ids)){
			throw new FakturaException('Nevybrány žádné zprávy.');
		}

		$gs = (new GP6Full())->FindBy('id', $gp6Ids, [], [], [
			'GP6Head.from' => 'ASC'
		]);

		return $this->loadArr($gs, $checkFaktured);
	}

	public function loadArr(
		$gs,
		$checkFaktured = true)
	{
		$h = reset($gs);
		$this->omId = $h['GP6Head']['odber_mist_id'];
		$this->type = $h['GP6Head']['type'];

		$this->loadOm($this->omId);

		$this->gs = $this->check(collection($gs), $checkFaktured);

		$f = $this->gs->min('f');
		$this->from = $f['f'];
		$t = $this->gs->max('t');
		$this->to = $t['t'];

		$this->from->setTime(0, 0, 0);
		$this->to->setTime(0, 0, 0);

		$zals = (new MZalohy())->getByRangeAndOmId($this->omId, $this->from, OBE_DateTime::modifyClone($this->to, '-1 day'));

		$this->zalohy = collection($zals ? $zals : []);

		$this->loadSml($this->omId, $this->from, $this->to);

		$this->zalohy = $this->zalohy->filter(function (
			$v)
		{
			return $v['Zalohy']['klient_id'] == $this->klientId;
		})
			->indexBy('Zalohy.zaloha_id')
			->sortBy('Zalohy.od');

		return $this;
	}

	private function loadOm(
		$omId)
	{
		$_OM = new MOdberMist();
		if($om = $_OM->FindOneById($omId)){
			$this->om = $om;
			return $this;
		}

		throw new FakturaException('V systému nenalezeno OM.');
	}

	private function loadSml(
		$omId,
		$from,
		$to)
	{
		$sml = $this->getSml($omId, $from, $to);

		$_MWH = new MCenaMWH();
		$mwh = $_MWH->getByRangeOmId($omId, $from, $to);
		$mwh = reset($mwh);

		if(1 > ($cenaMWh = $mwh[$_MWH->name]['cena'])){
			throw new FakturaException('Pro ' . $this->getOMInfo($from, $to) . ' není uvedena cena za MWh.');
		}

		$this->cmwh = $cenaMWh;
		$this->danZP = ($sml['SmlOMFlags']['dan']) ? 0 : $this->params['dnzpSazba'];
		$this->klientId = $sml['SmlOM']['klient_id'];
		$this->fakSkup = (new MFakSkup())->FindOneById($sml['SmlOM']['fak_skup_id']);
		$this->klient = (new MOdberatel())->FindOneById($this->klientId);
		$this->setSplatnost($this->klient['ContactDetails']['dat_spla_dnu']);

		if(!$this->klientId){
			throw new FakturaException('Nenalezen klient pro ' . $this->getOMInfo($from, $to) . '.');
		}

		return $this;
	}

	protected function getSml(
		$omId,
		$from,
		$to)
	{
		if($sml = (new MSmlOM())->getByRangeAndOmId($omId, $from, OBE_DateTime::modifyClone($to, '-1 day'))){
			return reset($sml);
		}

		throw new FakturaException('Nenalezena smlouva pro ' . $this->getOMInfo($from, $to) . '.');
	}

	public function build()
	{
		$this->buildZalohy();
		$this->buildDistribuce();
		$this->buildSum();
		$this->buildHead();
		$this->buildHistory();
		return $this;
	}

	private function buildZalohy()
	{
		$to = clone $this->to;

		$c = AdminApp::$container;

		$fac = $c->getByType(IZalohyDoFakturyStrategy::class);
		$str = $fac->create();

		$str->setFrom($this->from);
		$str->setTo($to->modify('-1 days'));
		$str->setKlientId($this->klientId);
		$str->setOmId($this->omId);

		$plas = $str->create();

		$sum = 0.0;
		$sum_z = 0.0;
		$sum_d = 0.0;

		$zals = [];
		foreach($plas as $p){
			if(!$p['when']){
				continue;
			}
			$sum += $p['vyse'];
			$sum_z += ($p['vyse'] - ($p['vyse'] * $p['dcoef']));
			$sum_d += ($p['vyse'] * $p['dcoef']);
			$zals[] = [
				'dp' => $p['when']->format('j.n. Y'),
// 				'od' => $p['od']->format('j.n. Y'),
				'c' => $p['vyse'],
				'z' => ($p['vyse'] - ($p['vyse'] * $p['dcoef'])),
				'd' => ($p['vyse'] * $p['dcoef'])
			];
		}

		$this->v['zalohy'] = $zals;
		$this->v['zz_c'] = $sum;
		$this->v['zz_z'] = $sum_z;
		$this->v['zz_d'] = $sum_d;

		return $this;
	}

	private function buildDistribuce()
	{
		$items = $this->gs->map(
			function (
				$v)
			{
				// 				dd($v);
				$is = [];
				$sum = 0;
				$rensum = 0;
				$summwh = 0;

				foreach($v['b']['meters'] as $m){
					// 					dd($m);
					$i = 0;
					$a = $m['atrib'];
					$b = $a['startState'];

					foreach($m['consumptions'] as $c){

						$dif = ($b + $i + $c['consumption']) - ($b + $i);

						$sum += $dif;
						$rensum += $dif * $c['factor'];
						$summwh += $dif * $c['factor'] * $c['flueGasHead'] / 1000;

						$is[] = [
							'o' => (($this->type == 'A' && $c['from'] == $c['to']) ? (new DateTime($c['from']))->format('d.m. Y') : (new DateTime($c['from']))->format(
								'd.m. Y') . ' - ' . OBE_DateTime::modifyClone(new DateTime($c['to']), '-1 day')->format('d.m. Y')),
							'cp' => $a['meterId'],
							's' => $b + $i,
							'e' => $b + $i + $c['consumption'],
							'dif' => $dif,
							't' => $a['readingType'],
							'kof' => $c['factor'],
							'pm3' => $dif * $c['factor'],
							'flu' => $c['flueGasHead'],
							'spm3' => $dif * $c['factor'] * $c['flueGasHead'] / 1000
						];

						$i += $c['consumption'];
					}
				}

				return [
					'items' => $is,
					'b' => $v['b'],
					'sum' => $sum,
					'rensum' => $rensum,
					'summwh' => $summwh,
					'psum' => $summwh * $this->cmwh,
					'dpsum' => $summwh * $this->danZP,
					'uprice' => $this->cmwh,
					'merj' => $this->danZP,
					'od' => $v['f'],
					'do' => $v['t']
				];
			})
			->compile();

		$this->distribuce = [
			'items' => $items->toArray(),
			'sum' => $items->sumOf('sum'),
			'psum' => $items->sumOf('psum') + ($this->danZP ? $items->sumOf('dpsum') : 0),
			'rensum' => $items->sumOf('rensum'),
			'summwh' => $items->sumOf('summwh'),
			'avgST' => $items->avg('items.{*}.flu')
		];

		$this->v['dzp_z'] = $items->sumOf('dpsum');
		$this->v['dzp_d'] = $this->dph_z_zakl($this->v['dzp_z']);
		$this->v['dzp_c'] = $this->v['dzp_z'] + $this->v['dzp_d'];

		$this->v['cco_z'] = $items->sumOf('psum');
		$this->v['cco_d'] = $this->dph_z_zakl($this->v['cco_z']);
		$this->v['cco_c'] = $this->v['cco_z'] + $this->v['cco_d'];

		$ote = $items->map(function (
			$v)
		{
			return $this->getOTE($v['b'], $v['summwh']);
		});

		$this->ote = [
			'items' => $ote->toArray(),
			'sum' => $ote->sumOf('sum')
		];

		return $this;
	}

	private function getOTE(
		$b,
		$gsum)
	{
		$cs = $b['contracts'];
		$is = $b['instruments'];

		$sum = 0;
		$ret = [];

		$from = null;
		$to = null;

		foreach($cs as $c){

			$cap = null;
			$pay = null;

			if(isset($c['payment'])){
				$pay = $c['payment'];
			}

			if(isset($c['capacity'])){
				$cap = $c['capacity'];
			}

			$f = new DateTime($c['from']);
			$t = (new DateTime($c['to']))->modify('+' . ($this->type == 'A' ? 1 : 0) . ' day');

			$a = [
				'od' => clone $f,
				'do' => clone $t
			];

			$a['msg'] = null;

			if($pay['rent']){
				$a['gsum'] = 0;
				$a['uprice'] = $pay['rent'];
				$a['obd'] = $pay['effect'];
				$a['psum'] = $pay['rent'] * $pay['effect']; // / 12;
			}elseif(isset($cap['volume'])){
				$a['gsum'] = $cap['volume'];
				$a['uprice'] = $cap['price'];
				$a['obd'] = $cap['effect'];
				$a['psum'] = ($cap['price'] * ($cap['volume'] / 1000) * $cap['effect']);
			}elseif(isset($cap['size'])){
				$a['gsum'] = $cap['size'];
				$a['uprice'] = $cap['price'];
				$a['obd'] = $cap['effect'];
				$a['psum'] = ($cap['price'] * ($cap['size'] / 1000) * $cap['effect']);
			}elseif(isset($cap['maxSize'])){
				$a['msg'] = 'překročení denní rezer.kapacity';
				$a['gsum'] = $cap['maxSize'];
				$a['uprice'] = $cap['unitPrice'];
				$a['obd'] = $cap['monthFactor'];
				$a['psum'] = ($cap['unitPrice'] * ($cap['maxSize'] / 1000) * $cap['monthFactor']);
			}
			$ret['mpk'][] = $a;

			$sum += (float) number_format((float) $a['psum'], 2, '.', '');

			if(!$from || $f < $from){
				$from = $f;
			}

			if(!$to || $t > $to){
				$to = $t;
			}
		}

		$f = 0;
		foreach($is as $i){
			$ret['mp'][] = [
				'od' => new DateTime($i['from']),
				'do' => (new DateTime($i['to']))->modify('+' . ($this->type == 'A' ? 1 : 0) . ' day'),
				'gsum' => $i['distributionSum'] / 1000,
				'uprice' => $i['unitPrice'] * 1000,
				'psum' => $i['distributionSum'] * $i['unitPrice']
			];
			$sum += (float) number_format((float) $i['distributionSum'] * $i['unitPrice'], 2, '.', '');
			$f = $i['MOSettlementUnitPrice'];
		}

		$ret['po'] = [
			'od' => $from,
			'do' => $to,
			'gsum' => $gsum,
			'uprice' => $f * 1000,
			'psum' => ($f * 1000) * $gsum
		];

		$ret['sum'] = $sum + number_format((float) $ret['po']['psum'], 2, '.', '');

		return $ret;
	}

	private function buildSum()
	{
		$this->total = $this->ote['sum'];
		$this->totalDph = $this->dph_z_zakl($this->ote['sum']);

		$this->v['ccd_z'] = $this->total;
		$this->v['ccd_d'] = $this->totalDph;
		$this->v['ccd_c'] = $this->totalDph + $this->total;

		$this->v['cpm_z'] = $this->v['cco_z'] + $this->v['ccd_z'];
		$this->v['cpm_d'] = $this->v['cco_d'] + $this->v['ccd_d'];
		$this->v['cpm_c'] = $this->v['cco_c'] + $this->v['ccd_c'];

		$this->v['c_z'] = $this->v['cpm_z'] + $this->v['dzp_z'];
		$this->v['c_d'] = $this->v['cpm_d'] + $this->v['dzp_d'];
		$this->v['c_c'] = $this->v['cpm_c'] + $this->v['dzp_c'];

		$this->v['u'] = $this->exts;
		$this->v['rz'] = ($this->v['c_z'] - $this->v['zz_z']) + $this->exts['z'];
		$this->v['rd'] = ($this->v['c_d'] - $this->v['zz_d']) + $this->exts['d'];
		$this->v['rc'] = ($this->v['c_c'] - $this->v['zz_c']) + $this->exts['c'];
		$this->v['fin'] = ($this->v['c_c'] - $this->v['zz_c']) + $this->exts['c'];

		return $this;
	}

	private function buildHead()
	{
		$this->datumVystaveni = $this->datumVystaveni ? $this->datumVystaveni : new DateTime();
		$this->datumSplatnosti = $this->datumSplatnosti ? $this->datumSplatnosti : OBE_DateTime::modifyClone($this->datumVystaveni,
			'+' . $this->splatnost . ' days');

		$this->head = [
			'dat_vys' => $this->datumVystaveni->format('j.n. Y'),
			'dat_spl' => $this->datumSplatnosti->format('j.n. Y'),
			'dat_dan' => $this->dzp ? $this->dzp->format('j.n. Y') : OBE_DateTime::modifyClone($this->to, '-1 day')->format('j.n. Y'),
			'zuc_obd' => $this->from->format('j.n. Y') . ' - ' . OBE_DateTime::modifyClone($this->to, '-1 day')->format('j.n. Y'),
			'zpus_pla' => 'příkaz k úhradě',
			'from' => $this->from,
			'to' => $this->to,
			'd' => $this->getDod(),
			'o' => $this->getOdb($this->klient, $this->fakSkup),
			'om' => $this->getOm($this->om),
			'fa_c' => $this->cislo,
			'zuc_mwh' => $this->distribuce['summwh'],
			'dan_zp' => $this->danZP,
			'dph_sazba' => $this->params['dph'],
			'dph_koef' => $this->params['dphKoef'],
			'params' => $this->exts
		];

		return $this;
	}

	private function buildHistory()
	{
		$year = $this->from->format('Y');

		$c = AdminApp::$container;

		$tblSpotreba = $c->getByType(SpotrebaTable::class);
		$tblFaktury = $c->getByType(FakturaTable::class);

		$selSpotr = new SpotrebaByOmYearSelection($tblFaktury, $tblSpotreba);
		$this->history = $selSpotr->get($year, $this->omId);

		return $this;
	}

	private function getDod()
	{
		$A = OBE_AppCore::getDBVar('front', 'OBCHODNIK_IDENT_A');
		$B = OBE_AppCore::getDBVar('front', 'OBCHODNIK_IDENT_B');

		$aA = explode(',', $A);
		$aB = explode(',', $B);

		return [
			'name' => $aA[0],
			'addr' => $aA[1] . ', ' . $aA[2],
			'ic' => str_replace(' ', '', str_replace('IČO ', '', $aA[3])),
			'dic' => str_replace(' ', '', str_replace('DIČ ', '', $aB[0])),
			'zapis' => $aB[1] . ', ' . $aB[2] . ', ' . $aB[3],
			'cu' => OBE_AppCore::getDBVar('front', 'CU_OBCHODNIKA'),
			'cl' => 241015819
		];
	}

	private function getOdb(
		$odberatel,
		$fakSkup)
	{
		$faAddr = null;
		$cu = null;

		if($fakSkup){
			$O = new MOdberatel();
			$fak = $O->FindOneById($fakSkup['FakSkup']['fa_klient_id']);

			$konAddr = MContactDetails::konAddr($fak, 'Korespond');
			if(MContactDetails::isKonAddrEmpty($konAddr)){
				$odbAddr = MContactDetails::konAddr($odberatel);
				$konAddr = $odbAddr;
			}

			$faAddr = MContactDetails::konAddr($odberatel);
			if($fak['ContactDetails']['cu']){
				$cu = $fak['ContactDetails']['cu'];
			}else{
				$cu = $odberatel['ContactDetails']['cu'];
			}
		}else{
			$odbAddr = MContactDetails::konAddr($odberatel);
			$konAddr = MContactDetails::konAddr($odberatel, 'Korespond');
			if(MContactDetails::isKonAddrEmpty($konAddr)){
				$konAddr = $odbAddr;
			}
			$cu = $odberatel['ContactDetails']['cu'];
		}

		$name = $korespondencni = '<b>' . MContactDetails::name($odberatel['ContactDetails']) . '</b><br />' . str_replace("\r\n", '<br />', $konAddr);
		$fakturacni = '<b>' . MContactDetails::name($odberatel['ContactDetails']) . '</b><br />' . str_replace("\r\n", '<br />',
			(($faAddr) ? $faAddr : $odbAddr));

		return [
			'name' => MContactDetails::name($odberatel['ContactDetails']),
			'ic' => ($odberatel['ContactDetails']['kind']) ? $odberatel['ContactDetails']['ico'] : '',
			'dic' => ($odberatel['ContactDetails']['kind']) ? $odberatel['ContactDetails']['dico'] : '',
			'cu' => $cu,
			'k_addr' => $korespondencni,
			'f_addr' => $fakturacni
		];
	}

	public function render()
	{
		$smarty = new OBE_Smarty();
		$smarty->assign('f', $this);

		$this->html = $smarty->fetch('custom/faktura.tpl');
		return $this;
	}

	public function udpate(
		$userId,
		$id)
	{
		(new MFaktury())->saveOTEFak($this, $userId, $id);
		return $this;
	}

	public function save(
		$userId)
	{
		(new MFaktury())->saveOTEFak($this, $userId);
		return $this;
	}

	public function savePdf()
	{
		(new FakturaFile($this))->saveAndSendPdf(true);
		return $this;
	}

	public function sendPdf(
		$sendOnly = false)
	{
		if($sendOnly){
			(new FakturaFile($this))->sendPdf();
		}else{
			(new FakturaFile($this))->saveAndSendPdf();
		}
		return $this;
	}

	/**
	 * @param Collection $c
	 * @param bool $checkFaktured
	 * @throws FakturaException
	 * @return Collection
	 */
	private function check(
		$c,
		$checkFaktured)
	{
		if($checkFaktured){
			$faktured = $c->filter(function (
				$i)
			{
				return $i['GP6Head']['faktura_id'];
			});

			if(!$this->ignoreCheck && !$faktured->isEmpty()){
				throw new FakturaException('Jedna z vybraných zpráv už byla vyfakturována.');
			}
		}

		$heads = $this->mapHeads($c);

		$end = null;
		$heads->each(
			function (
				$v) use (
			&$end)
			{
				if($v['cor'] == '01' || $v['cor'] == '02'){
					return;
				}

				if(!$end){
					$end = $v['t'];
					return;
				}

				if($end->format('U') != $v['f']->format('U')){
					throw new FakturaException(sprintf('Nekonzistence řady %s  -  %s.', $end->format('j.n. Y H:i:s'), $v['f']->format('j.n. Y H:i:s')));
				}

				$end = $v['t'];
			});

		$aha = $heads->toArray();
		$G = new GP6Head();
		$ah = $heads->each(
			function (
				$v,
				$k) use (
			$G,
			$aha)
			{
				if(!$r = $G->FindOneBy('attributes_SCNumber', $k)){
					return;
				}

				$cr = $r[$G->name]['attributes_corReason'];
				if($cr != '01' || $cr != '02'){
					return;
				}

				if(!isset($aha[$r[$G->name]['attributes_number']])){
					throw new FakturaException('Pro vybrané ote fakturace existuje storno nebo oprava a není vybrána.');
				}
			})
			->compile();

		$gs = $heads->reject(
			function (
				$v,
				$k) use (
			$ah)
			{
				if($f = $ah->firstMatch([
					'scnumber' => $k
				])){
					return $v['f']->format('U') == $f['f']->format('U') && $v['t']->format('U') == $f['t']->format('U');
				}
				return false;
			});

		if($gs->isEmpty()){
			throw new FakturaException('Není co vyfakturovat.');
		}

		return $gs->compile();
	}

	/**
	 * @param Collection $c
	 * @return Collection
	 */
	private function mapHeads(
		$c)
	{
		return $c->map(
			function (
				$v,
				$k)
			{
				$h = $v['GP6Head'];

				$f = new DateTime($h['from']);
				$t = new DateTime($h['to']);

				return [
					'id' => $h['id'],
					'b' => unserialize($v['GP6Body']['data']),
					'h' => $h,
					'f' => $f,
					't' => $t,
					'anumber' => $h['attributes_number'],
					'scnumber' => $h['attributes_SCNumber'],
					'cor' => $h['attributes_corReason'],
					'priceTotal' => $h['priceTotal'],
					'priceTotalDph' => $h['priceTotalDph']
				];
			})
			->sortBy(function (
			$v)
		{
			return -$v['f']->format('U');
		})
			->indexBy('anumber');
	}

	private function getOm(
		$om)
	{
		return [
			'addr' => MAddress::addr($om),
			'com' => $om['OdberMist']['com'],
			'eic' => $om['OdberMist']['eic']
		];
	}

	private function getOMInfo(
		$from,
		$to)
	{
		return 'OM: ' . $this->om['OdberMist']['com'] . ' ( ' . $this->om['OdberMist']['eic'] . ' ) v rozsahu od: ' . $from->format('j.n. Y') . ' do: ' . $to->format(
			'j.n. Y');
	}

	private function dph_z_dphp(
		$v)
	{
		return $v * ($this->params['dph'] / (100 + $this->params['dph']));
	}

	private function zakl_dph_z_dphp(
		$v)
	{
		return $v - ($v * ($this->params['dph'] / (100 + $this->params['dph'])));
	}

	private function dph_z_zakl(
		$v)
	{
		return $v * ((100 + $this->params['dph']) / 100) - $v;
	}
}
