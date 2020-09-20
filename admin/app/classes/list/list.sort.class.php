<?php

 class ListSortClass{
 	const SORT_KEY = 'sort';
 	const NONE = '0';
 	const UP = '1';
 	const DOWN = '2';

 	/**
 	 *
 	 * @var ListClass
 	 */
 	var $listObj;

 	/**
 	 * Sloupce definovane pro sort
 	 * @var Array
 	 */
 	var $sort = [];

 	var $session = NULL;

 	/**
 	 *
 	 * @param ListClass $listObj
 	 * @param Array $sortRows
 	 */
 	public function __construct($listObj, $sort){

 		$this->listObj = $listObj;

 		$this->session = $this->listObj->getRealUID();

 		if(!($sort_ses = OBE_Session::read($this->session))){
 			$sort_ses = [];
 		}

 		$action = null;
 		if($listObj->checkListAction()){
 			$action = OBE_Http::getGet(self::SORT_KEY);
 			if($action){
	 			$val = self::NONE; // default
	 			if(isset($sort_ses[$action])){
	 				$val = $sort_ses[$action];
	 			}
	 			$sort_ses[$action] = $this->handleVal($val);
 			}
 			foreach($sort_ses as $k => $v){
	 			if($v != self::UP && $v != self::DOWN){
	 				unset($sort_ses[$k]);
	 			}
 			}
 		}

		if(!empty($sort)){

			$rcols = [];
			$ocols = [];

			foreach($sort as $k => $col){
				$dir = -1;
				if(is_numeric($k)){
					$realCol = $listObj->getRealColName($col);
				}else{
					$realCol = $listObj->getRealColName($k);
					if($realCol != $action && empty($sort_ses)){
						if($col == 'DESC'){
							$dir = self::DOWN;
						}else{
							$dir = self::UP;
						}
					}
				}
				$rcols[$realCol] = $realCol;
				if(!isset($sort_ses[$realCol])){
					$ocols[$realCol] = $dir;
				}
			}

			$sort_ses = array_intersect_key($sort_ses, $rcols);

			$this->sort = array_merge($sort_ses, $ocols);
		}
 	}

 	public function handleVal($val){
 		if($val == self::UP){
 			return self::DOWN;
 		}else if($val == self::DOWN){
 			return self::NONE;
 		}else{
 			return self::UP;
 		}
 	}

 	public function save(){
 		OBE_Session::write($this->session, $this->sort);
 	}

 	public function getSortItem($col){
 		if(isset($this->sort[$col])){
 			return $this->sort[$col];
 		}
 		return false;
 	}

 	/**
 	 *
 	 * @param Array $modelSql
 	 */
 	public function updateOrderForModel($order){
 		$sort = [];
 		$forder = []; // filtrovane

 		foreach($order as $col){
 			if(!isset($this->sort[$col])){
 				$forder[] = $col;
 			}
 		}

		$dsort = []; // potlaceny defaultni sort
 		foreach($this->sort as $col => $ind){
 			$regs = [];
 			if(ModelHelper::MoreThanModelAndField($col, $regs)){
 				if($regs[1] == 'DATE_FORMAT('){
 					$col = (!empty($regs[2])? $regs[2] . '.' : '') . $regs[3];
 				}
 			}

 			if($ind == self::UP){
 				$sort[$col] = 'ASC';
 			}elseif($ind == self::DOWN){
 				$sort[$col] = 'DESC';
 			}else{
 				$dsort[] = $col;
 			}
 		}
 		return array_merge($sort, $dsort, $forder);
 	}
}