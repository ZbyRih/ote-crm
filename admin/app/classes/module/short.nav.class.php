<?php


class ShortNavClass extends ViewElementClass{

	const alone = 0;

	const prvni = 1;

	const posledni = 2;

	const mezi = 3;

	public $cardTitle = true;

	public $prev = NULL;

	public $next = NULL;

	public $curr = NULL;

	public $list = [];

	public $defPos = self::mezi;

	public $key = NULL;

	public $sesKey = NULL;

	public $onHandle = [];

	/**
	 *
	 * @var ModuleUrlScope
	 */
	var $scope = NULL;

	public function __construct($type = NULL){
		parent::__construct('quick_nav');
	}

	/**
	 *
	 * @param ModuleUrlScope $scope
	 * @param string $key
	 * @param array $list
	 * @param integer $defPos
	 * @return self
	 */
	public function initShortNav($scope, $key, $list, $defPos = self::mezi){
		$this->scope = $scope;
		$this->key = $key;
		$this->scope->addExt($key);
		$this->sesKey = $this->scope->module . '_shn_' . $this->key;
		$this->list = $list;
		$this->defPos = $defPos;
		$this->setCurrentByPosition($defPos);
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see ViewElementClass::getElementView()
	 */
	public function getElementView(){
		$this->handle();
		if($this->cardTitle){
			$views->setTitle($this->list[$this->curr]);
		}
		return $this;
	}

	public function setCurrent($value){
		if(isset($this->list[$value])){
			$this->curr = $value;
		}
		return $this;
	}

	public function setCurrentByPosition($pos = self::mezi){
		$this->curr = NULL;
		if($pos == self::prvni){
			reset($this->list);
			$this->curr = key($this->list);
		}else if($pos == self::posledni){
			end($this->list);
			$this->curr = key($this->list);
		}else if($pos == self::mezi){
			$mid = floor(count($this->list) / 2);
			$second = array_slice($this->list, $mid + 1, NULL, true);
			reset($second);
			$this->curr = key($second);
		}
		return $this;
	}

	public function handle(){
		if($val = $this->scope->getExt($this->key)){
			$this->scope->delExt($this->key);
			OBE_Session::write($this->sesKey, $val);
			$this->setCurrent($val);
		}else if($val = OBE_Session::read($this->sesKey)){
			$this->setCurrent($val);
		}

		$keys = array_keys($this->list);
		$pos = array_search($this->curr, $keys);
		$typ = self::mezi;

		if($pos == 0){
			$typ = self::prvni;
			if(count($keys) <= 1){
				$typ = self::alone;
			}
		}else if($pos == (count($keys) - 1)){
			$typ = self::posledni;
		}

		if($typ == self::posledni){ //posledni prvek
			$this->prev = $keys[$pos - 1];
		}elseif($typ == self::prvni){ //prvni prvek
			$this->next = $keys[$pos + 1];
		}elseif($typ == self::mezi){ //mezi
			$this->prev = $keys[$pos - 1];
			$this->next = $keys[$pos + 1];
		}
		if(!empty($this->onHandle)){
			foreach($this->onHandle as $h){
				call_user_func($h, $this->curr);
			}
		}
	}
}