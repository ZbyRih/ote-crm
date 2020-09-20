<?php

class OBE_JsonLinter{
	function __construct($file){
		$content = file_get_contents($file);
		$parser = new Seld\JsonLint\JsonParser();
		$e = $parser->lint($content);
		echo '<pre>' . $e->getMessage() . "\r\n".'</pre>';
		exit(0);
	}

	public static function lint($file){
		$lint = new self($file);
	}
}