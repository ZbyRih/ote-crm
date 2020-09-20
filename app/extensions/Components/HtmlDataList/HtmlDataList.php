<?php

namespace App\Extensions\Components;

use App\Extensions\Utils\Html;

class HtmlDataList extends Html{

	/** @var [] */
	private $rows;

	public function __construct(){
		$this->setName('dl');
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see Html::removeChildren()
	 */
	public function removeChildren(){
		parent::removeChildren();
		$this->rows = [];
	}

	public function addRow(){
		$this->rows[] = $r = new HtmlDataListRow();
		return $r;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see Html::render()
	 */
	public function render($indent = null){
		parent::removeChildren();

		foreach($this->rows as $r){
			foreach($r->compile() as $e){
				$this->addHtml($e);
			}
		}

		return parent::render();
	}
}