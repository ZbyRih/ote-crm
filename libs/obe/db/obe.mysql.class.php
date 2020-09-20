<?php
use Nette\Database\ResultSet;
use Nette\Database\Table\ActiveRow;

// use Nette\Bridges\DatabaseDI\DatabaseExtension;
class MysqlErrors{

	public $errors = [];

	public function clear()
	{
		$this->errors = [];
	}

	public function getErrors()
	{
		return $this->errors;
	}
}

/**
 * pripojeni na mysql databazi a rozhrani mysqli_
 * @author Olda
 */
class OBE_MySql{

	/** @var Nette\Database\Connection */
	private $ndb;

	public $errors;

	public function __construct(
		$dbConfig = NULL)
	{
		$this->errors = new MysqlErrors();
	}

	public function setNDB(
		Nette\Database\Connection $conn)
	{
		$this->ndb = $conn;
	}

	/**
	 * Připojení k databázi :)
	 * @param Array $dbConfig - nazev databaze
	 * @return boolean - true if succes, else false
	 */
	public function connect(
		$dbConfig)
	{
		return $this;
	}

	/**
	 * funkce provadi prikaz sql prikazy vrati to co vraci mysqli_query :)
	 * , s priznakem debug vypise sql dotaz a pripadne chyby
	 * @param string $sql
	 * @param boolean $debug
	 * @return ResultSet
	 */
	public function query(
		$sql,
		$bDebug = false)
	{
		$res = false;
		$this->errors->clear();
		try{
			$res = $this->ndb->query($sql);
		}catch(PDOException $e){
			$this->errors->errors[] = $e->getMessage();
		}
		return $res;
	}

	/**
	 * @param ResultSet $result
	 * @param Boolean $mode - true(fetch row)/false(fetch assoc)
	 * @return ActiveRow
	 */
	public function fetch_array(
		$result,
		$mode = false)
	{
		if($d = $result->fetch()){
			return $mode ? array_values((array) $d) : (array) $d;
		}
		return null;
	}

	/**
	 * @param ResultSet $result
	 * @param Boolean $mode - true(fetch row)/false(fetch assoc)
	 * @return ActiveRow
	 */
	public function fetch_row(
		$result)
	{
		if($d = $result->fetch()){
			return array_values((array) $d);
		}
		return null;
	}

	public function fetch_single_column(
		$result,
		$column)
	{
		if($r = $result->fetch()){
			return $r[$column];
		}
		return NULL;
	}

	public function fetch_object(
		$result)
	{
		return $result->fetch();
	}

	private function last_insert_id()
	{
		return $this->ndb->getInsertId();
	}

	public function getLastInsertId()
	{
		return $this->ndb->getInsertId();
	}

	public static function escape_string(
		$str)
	{
		return $str;
	}
}