<?php

namespace App\Extensions\Utils;

class ReflectionClass extends \ReflectionClass{

	public function getClassProperties(){
		$doc = $this->getDocComment();

		// Strip the opening and closing tags of the docblock
		$doc = substr($doc, 3, -2);
		// Split into arrays of lines
		$doc = preg_split('/\r?\n\r?/', $doc);
		// Trim asterisks and whitespace from the beginning and whitespace from the end of lines
		$doc = array_map(function ($line){
			return ltrim(rtrim($line), "* \t\n\r\0\x0B");
		}, $doc);

		$props = [];

		foreach($doc as $l){
			if(empty($l)){
				continue;
			}
			$count = 0;
			$parts = preg_split('/\s+/', $l, $count);
			foreach($parts as $p){
				if($p{0} === '$'){
					$props[] = trim($p, '$');
				}
			}
		}

		return $props;
	}
}