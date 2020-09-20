<?php

use Nette\Database\Table\Selection;
use Tracy\Debugger;

class Dumps{

	public static $isCLI = false;
}

// Dumps::$isCLI = (php_sapi_name() == 'cli');
Dumps::$isCLI = (PHP_SAPI == 'cli');

/**
 * @tracySkipLocation
 * @param mixed $var
 * @param string $title
 * @return mixed
 */
function dd(
	$var,
	$title = null)
{
	if(Dumps::$isCLI){
		var_dump($var) . PHP_EOL;
	}else{
		Debugger::barDump($var, $title);
	}
	return $var;
}

/**
 * @tracySkipLocation
 */
function ddSql(
	Selection $sel)
{
	$b = $sel->getSqlBuilder();
	$r = [
		$sel->getSql(),
		$b->getParameters()
	];

	if(Dumps::$isCLI){
		print_r($r);
		PHP_EOL;
	}else{
		Debugger::barDump($r);
	}

	return $sel;
}

/**
 * @tracySkipLocation
 */
function ddProperty(
	$vars,
	$property)
{
	$e = [];
	foreach($vars as $v){
		$e[] = $v->{$property};
	}

	if(Dumps::$isCLI){
		echo implode(", ", $e) . PHP_EOL;
	}else{
		Debugger::barDump($e);
	}
}

/**
 * @tracySkipLocation
 */
function ddIndex(
	$vars,
	$property)
{
	$e = [];
	foreach($vars as $v){
		$e[] = $v[$property];
	}

	if(Dumps::$isCLI){
		echo implode(", ", $e) . PHP_EOL;
	}else{
		Debugger::barDump($e);
	}
}