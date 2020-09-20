<?php

define('PAGE_KEY', 'page');

class MLPagerClass{
	/**
	 * @var ModelClass
	 */
	var $model = NULL;
	var $pageKey = NULL;
	var $page = 1;
	var $itemsNum = 0;
	var $itemsOnPage = 0;
	/**
	 *
	 * @param ModelClass $model
	 * @param Integer $itemsOnPage
	 * @return void
	 */
	function __construct($model, $itemsOnPage, $listRows, $conditions = []){
		$this->pageKey = $model->name . '_' . PAGE_KEY;
		if(isset($_GET[$this->pageKey])){
			$this->_setSession($_GET[$this->pageKey]);
		}else{
			$this->page = $this->_getSession($this->page);
		}
		$this->itemsNum = $model->CountBy(NULL, NULL, $conditions, $listRows, []);
		if($this->itemsNum < ($itemsOnPage * $this->page)){
			$this->_setSession(ceil($this->itemsNum / $itemsOnPage));
		}
		$this->itemsOnPage = $itemsOnPage;
	}

	function _setSession($num){
		if($num < 1){
			$num = 1;
		}
		OBE_Session::write($this->pageKey, $num);
		$this->page = $num;
	}

	function _getSession(){
		if(OBE_Session::exists($this->pageKey)){
			if(OBE_Session::read($this->pageKey) < 1){
				OBE_Session::write($this->pageKey, 1);
			}
			return OBE_Session::read($this->pageKey);
		}
		return 1;
	}

	function getPages(){
		$first = 1;
		$prev = false;
		$last = ceil($this->itemsNum / $this->itemsOnPage);
		$next = false;
		$bHide = false;
		$pages = [];
		if($this->itemsNum > $this->itemsOnPage){
			if($this->page < 2){
				$first = false;
			}else{
				$prev = $this->page - 1;
			}
			for($i = 1; $i <= $last; $i++){
				if($this->page == $i){
					$pages[$i] = 1;
				}else{
					$pages[$i] = 0;
				}
			}
			if($last < 2 || $last == $this->page){
				$last = false;
			}else{
				$next = $this->page + 1;
			}
		}else{
			$bHide = true;
		}

		return [
			   'first' => $first
			 , 'prev' => $prev
			 , 'pages' => $pages
			 , 'next' => $next
			 , 'last' => $last
			 , 'bHide' => $bHide
			 , 'paramKey' => $this->pageKey
		];
	}

	function getPage(){
		return $this->page;
	}

	function getSize(){
		return $this->itemsOnPage;
	}
}