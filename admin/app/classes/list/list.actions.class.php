<?php


class ListActionsClass extends CallBackControlClass{

	static $Tpl = 'list.actions2.tpl';

	static $defaultOrder = [
		ListAction::EDIT,
		ListAction::SELECT,
		ListAction::NONE
	];

	var $avaibleActions = [];

	var $actions = [];

	var $default = ListAction::NONE;

	var $numIconized = 0;

	var $bHaveMassActions = false;

	var $bDisableMassActions = false;

	/**
	 *
	 * @var ActionsModulListRightsClass $rightsObj
	 */
	var $rightsObj = NULL;

	/**
	 * constructor - NO! Realy?
	 * @param ActionsModulListRightsClass $rightsObj
	 * @param Array $callBacks
	 * @return void
	 */
	function __construct($rightsObj = NULL, $callBacks = NULL, $callParent = NULL){
		parent::__construct($callParent, $callBacks);
		if($rightsObj === NULL){
			$rightsObj = new ActionsModulListRightsClass();
		}
		$this->avaibleActions = $rightsObj->filterActions(ListAction::$access);
		$this->rightsObj = $rightsObj;
	}

	function resetActions($newActions = NULL, $bMassActions = true){
		$this->disableAllActions();
		$this->avaibleActions = $this->rightsObj->filterActions(ListAction::$access);
		$this->initAvaibleDefaultActions($newActions, $bMassActions);
		$this->setDefaultAction();
	}

	function disableAllActions(){
		$this->actions = NULL;
		$this->default = new ListAction(ListAction::NONE);
		$this->numIconized = 0;
		$this->bHaveMassActions = false;
	}

	function initAvaibleDefaultActions($actions = NULL, $bMassActions = true){
		$this->bDisableMassActions = !$bMassActions;

		$actions = MArray::AllwaysArray($actions);
		$keys = array_keys($this->avaibleActions);
		$avaibleActions = array_intersect($keys, $actions);

		foreach($avaibleActions as $action){
			$this->addAction($action);
		}
	}

	function initNonDefaultActions($allActions){
		$keys = array_keys(ListAction::$access);
		$userActions = array_diff($allActions, $keys);
		foreach($userActions as $userAction){
			parent::addCallBack($userAction, NULL);
		}
	}

	/**
	 *
	 * @param string $action
	 * @param ListAction $obj
	 * @return ListAction
	 */
	function addAction($action, $obj = NULL){
		$action = ($obj) ? $obj : new ListAction($action);

		if($this->rightsObj->isActionEnable($action->right)){
			if($this->bDisableMassActions){
				$action->mass = NULL;
			}

			$this->actions[$action->action] = $action;

			if(!empty($action->icon) && $action->action !== ListAction::NONE){
				$this->numIconized++;

				if(!empty($action->mass)){
					$this->bHaveMassActions = true;
				}
			}
		}
		return $action;
	}

	function setCallBack($key, $callBack = NULL){
		if($callBack === NULL && $this->defualtAction !== NULL){
			$callBack = $key;
			$key = $this->defualtAction;
		}

		if(!$this->exist($key)){
			parent::addCallBack($key, $callBack);
		}else{
			parent::setCallBack($key, $callBack);

			if(array_key_exists($key, ListAction::$masses)){
				if(!$this->isCallable($key)){
					parent::setCallBack(ListAction::$masses[$key], $callBack);
				}
			}
		}
	}

	function removeAction($key){
		unset($this->actions[$key]);
	}

	function del($key){
		unset($this->actions[$key]);
	}

	function setDefaultAction($defaultAction = NULL){

		if(isset($this->actions[$defaultAction])){
			if($this->rightsObj->isActionEnable($this->actions[$defaultAction]->right)){
				$this->default = $this->actions[$defaultAction];
				return;
			}
		}
		$this->default = $this->getNearestAvaibleDefaultAction();
	}

	function getNearestAvaibleDefaultAction(){
		$action = new ListAction(ListAction::NONE);
		foreach(self::$defaultOrder as $a){
			if($this->rightsObj->isActionEnable(ListAction::$access[$a]) && isset($this->actions[$a])){
				$action = $this->actions[$a];
			}
		}
		return $action;
	}

	function updateSizeHead($headSize){
		if($this->numIconized > 0){
			$headSize++;
		}
		if($this->bHaveMassActions){
			$headSize++;
		}
		return $headSize;
	}

	/**
	 *
	 * @param string $action
	 * @return ListAction
	 */
	function get($action){
		if(isset($this->actions[$action])){
			return $this->actions[$action];
		}
		return NULL;
	}

	function setAction($action, $obj){
		$this->actions[$action] = $obj;
	}

	function getForSmarty(){
		$bHasMass = false;
		foreach($this->actions as $a){
			if($a->mass){
				$bHasMass = true;
			}
		}
		return [
			'actions' => $this->actions,
			'default' => $this->default,
			'bIcons' => ($this->numIconized > 0),
			'Tpl' => self::$Tpl,
			'bHasMass' => $bHasMass,
			'bHasMove' => (isset($this->actions[ListAction::MOVE_DOWN]) || isset($this->actions[ListAction::MOVE_DOWN])),
			'colsnum' => ((($this->numIconized > 0) ? 1 : 0) + ($bHasMass ? 1 : 0))
		];
	}
}