<?php

namespace App\Extensions\Components;

use App\Extensions\Utils\Html;

class HtmlTable extends Html{

	private $headCells;

	private $footCells;

	private $bodyRows;

	private $cols;

	public function __construct()
	{
		$this->setName('table');
		$this->headCells = [];
		$this->bodyRows = [];
		$this->footCells = [];
	}

	public function addRow()
	{
		$this->bodyRows[] = $e = new HtmlTableRow();
		return $e;
	}

	public function addHeadCol(
		$label)
	{
		$this->headCells[] = $e = Html::el('th')->addText($label);
		return $e;
	}

	public function addFootCol(
		$label)
	{
		$this->footCells[] = $e = Html::el('td')->addText($label);
		return $e;
	}

	public function getCol(
		$index)
	{
		if(array_keys($this->cols, $index)){
			return $this->cols[$index];
		}
		return $this->cols[$index] = Html::el('col');
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nette\Utils\Html::removeChildren()
	 */
	public function removeChildren()
	{
		parent::removeChildren();
		$this->headCells = [];
		$this->bodyRows = [];
		$this->footCells = [];
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Nette\Utils\Html::render()
	 */
	public function render(
		$indent = null)
	{
		parent::removeChildren();

		if($h = $this->compile(Html::el('thead'), $this->headCells)){
			$this->addHtml($h);
		}

		$body = Html::el('tbody');
		if($this->bodyRows){
			foreach($this->bodyRows as $r){
				$body->addHtml($r->compile($this->headCells));
			}
			$this->addHtml($body);
		}

		if($f = $this->compile(Html::el('tfoot'), $this->footCells)){
			$this->addHtml($f);
		}

		return parent::render();
	}

	private function compile(
		$root,
		$childs)
	{
		if(!$childs){
			return null;
		}

		foreach($childs as $c){
			$root->addHtml($c);
		}

		return $root;
	}
}