<?php

namespace App\Extensions\Components;

use Nette\FileNotFoundException;

trait TTemplateDefaultResolver{

	public function getTemplateDefaultFile($file = 'default'){
		$r = new \ReflectionClass($this);
		$file = dirname($r->getFileName()) . '/' . $file . '.latte';

		if(!file_exists($file)){
			$r = $r->getParentClass();
			$file = dirname($r->getFileName()) . '/' . $file . '.latte';
		}

		if(!file_exists($file)){
			throw new FileNotFoundException($file);
		}

		return $file;
	}
}