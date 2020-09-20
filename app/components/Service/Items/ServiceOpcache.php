<?php

namespace App\Components\Service;

use Latte\Runtime\Filters;

class ServiceOpcache extends ServicePanel{

	public function __construct(){
		if(!function_exists('opcache_reset')){
			parent::__construct('OPCache', [
				'status' => 'disable'
			]);
			return;
		}

		$op = function_exists('opcache_get_status') ? @opcache_get_status() : null;
		$cachedFiles = isset($op['scripts']) ? array_intersect(array_keys($op['scripts']), get_included_files()) : [];
		$ratio = $op ? round(count($cachedFiles) * 100 / count(get_included_files())) : 0;

		$items = [
			'status' => ($op && $op['opcache_enabled'] ? 'enabled' : 'disabled'),
			'ration' => ($op ? $ratio : 0) . '%',
			'used' => ($op ? Filters::bytes($op['memory_usage']['used_memory']) : 0),
			'free' => ($op ? Filters::bytes($op['memory_usage']['free_memory']) : 0),
			'use cwd' => (ini_get('opcache.use_cwd') ? 'ano' : 'ne'),
			'load comments' => (ini_get('opcache.load_comments') ? 'ano' : 'ne'),
			'save comments' => (ini_get('opcache.save_comments') ? 'ano' : 'ne')
		];

		$actions = [
			'reset' => [
				'kind' => 'primary',
				'color' => 'yellow',
				'title' => 'OPCache Reset'
			]
		];

		parent::__construct('OPCache', $items, $actions);
	}

	public function getEnable(){
		return function_exists('opcache_reset');
	}

	public function handleReset(){
		if(function_exists('opcache_reset')){
			opcache_reset();
			$this->presenter->flashSuccess('Opcache resetována.');
		}else{
			$this->presenter->flashWarning('OPCache není aktivní.');
		}
		$this->presenter->redirect('default');
	}
}