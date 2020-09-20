<?php


class OBE_FileLog{

	const LOG_NONE = 0;

	const LOG_INCREMENTAL = 1;

	const LOG_ONE_FILE = 2;

	const LOG_SESSION_AS_FILE = 3;

	const MAX_FILE_IN_DIR = 10;

	const END_LINE = "\n";

	/**
	 *
	 * @var OBE_FileIO
	 */
	private $file = null;

	function __construct($name, $mode = self::LOG_ONE_FILE){
		if($mode == self::LOG_SESSION_AS_FILE){
			$elem = OBE_FileIO::fileNameToNameAndExt($name);
			$name = $elem[0] . '_' . date('Ymd_His') . '.' . $elem[1];
		}

		$write = 'w+';
		if($mode == self::LOG_INCREMENTAL){
			if($name === null){
				throw new OBE_Exception('Není definováno jméno logovacího souboru a logování je nastaveno na incremental');
			}
			if(file_exists($name)){
				$write = 'a+';
			}

			$this->file = new OBE_FileIO($name);
			$this->file->open($write);
			return;
		}

		$this->file = new OBE_FileIO($name, 'buff');
	}

	function flush(){
		$this->file->flush();
	}

	function close(){
		$this->file->close();
	}

	function write($strLines = ''){
		$this->file->lines($strLines, self::END_LINE);
	}

	function clean(){
		$outFile = $this->file->getName();
		$path_parts = pathinfo($this->file->getName());

		$files = glob($path_parts['dirname'] . '/*');

		if(count($files) > self::MAX_FILE_IN_DIR){
			$kfiles = [];

			foreach($files as $file){
				if(is_file($file) && $file != $outFile){
					$kfiles[filemtime($file)] = $file;
				}
			}

			$kfiles = array_reverse($kfiles);

			while(count($kfiles) > self::MAX_FILE_IN_DIR){
				@unlink(array_shift($kfiles));
			}
		}
	}

	function copy(){
		$this->file->copyWithTimeMark();
	}

	function getContent(){
		return $this->file->getContent();
	}
}