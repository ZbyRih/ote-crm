<?php

namespace App\Extensions\Components;

use App\Extensions\Utils\Html;

class HtmlDataListRow{

	/** @var Html */
	private $dt;

	/** @var Html */
	private $dd;

	/** @var string */
	private $label;

	/** @var string */
	private $data;

	public function __construct(){
		$this->dt = Html::el('dt');
		$this->dd = Html::el('dd');
	}

	public function label($label){
		$this->label = $label;
		return $this->dt;
	}

	public function data($data){
		$this->data = $data;
		return $this->dd;
	}

	public function compile(){
		return [
			$this->dt->addHtml($this->label),
			$this->dd->addHtml($this->data)
		];
	}
}