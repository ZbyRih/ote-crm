<?php
use App\Models\Strategies\Fakturace\CreateHtmlFakturaStrategy;

class MFaktury extends ModelClass{

	const CIS = 'fa';

	var $name = 'Faktura';

	var $primaryKey = 'id';

	var $table = 'tx_faktury';

	var $rows = [
		'id',
		'klient_id',
		'om_id',
		'user_id',
		'platba_id',
		'man',
		'storno',
		'deleted',
		'cis',
		'od',
		'do',
		'vystaveno',
		'odeslano',
		'splatnost',
		'suma',
		'dph',
		'suma_a_dph',
		'sum_zals',
		'preplatek',
		'dzp',
		'dph_sazba',
		'dph_koef',
		'dan_zp',
		'cena_distribuce',
		'cena_dan_plyn',
		'cena_za_mwh',
		'nakup_mwh',
		'spotreba',
		'uhrazeno_dzp',
		'!getUhrFaktura(Faktura.id) AS uhrazeno',
		'html',
		'params',
		'ext'
	];

	public function onSaveBefor(
		&$modelItem)
	{
		unset($modelItem[$this->name]['uhrazeno']);
	}

	public function onDelete(
		$id,
		$conditions,
		$cascade)
	{
		$F = new MFaktury();
		$F->conditions = $conditions;

		if(!($faks = $F->FindAllById($id))){
			return false;
		}

		$faks = collection($faks);
		$ids = $faks->extract('Faktura.id')->toList();

		$PPF = new MPVParFZ();
		if($count = $PPF->Count('id', [
			'faktura_id' => $ids
		])){
			$count = reset($count);
			if($count['num'] > 0){
				throw new ModelDeleteException('Faktura je svázána s platbou.');
			}
		}

		$ret = true;

		$faks->each(
			function (
				$v,
				$k) use (
			$F,
			&$ret)
			{
				$cis = $v[$F->name]['cis'];
				$id = $v[$F->name]['id'];

				if($v[$F->name]['odeslano']){
					throw new ModelDeleteException('Faktura ' . $cis . ' byla již odeslána, půjde pouze stornovat.');
				}else{
					$this->unlinkGP6($id);

					// 					AdminApp::postMessage('Faktura ' . $cis . ' smazána.', 'info');

					$pp = Counters::getPP(self::CIS, $cis);
					$current = Counters::get(self::CIS, $pp);

					if($current == $cis){
						self::updateCis($cis);
						$ret &= true;
					}else{

						$v[$F->name]['deleted'] = 'NOW()';
						$F->Save($v);

						$ret &= false;
					}
				}
			});

		return $ret;
	}

	public function unlinkGP6(
		$fid)
	{
		$gp6 = new GP6Head();
		if($hs = $gp6->FindBy('faktura_id', $fid)){
			collection($hs)->each(function (
				$v,
				$k)
			{
				$v['GP6Head']['faktura_id'] = null;
				(new GP6Head())->Save($v);
			});
		}
	}

	public function getYears(
		$cond = [])
	{
		$years = $this->FindAll($cond, [
			'!YEAR(MIN(vystaveno)) AS y_min',
			'!YEAR(MAX(vystaveno)) AS y_max'
		]);
		if($years){

			$years = reset($years);

			$min = $years[$this->name]['y_min'];
			$max = $years[$this->name]['y_max'];

			$years = [];

			if($min && $max){
				$years = range($min, $max);
			}
		}

		if(!$years){
			$years = [
				date('Y')
			];
		}

		return array_combine($years, $years);
	}

	public function getView(
		$id)
	{
		$F = new MFaktury();
		$_f = $F->FindOneById($id);
		if($_f[$F->name]['man']){
			throw new FakturaException('manuální faktura nemá náhled');
		}

		$html = $_f[$F->name]['html'];

		if($_f[$F->name]['storno'] && !strpos($html, '<div id="faktura-storno">')){
			$storno = file_get_contents(APP_DIR . '/assets/files/storno.html');

			$html = str_replace('<div id="faktura">', '<div id="faktura">' . $storno, $html);
		}

		return FakturaPreview::make($html);
	}

