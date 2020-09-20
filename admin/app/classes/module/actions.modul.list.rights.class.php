<?php

class ActionsModulListRightsClass{
	static $accessPriority = [FormFieldRights::DISABLE, FormFieldRights::VIEW, FormFieldRights::EDIT, FormFieldRights::DELETE];

	var $access;

	function __construct($globalAccess = FormFieldRights::DELETE){
		$this->access = $globalAccess;
	}

	function filterActions($actionsWithAccess, $globalAccess = NULL){
		if($globalAccess === NULL){
			$globalAccess = $this->access;
		}
		foreach($actionsWithAccess as $key => $rights){
			if($rights > $globalAccess){
				unset($actionsWithAccess[$key]);
			}
		}
		return $actionsWithAccess;
	}

	function isActionEnable($rights){
		if($rights <= $this->access){
			return true;
		}
		return false;
	}
}