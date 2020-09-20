<?php


/**
 * rozhrani cached tables

 *
 */
class OBE_IDB extends OBE_DB{

	private $cacheTables = [];

	public function setTables($tables){
		$this->cacheTables = $tables;
	}

	public function InsertI($table, $values){
		return $this->Insert($table, $values);
	}

	/**
	 * Update data hodnotama z $values na zaznamy na ktere sedi $conditions v tabulce z cahcetables[$table]
	 * @param String $table klic tabulky
	 * @param $values
	 * @param $conditions
	 * @return
	 */
	function UpdateI($table, $values, $conditions = []){
		return $this->Update($table, $values, $conditions);
	}

	function DeleteI($fromTable, $conditions = []){
		$this->Delete($fromTable, $conditions);
	}
}