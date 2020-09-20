<?php

class OBE_DbLog{
	const TABLE = 'log_errors';
	/**
	 * stav logovani on/off
	 *
	 * @var boolean
	 */
	var $bLogingEnable = false;
	/**
	 * stav logovaci tabulky existuje/neexistuje
	 *
	 * @var boolean
	 */
	var $bTableExist = false;
	/**
	 * Cil logovani do databaze/dosouboru
	 *
	 * @var boolean
	 */
	static $sessionId = 0;
	static $lineSeparator = "\n";
	var $bLogingProcess = false;

	function __construct(){
		if($this->isTableExist()){
			self::$sessionId = $this->getLastSessionId() + 1;
			$this->bLogingEnable = true;
		}else{
			if($this->createTable()){
				$this->bLogingEnable = true;
			}
		}
	}

	function isTableExist(){
		return OBE_App::$db->query('DESCRIBE ' . self::TABLE);
	}

	function getLastSessionId(){
		return OBE_App::$db->fetchSingleColumn('SELECT max(sessionid) as lastses FROM ' . self::TABLE);
	}

	function createTable(){
		return OBE_App::$db->query(
			"CREATE TABLE " . self::TABLE . " (id INT(19) NOT NULL AUTO_INCREMENT, PRIMARY KEY (id), query TEXT, result TINYINT(1), time DATETIME, trace TEXT, source varchar(5), sessionid INT(19))");
	}

	function write($lines, $stack, $error, $result = 0, $elapsed = null){
		if(!$this->bLogingProcess && $this->bLogingEnable){
			$this->bLogingProcess = true;
			if(is_array($lines)){
				$logText = implode(self::$lineSeparator, $lines);
			}else{
				$logText = $lines;
			}
			$stack = implode(self::$lineSeparator, $stack);
			$name = (isset(OBE_AppCore::$conf['name']))? OBE_AppCore::$conf['name'] : __FILE__;
			OBE_App::$db->Insert(self::TABLE, [
				  'query' => $logText
				, 'result' => (int)$result
				, 'elapsed' => (($elapsed)? $elapsed: 0)
				, 'time' => 'NOW()'
				, 'source' => mb_substr($name, 0, 10)
				, 'trace' => $stack
				, 'error' => $error
				, 'sessionid' => self::$sessionId
			]);
			$this->bLogingProcess = false;
		}
	}

	function disable(){
		$this->bLogingEnable = false;
	}

	function enable(){
		$this->bLogingEnable = true;
	}
}