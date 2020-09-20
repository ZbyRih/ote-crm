<?php

class ExportClass{
	/**
	 * @var CSVWriterClass
	 */
	private $writerObj = NULL;
	private $lineCallBack = NULL;

	public function __construct($sqlView, $writerObj, $lineCallBack = NULL){
		$this->sqlView = $sqlView;
		$this->writerObj = $writerObj;
		$this->lineCallBack = $lineCallBack;
	}

	private function getData(){
		$sqlObj = NULL;
		if(is_string($this->sqlView)){
			$sql = $this->sqlView;
			$sqlObj = new SqlView();
		}else{
			$sqlObj = $this->sqlView;
			$sql = $sqlObj->MakeSql();
		}

		if($sqlObj->Execute($sql)){
			return $sqlObj->data;
		}
	}

	public function export(){
		$this->writerObj->prepare();
		if($data = $this->getData()){
			foreach($data as $item){
				$item = $this->writeCallBack($item);
				$witem = $this->writerObj->mapData($item);
				$this->writerObj->write($witem);
			}
		}
	}

	private function writeCallBack($item){
		if($this->lineCallBack){
			return call_user_func($this->lineCallBack, $item);
		}
		return $item;
	}

	public function flush($realFilename){
		$this->writerObj->close();
		OBE_Http::headForForceDownload($realFilename, $this->writerObj->getSize(), 'text/csv');
	    ob_clean();
	    flush();
    	$this->writerObj->output();
    	ob_end_flush();
	}
}