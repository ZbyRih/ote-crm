<?php

namespace App\Components\Service;

use Latte\Runtime\Filters;

class ServiceXcache extends ServicePanel{

	public function __construct(){
		if(!function_exists('xcache_info')){
			parent::__construct('XCache', [
				'status' => 'disable'
			]);
			return;
		}

		$c = ini_get('xcache.cacher');
		$enable = ($c == 1 || strtolower($c) == 'on');

		$var = xcache_info(XC_TYPE_VAR, 0);
		$php = xcache_info(XC_TYPE_PHP, 0);

		$items = [
			'status' => $enable ? 'enable' : 'disable',
			'php hits' => isset($php['hits']) ? $php['hits'] : 0,
			'php missed' => isset($php['misses']) ? $php['misses'] : 0,
			'php mem. used' => isset($php['size']) ? Filters::bytes($php['size']) : 0,
			'php mem. left' => isset($php['avail']) ? Filters::bytes($php['avail']) : 0,
			'var hits' => isset($var['hits']) ? $var['hits'] : 0,
			'var missed' => isset($var['misses']) ? $var['misses'] : 0,
			'var mem. used' => isset($var['size']) ? Filters::bytes($var['size']) : 0,
			'var mem. left' => isset($var['avail']) ? Filters::bytes($var['avail']) : 0
		];

		$actions = [
			'reset' => [
				'kind' => 'primary',
				'color' => 'yellow',
				'title' => 'XCache Reset'
			]
		];

		parent::__construct('XCache', $items, $actions);
	}

	public function getEnable(){
		return function_exists('xcache_clear_cache');
	}

	public function handleReset(){
		if(function_exists('xcache_clear_cache')){
			xcache_clear_cache();
			$this->presenter->flashSuccess('XCache resetována.');
		}else{
			$this->presenter->flashWarning('XCache není aktivní.');
		}
		$this->presenter->redirect('default');
	}
}