<?php

class OBE_DBException extends OBE_Exception{

	var $name = 'Chyba databáze';

	static function writeLog(
		$eObj)
	{
		$varDump = NULL;

		if(isset($eObj->varDump) && !empty($eObj->varDump)){
			$varDump = $eObj->varDump;
		}

		$message = self::getFormatMessage($eObj);
		$stack = self::getFormatTrace($eObj);

		OBE_Error::output(array_merge($message, $stack), E_USER_ERROR, $varDump);
	}
}

class OBE_DBErrors{

	const SERVER_TOO_BUSSY = 1226;

	const CONNECTION_NOT_WORKING = 2002;

	const GONE_AWAY = 2006;

	/**
	 *
	 * @var OBE_QryDump
	 */
	private $cache = [];

	private $count = 0;

	private $conn = NULL;

	private $transakce = NULL;

	private static $_count = 0;

	private static $errs = [ // tvrdy chyby pri kterych se to rovnou shodi
		self::CONNECTION_NOT_WORKING => '',
		self::SERVER_TOO_BUSSY => 'Databázový sever je příliš zatížen',
		self::GONE_AWAY => 'Databázový server je nedostupný'
	];

	public function __construct(
		$conn = NULL)
	{
		$this->conn = $conn;
	}

	public function startTransakce()
	{
		if($this->transakce){
			$this->transakce->startTransakce($this->conn);
		}else{
			$this->transakce = new OBE_DBErrors($this->conn);
		}
	}

	public function finishTransakce()
	{
		if($this->transakce){
			$this->cache = array_merge($this->cache, $this->transakce->getErrors());

			$this->transakce->finishTransakce();
		}
		$this->transakce = NULL;
	}

	public function check(
		$sSql = null,
		$res = null,
		$elapsed = null)
	{
		if($error = $this->getError()){
			self::$_count++;

			$errno = $this->getErrNo();

			if(isset(self::$errs[$errno])){
				$this->terminate(self::$errs[$errno]);
			}

			$this->addErr($sSql, $error, $errno);

			OBE_Log::logQuery($sSql, $res, $elapsed, $error);

			if($this->transakce){
				return true;
			}else{
				throw new OBE_DBException($error, $errno);
			}
		}
		return false;
	}

	public function addErr(
		$sql,
		$err,
		$eno = NULL)
	{
		if($this->transakce){
			$this->transakce->addErr($err, $eno);
		}else{
			$this->cache[] = [
				$sql,
				$err,
				$eno
			];
			$this->count = count($this->cache);
		}
	}

	public function anyErrors()
	{
		if($this->transakce){
			return $this->transakce->anyErrors();
		}
		return ($this->count > 0);
	}

	public function getError()
	{
		return mysqli_error($this->conn);
	}

	public function getErrNo()
	{
		return mysqli_errno($this->conn);
	}

	public function getErrors()
	{
		$cache = $this->cache;
		if($this->transakce){
			$cache = array_merge($cache, $this->transakce->getErrors());
		}
		return $cache;
	}

	public function getLastError()
	{
		if($this->transakce){
			return $this->transakce->getLastError();
		}else{
			if($error = end($this->cache)[1]){
				return $error;
			}
		}
		return NULL;
	}

	public function count()
	{
		return self::$_count;
	}

	public function terminate(
		$message = '')
	{
		if(OBE_Core::$debug){
			die('Terminated due to: ' . $message);
		}
		die('We seem to be having database issues. We are sorry for the inconvenience.');
	}
}

class OBE_BDErrorsDebugPanel extends OBE_DebugPanel{

	public function __construct(
		$id = '')
	{
		parent::__construct('sql-errors');
		$this->head = 'Sql Err`s';
	}

	public function getContent()
	{
		$cnt = '<table><tr><th>Sql</th><th>err str</th><th>err no</th></tr>';
		$errs = OBE_App::$db->errors->getErrors();
		foreach($errs as $e){
			$cnt .= '<tr><td>' . SqlFormatter::format($e[0]) . '</td><td>' . $e[1] . '</td><td>' . $e[2] . '</td></tr>';
		}
		$cnt .= '</table>';
		return $cnt;
	}

	public function getLabel()
	{
		return 'Sql Err`s: ' . ((OBE_App::$db) ? OBE_App::$db->errors->count() : 'null');
	}
}