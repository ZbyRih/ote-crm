<?php
setlocale(LC_CTYPE, 'cs_CS.utf8');

class CSVWriterClass extends OBE_FileIO{
	public $delimiter = ';';
	public $enclosure = '"';

	private $Head = [];
	private $mapData = [];
	private $codePage = 'UTF-8';

	const NATIVE = 'UTF-8';

	public function __construct($CSVHead, $delimiter = ';', $enclosure = '"'){
		$this->Head = $CSVHead;
		parent::__construct(OBE_FileIO::getTemp());
		$this->open('w+');
		$this->mapData = array_keys($this->Head);
	}

	public function __destruct(){
		$this->unlink();
	}

	public function prepare(){
		$head = array_values($this->Head);
		$this->write($head);
	}

	public function mapData($item){
		$witem = [];
		foreach($this->mapData as $key){
			$witem[] = $item[$key];
		}
		return $witem;
	}

	public function setCodePage($codePage){
		$this->codePage = $codePage;
	}

	public function write($data){
		if($this->codePage != self::NATIVE){
			$this->changeCodePage($data);
		}
		fputcsv($this->handle, $data, $this->delimiter, $this->enclosure);
	}

	private function changeCodePage(&$array){
		foreach($array as &$item){
			$item = @iconv(self::NATIVE, $this->codePage . '//TRANSLIT//IGNORE', $item);
		}
	}
}