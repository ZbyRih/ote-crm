<?php

class MultiListPickFieldClass extends ListFieldClass{

	public $link = null;
	public $select = null;

	function setLink($link){
		$this->link = '?' . $link;
		$this->handleAccessPre();
	}

	function setSelect($link){
		$this->select = '?' . $link;
		$this->handleAccessPre();
	}

	function handleAccessPre(){
		parent::handleAccessPre();
		$this->data['link'] = $this->link;
		$this->data['select'] = $this->select;
	}
}