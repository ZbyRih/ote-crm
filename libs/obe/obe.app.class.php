<?php

class OBE_App{

	public static $newVars = null;

	/**
	 *
	 * @var OBE_VarLoader
	 */
	public static $Vars = null;

	/**
	 * smarty
	 * @var OBE_Smarty
	 */
	public static $Smarty = null;

	/**
	 *
	 * @var OBE_IDB
	 */
	public static $db = null;

	public static function destroy()
	{
		self::$Smarty = null;
	}
}