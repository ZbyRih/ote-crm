<?php

namespace App\Extensions\Abstracts;

use App\Extensions\Utils\Strings;

class NumberSeries{

	private $tbl;

	private $column;

	private $default;

	private $prefix;

	public function __construct(Table $tbl, $column, $default, $prefix = ''){
		$this->tbl = $tbl;
		$this->column = $column;
		$this->default = $default;
		$this->prefix = $prefix;
	}

	public function next($year){
		$col = 'MAX(`' . $this->column . '`)';

		if($r = $this->tbl->select($col)
			->where($this->column . ' LIKE ?', '%' . $year . '%')
			->fetch()){
			if($r[$col]){
				return $r[$col] + 1;
			}
		}

		if(Strings::startsWith($this->default, $this->prefix . $year)){
			return $this->default + 1;
		}

		return $this->prefix . $year . '00001';
	}
}