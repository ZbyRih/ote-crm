<?php


class MOTEMails extends ModelClass{

	var $name = 'OTEMails';

	var $table = 'tx_mails_ote';

	var $primaryKey = 'id';

	var $rows = [
		'id',
		'msg_uid',
		'received',
		'decrypted',
		'processed',
		'file_eml',
		'file_xml',
		'ote_kod',
		'ote_id',
		'subject'
	];

	public function years($cond = []){
		$years = $this->FindAll($cond, [
			'!YEAR(Min(received)) AS y_min',
			'!YEAR(MAX(received)) AS y_max'
		]);
		if($years){

			$years = reset($years);

			$min = $years[$this->name]['y_min'];
			$max = $years[$this->name]['y_max'];

			$years = [];

			if($min && $max){
				for($y = $min; $y <= $max; $y++){
					$years[] = $y;
				}
			}

		}

		if(!$years){
			$years = [
				date('Y')
			];
		}

		return array_combine($years, $years);
	}

	public function getFileCnt($m){
		$m = $m[$this->name];
		$file = null;
		$cnt = null;

		if($m['decrypted']){
			if($m['processed']){
				if($m['file_xml']){
					$file = $m['file_xml'];
					$cnt = $this->formatXml(file_get_contents($this->file($m['file_xml'])));
				}
			}else{
				if($m['file_xml']){
					$file = $m['file_xml'];
					$cnt = $this->formatXml(file_get_contents($this->file($m['file_xml'])));
				}elseif($m['file_eml']){
					$file = $m['file_eml'];
					$cnt = $this->decodeMessage(file_get_contents($this->file($m['file_eml'])));
				}
			}
		}else{
			$file = $m['file_eml'];
			$cnt = $this->decodeMessage(file_get_contents($this->file($m['file_eml'])));
		}

		return [
			$file,
			$cnt
		];
	}

	function file($file){
		$file = APP_DIR_OLD . '/' . ltrim($file, './app');
		if(!file_exists($file)){
			throw new OBE_FileException('File : ' . $file . ' not exits.');
		}
		return $file;
	}

	function decodeMessage($cnt){
		$elm = explode("\r\n\r\n", $cnt);

		if(strpos($elm[0], 'Content-Transfer-Encoding: quoted-printable')){
			return $elm[0] . "\r\n\r\n" . quoted_printable_decode($elm[1]);
		}

		if(strpos($elm[0], 'Content-Transfer-Encoding: base64')){
			return $elm[0] . "\r\n\r\n" . base64_decode($elm[1]);
		}

		return $cnt;
	}

	function formatXml($xml){
		$sxml = simplexml_load_string($xml);
		$dom = dom_import_simplexml($sxml)->ownerDocument;
		$dom->formatOutput = true;
		return htmlentities($dom->saveXML());
	}
}
