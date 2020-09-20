<?php

class CsvReaderClass{

	/**
	 * csv separator
	 * @var string
	 */
	var $separator = ';';

	/**
	 * csv enclouser
	 * @var string
	 */
	var $encloser = '"';

	/**
	 * Odloziste error zprav v ramci cteni jedne radky csv
	 * @var array
	 */
	var $error_buffer = [];

	/**
	 * pole se vsemi chybami v ramci csv souboru
	 * @var array - [key] => array('line' => cislo radku, 'message' => array('msg1', ...))
	 */
	var $errors_on_line = [];

	/**
	 * pokud je definovano tak striktne omezi pocet poli, nesmi byt ani mene ani vice
	 * @var integer
	 */
	var $checkNumFileds = NULL;

	/**
	 * Callback po nacteni radek
	 * @var closure
	 */
	var $readCallBack = NULL;

	/**
	 * konstruktor
	 * @return importClass
	 */
	function __construct()
	{
	}

	function parseCSVFile(
		$fileName)
	{
		$line_no = 0;
		$this->error_buffer = NULL;
		if($hFile = fopen($fileName, "r")){
			$head = fgetcsv($hFile, NULL, $this->separator, $this->encloser);
			if($this->checkNumFileds !== NULL){
				if(sizeof($head) != $this->checkNumFileds){
					$this->error_buffer = 'Nespravný počet polí v hlavičce';
					return -1;
				}
			}
			while($line = fgetcsv($hFile, NULL, $this->separator, $this->encloser)){
				if(!$this->readFromCSV($line, $userValid)){
					if($userValid){
						$this->errors_on_line[] = [
							'line' => $line_no,
							'message' => $this->error_buffer
						];
					}
				}
				$line_no++;
			}
			fclose($hFile);
		}else{
			$this->error_buffer = "Soubor nelze otevřít";
			return -1;
		}
		return $line_no;
	}

	/**
	 * preklada radku csv
	 * @param array $line
	 * @param bool $userValid - umoznuje preskocit radek bez zakladani idecek hned po nacteni
	 * @return string|null
	 */
	function readFromCSV(
		$line,
		&$userValid)
	{
		$userValid = true;

		if($this->checkNumFileds !== NULL){
			if(sizeof($line) != $this->checkNumFileds){
				$this->error_buffer = 'Nespravný počet polí na řádce';
				return NULL;
			}
		}

		return $line;
	}

	/**
	 * prida chybovou hlasku
	 * @param $str
	 */
	function addError(
		$str)
	{
		if(!empty($this->error_buffer)){
			$this->error_buffer .= '\r\n';
		}else{
			$this->error_buffer = '';
		}
		$this->error_buffer .= $str;
	}

	/**
	 * vytvori soubor ktery obsahuje jen
	 * @param string $filename
	 */
	function makeErrorBackUpFile(
		$filename,
		$backupfilename)
	{
		if(!empty($this->errors_on_line)){
			$hFileOrg = fopen($filename, "r");
			$hFileBack = fopen($backupfilename, "w+");
			$head = fgets($hFileOrg);
			fputs($hFileBack, $head);
			$line_no = 0;
			$eline = array_shift(reset($this->errors_on_line));
			while($line = fgets($hFileOrg)){
				if($line_no == $eline){
					fputs($hFileBack, $line);
					if(!($eline = next($this->errors_on_line))){
						break;
					}else{
						$eline = $eline['line'];
					}
				}
				$line_no++;
			}
			fclose($hFileBack);
			fclose($hFileOrg);
			return $backupfilename;
		}
		return NULL;
	}

	function readCallBack(
		$item,
		$newIds,
		$line)
	{
		if($this->readCallBack){
			$item = call_user_func($this->readCallBack, $item, $newIds, $line);
		}
		return $item;
	}

	/**
	 * oreze uvodni a koncove uvozovky
	 * @param $val
	 */
	static function strPremakeValue(
		$val)
	{
		$val = preg_replace('/(~")*("$)/', '', $val);
		$val = trim($val);
		return $val;
	}
}