<?php


/**
 * nacita serializovane promene z databaze
 */
class OBE_VarLoader{

	var $data = NULL;

	// promenna v niz skonci data
	var $table_name = NULL;

	// nazev tabulky mutaci
	var $data_row = NULL;

	// nazev sloupce s hodnotou
	var $id_row = NULL;

	// nazev sloupce s klicem

	/**
	 * constructor
	 * @param $table_name - nazev tabulky
	 * @param $data_row_name - nazev sloupce s promenou
	 * @param $id_row_name - nazev sloupce klice
	 */
	function __construct($table_name = NULL, $data_row_name = NULL, $id_row_name = NULL){
		$this->Init($table_name, $data_row_name, $id_row_name);
	}

	/**
	 * inicializace
	 * @param $table_name - nazev tabulky
	 * @param $data_row_name - nazev sloupce s promenou
	 * @param $id_row_name - nazev sloupce klice
	 */
	function Init($table_name, $data_row_name, $id_row_name){
		$this->table_name = $table_name;
		$this->data_row = $data_row_name;
		$this->id_row = $id_row_name;
		$this->data = NULL;
	}

	/**
	 * funkce _load - interni funkce pro nacteni
	 * @param $id_value - hodnota sloupce klice
	 * @param $key_row_name - nazev sloupce kde je klic pro promenou $data
	 */
	function Load($id_value, $key_row_name = ''){
		if(!empty($key_row_name)){
			$key_row = ", t." . $key_row_name . " ";
		}else{
			$key_row = ", '' AS `key`";
		}

		$sql = "SELECT t." . $this->data_row . " " . $key_row . "
				FROM " . $this->table_name . " AS t
				WHERE t." . $this->id_row . " = '" . $id_value . "'";

		$result = OBE_App::$db->query($sql);
		if($result){
			list($blob, $key) = OBE_App::$db->fetch_row($result);
			if(!empty($blob)){
				if(!empty($key_row_name) && !empty($key)){
					$this->data[$key] = unserialize($blob);
				}else{
					$this->data = unserialize($blob);
				}
				return true;
			}
		}
		return false;
	}

	function Save($id_value, $bInsert = NULL, $key_name = ''){
		if(!empty($key_name)){
			$blob = serialize($this->data[$key_name]);
		}else{
			$blob = serialize($this->data);
		}
		if($bInsert === NULL){
			if(OBE_App::$db->fetchSingleColumn(
				'SELECT count(t.' . $this->id_row . ') AS num FROM ' . $this->table_name . ' AS t WHERE t.' . $this->id_row . " = '" . $id_value . "'", 'num') > 0){
				return OBE_App::$db->Update($this->table_name, [
					$this->data_row => $blob
				], [
					$this->id_row . '=\'' . $id_value . '\''
				]);
			}
		}else if(!$bInsert){
			return OBE_App::$db->Update($this->table_name, [
				$this->data_row => $blob
			], [
				$this->id_row . '=\'' . $id_value . '\''
			]);
		}
		return OBE_App::$db->Insert($this->table_name, [
			$this->data_row => $blob,
			$this->id_row => $id_value
		]);
	}

	function SaveD($id_value, $data){
		$this->data = $data;
		return $this->Save($id_value);
	}
}