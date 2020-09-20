<?php

class PlatbyFileReader{

	public function __construct(){
	}

	public function readCSV($file, &$rest){
		$lines = explode("\r\n", $file);
		$csv = [];
		foreach($lines as $l){
			$csv[] = array_map(function ($v){
				return trim($v, '"');
			}, explode('	', $l));
		}

		array_splice($csv, 0, 5);

		$plas = [];

		foreach($csv as $l){
			if(!$l || !array_key_exists(1, $l)){
				continue;
			}

			$p = [];

			$castka = strtr($l[6], [
				'CZK' => '',
				' ' => '',
				' ' => '',
				'/' => ''
			]);

			$castka = OBE_Math::correctFloatNumber($castka);

			if($castka < 0){
				continue;
			}

			$p['when'] = OBE_DateTime::convertToDB($l[4]);
			$p['platba'] = $castka;
			$p['from'] = trim($l[14]);
			$p['sub'] = $l[13];

			$p['ks'] = $l[9];
			$p['vs'] = $l[10];
			$p['ss'] = $l[11];
			$p['msg'] = $l[15];

			$plas[] = $p;
		}

		$plas = $this->check($plas, $rest);
		return $this->insert($plas);
	}

	public function readOld($cnt, &$rest){
		$platby = [];
		if($res_count_id = preg_match_all(
			'/^datum zaúčtování\:  (\d{1,2}\.\d{1,2}\.\d{4})\r\n' . 'částka\:            ([\d\.\d{1,2}]+)\r\n' . '.*\r\n+' . '.*\r\n+' . 'konstantní symbol\: (\d+)?\r\n' . 'variabilní symbol\: (\d+)?\r\n' . 'specifický symbol\: (\d+)?\r\n' . '.*\r\n+' . 'název protiúčtu\:   ([\w\W]+?)\r\n' . 'protiúčet\:         (\d*-?\d*\/\d{4,})\r\n' . '/im',
			$cnt, $matchs_id)){

			$matchs_msg = '';

			preg_match_all('/^poznámka:          ((.*))\r\n/imU', $cnt, $matchs_msg);

			for($i = 0; $i < $res_count_id; $i++){

				if(OBE_DateTime::getYear($matchs_id[1][$i]) > 2014){
					$p = [];
					$p['when'] = OBE_DateTime::convertToDB($matchs_id[1][$i]);
					$p['platba'] = OBE_Math::correctFloatNumber($matchs_id[2][$i]);
					$p['from'] = $matchs_id[7][$i];
					$p['sub'] = $matchs_id[6][$i];

					$p['ks'] = trim($matchs_id[3][$i]);
					$p['vs'] = trim($matchs_id[4][$i]);
					$p['ss'] = trim($matchs_id[5][$i]);
					$p['msg'] = isset($matchs_msg[1][$i]) ? trim($matchs_msg[1][$i]) : '';
					$platby[] = $p;
				}
			}
		}

		$platby = $this->check($platby, $rest);
		return $this->insert($platby);
	}

	public function check($platby, &$rest){
		$Platba = new MPlatby();
		$checked = [];
		foreach($platby as $p){
			if($c = $Platba->CountBy(null, null,
				[
					'when' => $p['when'],
					'from_cu' => $p['from'],
					'subject' => $p['sub'],
					'platba' => $p['platba'],
					'vs' => $p['vs']
				]) < 1){
				$checked[] = $p;
			}else{
				$rest[] = $p;
			}
		}
		return $checked;
	}

	public function insert($incomes){
		if($incomes){
			$Platba = new MPlatby();

			foreach($incomes as $f){
				$pl['Platba'] = [
					'when' => $f['when'],
					'from_cu' => $f['from'],
					'subject' => $f['sub'],
					'platba' => $f['platba'],
					'vs' => $f['vs'],
					'ks' => $f['ks'],
					'ss' => $f['ss'],
					'msg' => $f['msg'],
					'man' => 0,
					'edit' => 0,
					'link' => 0,
					'mail_id' => null
				];
				$Platba->Save($pl);
			}
			return count($incomes);
		}
		return 0;
	}
}