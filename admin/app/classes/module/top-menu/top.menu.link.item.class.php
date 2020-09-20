<?php

class TopMenuLinkItemClass extends TopMenuItemClass{
	function __construct($name = '', $userAccessLevel = 0, $callToView = NULL, $callToCreate = NULL){
		parent::__construct('link', $name, $userAccessLevel, $callToView, $callToCreate);
	}

	function initByArray($config){
		parent::initByArray($config);
		$this->type = 'link';
	}
}