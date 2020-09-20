<?php


class ModuleUrlScope{

	/**
	 *
	 * @var string
	 */
	var $module = null;

	// module key
	/**
	 *
	 * @var string
	 */
	var $view = null;

	// view
	/**
	 *
	 * @var integer
	 */
	var $recordId = null;

	// mainrecord id
	/**
	 *
	 * @var string
	 */
	var $action = null;

	// action
	/**
	 *
	 * @var array
	 */
	var $static = [];

	/**
	 *
	 * @var array
	 */
	var $ext = [];

	/**
	 *
	 * @var ModuleUrlScope
	 */
	var $parent = null;

	/**
	 *
	 * @var array
	 */
	var $carry = null;

	/**
	 *
	 * @var ModuleInfoClass
	 */
	var $info = null;

	/**
	 *
	 * @param integer $module module key
	 * @param ModuleUrlScope $parent
	 */
	public function __construct($module, $parent = null){
		$this->module = $module;
		$this->parent = $parent;
		$this->handle();
	}

	public function add($new){
		$this->static[$new] = OBE_Http::getGet($new);
	}

	public function get($static){
		return $this->static[$static];
	}

	public function addExt($new){
		$this->ext[$new] = OBE_Http::getGet($new);
	}

	public function getExt($static){
		if(isset($this->ext[$static])){
			return $this->ext[$static];
		}
		return null;
	}

	public function delExt($key){
		unset($this->ext[$key]);
	}

	public function setStatic($key, $value){
		$this->static[$key] = $value;
	}

	public function setRecId($recordId){
		$this->recordId = $recordId;
		return $this;
	}

	public function setCarry($key, $val){
		$this->carry[$key] = $val;
	}

	public function getCarry($key){
		if(isset($this->carry[$key])){
			return $this->carry[$key];
		}
		return null;
	}

	public function getView($view = null){
		$this->setView($view);
		return $this->view;
	}

	public function setView($view = null){
		if($view){
			$this->view = $view;
		}
		return $this;
	}

	public function unsetView(){
		$this->view = null;
		return $this;
	}

	public function getViewKey(){
		return $this->module . 'v';
	}

	public function getRecKey(){
		return $this->module . 'r';
	}

	public function getActionKey(){
		return $this->module . 'a';
	}

	public function setAction($action){
		$this->action = $action;
	}

	public function handle(){
		$this->view = OBE_Http::getGet($this->module . 'v');
		$this->recordId = OBE_Http::getGet($this->module . 'r');
		$this->action = OBE_Http::getGet($this->module . 'a');
		return $this;
	}

	/**
	 * return string - parent + static cast linku
	 */
	public function getStaticLink($view = null){
		$link = $this->getParentLinkAmp() . $this->statLink($view);
		return rtrim($link, '&');
	}

	public function getAjaxSel($link){
		return 'ajax=select&' . $link;
	}

	public function getLinkView($view){
		return $this->getParentLinkAmp() . $this->statLink($view) . '&' . $this->dynLink($this->recordId);
	}

	/**
	 * return string - parent + static + dynamic(recordid, action) automaticky vnitrni
	 */
	public function getLink($action = null){
		if(!$action){
			$action = $this->action;
		}
		$link = $this->getParentLinkAmp() . $this->statLink() . '&' . $this->dynLink($this->recordId, $action);
		return rtrim($link, '&');
	}

	/**
	 * return string - parent + static + dynamic(recordid, action) automaticky vnitrni
	 */
	public function getLinkExt($eKey = null, $eValue = null, $action = null){
		$link = $this->getParentLinkAmp() . $this->statLink() . '&' . $this->dynLink($this->recordId, $action);
		$elink = $this->extLink($eKey, $eValue);
		return (!empty($elink)) ? rtrim($link, '&') . '&' . $elink : rtrim($link, '&');
	}

	/**
	 * return string - parent + static + dynamic(recordid, action) podle vstupu
	 */
	public function getDynLink($recordId = null, $action = null){
		$link = $this->getParentLinkAmp() . $this->statLink() . '&' . $this->dynLink($recordId, $action);
		return rtrim($link, '&');
	}

	public function getParentLink(){
		return rtrim($this->getParentLinkAmp(), '&');
	}

	public function getModulLink(){
		if($this->module){
			return (($this->parent) ? $this->module : k_module) . '=' . $this->module;
		}
		return null;
	}

	public function getRecordLink(){
		if($this->recordId || $this->recordId === '0' || $this->recordId === 0){
			return $this->module . 'r=' . $this->recordId;
		}
		return null;
	}

	/**
	 * return string - parent cast linku
	 */
	private function getParentLinkAmp(){
		$link = '';
		if($this->parent){
			$link = $this->parent->getLink() . '&';
		}
		return $link;
	}

	private function statLink($view = null){
		$link = '';
		if($this->module){
			$link .= (($this->parent) ? $this->module : k_module) . '=' . $this->module . '&';
		}
		if($this->view || $view){
			$link .= $this->module . 'v=' . (($view) ? $view : $this->view) . '&';
		}
		if(!empty($this->static)){
			foreach($this->static as $key => $val){
				$link .= $key . '=' . $val . '&';
			}
		}
		return rtrim($link, '&');
	}

	private function dynLink($recordId = null, $action = null){
		$link = '';
		if($recordId || $recordId === '0' || $recordId === 0){
			$link .= $this->module . 'r=' . $recordId . '&';
		}
		if($action){
			$link .= $this->module . 'a=' . $action . '&';
		}
		return rtrim($link, '&');
	}

	private function extLink($eKey = null, $eVal = null){
		$link = '';
		$ext = $this->ext;
		if($eKey && ($eVal || $eVal === '0' || $eVal === 0)){
			$ext[$eKey] = $eVal;
		}
		if(!empty($ext)){
			foreach($ext as $key => $val){
				if($val || $val === '0' || $val === 0){
					$link .= $key . '=' . $val . '&';
				}
			}
		}
		return rtrim($link, '&');
	}

	public function isEmptyRecId(){
		return !($this->recordId || $this->recordId === '0' || $this->recordId === 0);
	}

	public function isRecId(){
		return ($this->recordId || $this->recordId === '0' || $this->recordId === 0);
	}

	public function isSetRecId(){
		return OBE_Http::issetGet($this->module . 'r');
	}

	public function isAction($action){
		return ($this->action == $action);
	}

	public function getFullSetLink($module, $view, $record, $action){
		$name = AdminApp::$modules->getModuleName($module);
		$scope = new ModuleUrlScope($name, null);
		$scope->setView($view);
		$scope->static = $this->static;
		return $scope->getDynLink($record, $action);
	}

	public function resetViewByRedirect($recordId = null, $view = null){
		$this->setView($view);
		AdminApp::Redirect($this->getDynLink($recordId));
	}

	public function goToViewByRedirect($view = null){
		$this->setView($view);
		AdminApp::Redirect($this->getDynLink());
	}

	public function resetViewWithRecByRedirect(){
		AdminApp::Redirect($this->getDynLink($this->recordId));
	}

	public function getMasterId(){
		if($this->parent){
			return $this->parent->getMasterId();
		}
		return $this->recordId;
	}

	/**
	 *
	 * @return ModuleUrlScope
	 */
	public function getMaster(){
		if($this->parent){
			return $this->parent->getMaster();
		}
		return $this;
	}
}