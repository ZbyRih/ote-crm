<?php

class ListAction{

	const NONE = 'none';

	const SELECT = 'select';

	const VISIBLE = 'show';

	const HIDE = 'hide';

	const EDIT = 'edit';

	const DELETE = 'delete';

	const MOVE_UP = 'mup';

	const MOVE_DOWN = 'mdown';

	const MOVE_TO = 'moveTo';

	const M_HIDE = 'm_hide';

	const M_VISIBLE = 'm_show';

	const M_DELETE = 'm_delete';

	static $titles = [
		self::NONE => 'Žádná akce není k dispozici',
		self::SELECT => 'Vybrat',
		ListAction::VISIBLE => 'Zobrazit',
		ListAction::HIDE => 'Skrýt',
		ListAction::EDIT => 'Upravit',
		ListAction::DELETE => 'Smazat',
		ListAction::MOVE_UP => 'Přesunout výš',
		ListAction::MOVE_DOWN => 'Přesunout níž',
		ListAction::MOVE_TO => 'Přesunout na'
	];

	static $access = [
		self::NONE => FormFieldRights::DISABLE,
		ListAction::SELECT => FormFieldRights::VIEW,
		ListAction::VISIBLE => FormFieldRights::EDIT,
		ListAction::HIDE => FormFieldRights::EDIT,
		ListAction::EDIT => FormFieldRights::EDIT, // edit neni typicka akce, ta se odchytava a menu view
		ListAction::DELETE => FormFieldRights::DELETE,
		ListAction::MOVE_UP => FormFieldRights::EDIT,
		ListAction::MOVE_DOWN => FormFieldRights::EDIT,
		ListAction::MOVE_TO => FormFieldRights::EDIT
	];

	static $icons = [
		self::NONE => '',
		self::SELECT => '',
		self::VISIBLE => 'fa fa-thumbs-o-down',
		self::HIDE => 'fa fa-thumbs-up',
		self::EDIT => 'fa fa-edit',
		self::DELETE => 'md md-delete',
		self::MOVE_UP => 'fa fa-arrow-circle-o-up',
		self::MOVE_DOWN => 'fa fa-arrow-circle-o-down',
		self::MOVE_TO => 'fa fa-expand'
	];

	static $masses = [
		ListAction::VISIBLE => self::M_HIDE,
		ListAction::HIDE => self::M_VISIBLE,
		ListAction::DELETE => self::M_DELETE
	];

	public $action = '';

	public $title = NULL;

	public $right = NULL;

	public $mass = NULL;

	public $indent = false;

	public $icon = NULL;

	public function __construct($action){
		$this->action = $action;
		$this->indent = false;
		if(isset(static::$titles[$action])){
			$this->title = self::$titles[$action];
			$this->right = self::$access[$action];
			$this->mass = (isset(self::$masses[$action])) ? self::$masses[$action] : NULL;
			$this->indent = ($action == self::DELETE && $this->right == FormFieldRights::DELETE);
			$this->icon = self::$icons[$action];
		}
	}

	public function getMassTitle(){
		if($this->action == self::HIDE){
			return self::$titles[self::VISIBLE];
		}else if($this->action == self::VISIBLE){
			return self::$titles[self::HIDE];
		}else{
			$mass2normal = array_flip(self::$masses);
			if(isset($mass2normal[$this->mass])){
				return self::$titles[$mass2normal[$this->mass]];
			}else{
				return $this->mass;
			}
		}
	}

	public function setTitle($title){
		$this->title = $title;
		return $this;
	}

	public function setIcon($icon){
		$this->icon = $icon;
		return $this;
	}

	public function setRight($right){
		$this->right = $right;
		return $this;
	}

	public function setMass($mass){
		$this->mass = $mass;
		return $this;
	}
}