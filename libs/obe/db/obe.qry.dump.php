<?php

class OBE_QryDumpItem{
	public $sql = null;
	public $res = null;
	public $elapsed = null;
	public $rows = null;

	public function __construct($sql, $res, $elapsed, $rows){
		$this->sql = $sql;
		$this->res = $res;
		$this->elapsed = $elapsed;
		$this->rows = $rows;
	}

	public function getSql(){
		return SqlFormatter::format($this->sql);
	}
}

class OBE_QryDump extends OBE_Array{

}