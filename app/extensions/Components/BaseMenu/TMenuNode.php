<?php
namespace App\Extensions\Components\Menu;

trait TMenuNode{

	/** @var MenuNode[] */
	public $nodes = [];

	/** @var string */
	public $action;

	/** @var string */
	public $icon;

	/** @var MenuNode */
	public $parent;

	/** @var boolean */
	public $active;

	/** @var string */
	public $title;

	/** @var string */
	public $url;

	/** @var string */
	public $resource;

	/** @var string */
	public $priviledge;

	/** @var string */
	public $modul;

	protected function set($action, $title, $icon, $parent, $resource){
		$this->action = $action;
		$this->title = $title;
		$this->icon = ($icon ? $icon : 'fa fa-ban');
		$this->parent = $parent;
		$this->active = false;
		$this->priviledge = 'view';

		if($resource){
			$a = explode('_', $resource);
			if(count($a) > 1){
				list($this->resource, $this->priviledge) = $a;
			}else{
				$this->resource = $resource;
			}
		}

		if($action instanceof MenuItemLink){
			$a = explode(':', $action->modul);
		}else{
			$a = explode(':', $action);
		}
		$this->modul = $a[0];
	}

	public function addItem($action, $title, $icon, $resource = null){
		$this->add($action, $title, $icon, $resource);
		return $this;
	}

	public function addNode($action, $title, $icon, $resource = null){
		return $this->add($action, $title, $icon, $resource);
	}

	private function add($action, $title, $icon, $resource = null){
		$this->nodes[] = $n = new MenuNode($action, $title, $icon, $this, $resource);
		return $n;
	}
}