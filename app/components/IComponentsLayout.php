<?php

namespace App\Components;

interface IComponentMenuFactory{

	/**
	 *
	 * @return \App\Components\Menu
	 */
	function create();
}

interface IComponentNavBarFactory{

	/**
	 *
	 * @return \App\Components\NavBar
	 */
	function create();
}

interface IComponentNavBarNotificationFactory{

	/**
	 *
	 * @return \App\Components\NavBarNotification
	 */
	function create();
}