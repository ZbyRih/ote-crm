<?php
class xmlExportClass extends exportClass{
	var $charset_in = 'utf-8';
	var $charset_out = 'utf-8';

	function __construct($sqlView){
		parent::__construct($sqlView);
		$head = '<?xml version="1.0" encoding="' . $this->charset_out . '"?>'. "\r\n";
		$this->Write($head);
	}

	function prepare(){

	}

	function Main($fclose = true){
		if($this->hFile){
			$sql = $this->sqlView->MakeSql();
			if($this->sqlView->Execute($sql)){
				foreach($this->sqlView->data as $item){
					$this->Write($this->_callBackData($item));
				}
			}
			if($fclose){
				fclose($this->hFile);
				$this->hFile = NULL;
			}
		}
	}

	function _callBackData($data){
		return $data;
	}
}