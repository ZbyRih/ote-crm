<?php

use App\Components\IViewElement;


class ViewElementClass implements IViewElement{

	private static $_uID = 0;

	var $uID = NULL;

	var $data = NULL;

	var $type = 'raw';

	var $name = NULL;

	public function __construct($type = 'raw'){
		$this->uID = ++self::$_uID;

		if($type !== NULL){
			$this->type = $type;
		}
	}

	public function getElementView(){
		return $this;
	}

	function setName($name){
		$this->name = $name;
		return $this;
	}
}

class NewCardElement extends ViewElementClass{

	public function __construct($type = 'new_card'){
		parent::__construct('new_card');
	}
}

class LinkViewElement extends ViewElementClass{

	var $fraze = '';

	var $link = '';

	var $ico = NULL;

	var $confirm = false;

	var $popup = false;

	public function __construct($type = NULL){
		parent::__construct('link');
	}

	public function init($link, $fraze, $ico = NULL, $confirm = false, $popup = false){
		$this->link = $link;
		$this->fraze = $fraze;
		$this->ico = $ico;
		$this->confirm = $confirm;
		$this->popup = $popup;
	}
}

class AjaxSelectViewElement extends ViewElementClass{

	var $data = NULL;

	public function __construct($type = NULL){
		parent::__construct('ajax');
	}

	public function init($selModul, $saveLink, $fraze, $class = null, $ico = null){
		$this->data = [
			'selLink' => '?ajax=select&module=select&frommodule=' . $selModul,
			'saveLink' => $saveLink,
			'fraze' => $fraze,
			'class' => $class,
			'ico' => $ico
		];
	}
}

class PackElement{

	public $elm;

	public $size;

	public function __construct($elm, $size){
		$this->elm = $elm;
		$this->size = $size;

	}
}

class PackBreak{

	public $title;

	public function __construct($title){
		$this->title = $title;
	}
}

class PacketViewElement extends ViewElementClass{

	public $elements = [];

	public $float = true;

	public function __construct($type = NULL){
		parent::__construct('packet');
	}

	public function init($float){
		$this->float = $float;
		return $this;
	}

	public function add($elm, $size = 12){
		if(is_subclass_of($elm, 'ViewElementClass') || is_a($elm, 'ViewElementClass')){
			$this->elements[] = new PackElement($elm->getElementView(null), $size);
		}else{
			throw new OBE_Exception('ModulViewsManager::getView ' . var_export($elm, true) . ' není zdenen od ViewElementClass');
		}
	}

	public function addBreak($title = ''){
		$this->elements[] = new PackBreak($title);
	}

	function setNames($name){
		foreach($this->elements as $e){
			$e->setBlockName($blockName);
		}
	}
}

class StatusViewElement extends ViewElementClass{

	var $statuses = NULL;

	public function __construct($type = NULL){
		parent::__construct('stats');
	}

	public function init($name, $statuses){
		$this->name = $name;
		$this->statuses = $statuses;
	}
}

class StatsViewElement extends ViewElementClass{

	const GRAPH = 'graph';

	const LISTV = 'list';

	var $statType;

	const LINE = 'line';

	const BAR = 'bar';

	var $graphType = self::LINE;

	public function __construct($type = NULL){
		parent::__construct('statistics');
	}

	public function init($name, $data, $type){
		$this->name = $name;
		$this->statType = $type;
		$this->graphType = self::LINE;
	}
}