<?php

use App\Components\IViewElement;


class ModulViewsManager{

	private $haveQN = false;

	var $head = [];

	var $main = [];

	var $foot = [];

	var $title = NULL;

	public function addToRight($item){
		$this->add($item, true);
	}

	public function add($item, $bOnRight = false){
		$newItem = $this->getView($item);

		if($newItem->type == 'tab'){
			$this->head[] = $newItem;
			return $item;
		}

		if(!$this->haveQN && ($newItem->type == 'quick_nav' || $newItem->type == 'model_quick_nav')){
			$this->head = array_merge([
				$newItem
			], $this->head);
			$this->haveQN = true;
			return $item;
		}

		if(!$bOnRight){
			$this->main[] = $newItem;
		}else{
			$lastItem = array_pop($this->main);
			if(is_subclass_of($lastItem, 'PacketViewElement')){
				$lastItem->add($newItem);
			}else{
				$pack = new PacketViewElement();
				$pack->add($lastItem);
				$pack->add($newItem);
				$lastItem = $pack;
			}
			$this->main[] = $lastItem;
		}

		return $item;
	}

	/**
	 *
	 * @param ModulViewsManager $views
	 */
	public function get($views){
		foreach($views->main as $m){
			$this->main[] = $m;
		}

	}

	public function dropAll(){
		$this->head = [];
		$this->main = [];
		$this->foot = [];
	}

	public function setTitle($title){
		if(!$this->title){
			$this->title = $title;
		}
	}

	/**
	 *
	 * @param ViewElementClass $obj
	 * @return Array
	 */
	private function getView($obj){
		if(is_object($obj)){
			//is_subclass_of($obj, 'ViewElementClass') || is_a($obj, 'ViewElementClass')
			if($obj instanceof IViewElement){
				return $obj->getElementView($this);
			}else{
				throw new OBE_Exception('ModulViewsManager::getView ' . var_export($obj, true) . ' není zdenen od ViewElementClass');
			}
		}else{
			throw new OBE_Exception('ModulViewsManager::getView ' . var_export($obj, true) . ' není object');
		}
		return NULL;
	}
}