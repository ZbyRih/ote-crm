<?php
namespace App\Core;

interface ILogger{

	const DEBUG = 'debug', INFO = 'info', WARNING = 'warning', ERROR = 'error', EXCEPTION = 'exception', CRITICAL = 'critical';

	public function log($msg, $priority = self::INFO);
}

class Logger implements ILogger{

	private $file;

	public function __construct($sub, $file){
		if($sub){
			\OBE_File::checkDirectorys(APP_DIR_OLD . '/log/' . $sub);
			$this->file = new \OBE_FileLog(APP_DIR_OLD . '/log/' . $sub . '/' . $file, \OBE_FileLog::LOG_SESSION_AS_FILE);
		}
	}

	public function log($strs, $priority = ILogger::INFO){
		if($this->file){
			$this->file->write($strs);
		}

		if(\OBE_Core::$cli){
			\OBE_Cli::writeBr($strs);
		}
	}
}