	/**
	 * @param OTEFaktura $fak
	 * @param integer $updateId
	 */
	public function saveOTEFak(
		$fak,
		$userId,
		$updateId = false)
	{
		$F = new MFaktury();

		$sda[$F->name] = [
			'klient_id' => $fak->klientId,
			'om_id' => $fak->omId,
			'cis' => $fak->cislo,
			'od' => OBE_DateTime::convertDTToDB($fak->from),
			'do' => OBE_DateTime::convertDTToDB($fak->to),

			'suma' => self::nf($fak->v['c_z']),
			'dph' => self::nf($fak->v['c_d']),
			'suma_a_dph' => self::nf($fak->v['c_c']),
			'preplatek' => self::nf($fak->v['fin']),
			'cena_distribuce' => self::nf($fak->v['ccd_z']),
			'cena_dan_plyn' => self::nf($fak->v['dzp_z']),
			'cena_za_mwh' => self::nf($fak->cmwh),
			'spotreba' => $fak->head['zuc_mwh'],

			'dan_zp' => $fak->head['dan_zp'],
			'dph_sazba' => $fak->head['dph_sazba'],
			'dph_koef' => $fak->head['dph_koef'],
			'dzp' => OBE_DateTime::convertToDB($fak->head['dat_dan']),

			'user_id' => $userId,
			'params' => serialize($fak->exts),
			'ext' => 'pdf'
		] + (($updateId === false) ? [
			'vystaveno' => OBE_DateTime::convertDTToDB($fak->datumVystaveni),
			'splatnost' => OBE_DateTime::convertDTToDB($fak->datumSplatnosti)
		] : []) + ($fak->html ? [
			'html' => $fak->html
		] : []);

		if($updateId !== false){
			$sda[$F->name][$F->primaryKey] = $updateId;
			$sda[$F->name]['odeslano'] = null;
		}

		$F->Save($sda);

		if($updateId === false){
			$id = $sda[$F->name]['id'];

			$gh = new GP6Head();
			$ids = $fak->gs->extract('id');

			$gs = $gh->FindAllById($ids->toArray());

			foreach($gs as $g){
				$g[$gh->name]['faktura_id'] = $id;
				$h[$gh->name] = $g[$gh->name];
				$gh->save($h);
			}
		}
	}

	public function sparovat(
		$pid,
		$fid)
	{
		$F = new MFaktury();
		$P = new MPlatby();
		$PPF = new MPVParFZ();

		$pl = $P->FindOneById($pid);
		$fa = $F->FindOneById($fid);

		if($pl && $fa){

			$a = [
				$PPF->name => [
					'platba_id' => $pid,
					'faktura_id' => $fid,
					'suma' => (($pl[$P->name]['platba'] > $fa[$F->name]['preplatek']) ? $fa[$F->name]['preplatek'] : $pl[$P->name]['platba']),
					'dne' => $pl[$P->name]['when']
				]
			];

			$PPF->Save($a);
		}
	}

	public static function nf(
		$f)
	{
		return number_format($f, 2, '.', '');
	}

	public static function getNewCis(
		$pp)
	{
		return Counters::getNext(self::CIS, $pp);
	}

	public static function updateCis(
		$cis)
	{
		if($prev = (new MFaktury())->FindOne([
			'cis < ' . $cis
		], [], [
			'cis DESC'
		])){
			$pcis = $prev['Faktura']['cis'];
			$pp = Counters::getPP(self::CIS, $cis);
			$pp2 = Counters::getPP(self::CIS, $pcis);
			if($pp == $pp2){
				Counters::set(self::CIS, $pcis, $pp);
			}
		}
	}
}