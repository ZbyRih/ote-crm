<?php
namespace App\Extensions\App;

use Tracy\ILogger;
use Nette\Utils\Strings;

class LoggerSection{

	/** @var ILogger */
	private $logger;

	/** @var string */
	private $namespace;

	/**
	 *
	 * @param ILogger $logger
	 */
	public function __construct(ILogger $logger){
		$this->logger = $logger;
	}

	/**
	 *
	 * @param string $namespace
	 * @return self
	 */
	public function setNameSpace($namespace){
		$this->namespace = $namespace;
		return $this;
	}

	public function log($value){
		if(is_string($value)){
			$value = trim($value, '"');
			if(!Strings::startsWith($value, 'FALSE') && !Strings::startsWith($value, 'TRUE') && !Strings::startsWith($value, 'ERROR')){
				$value = '"' . $value;
			}
			if(Strings::contains($value, '"')){
				$value .= '"';
			}
		}else if(is_array($value)){
			reset($value);
			$first = current($value);
			if($first === 'FALSE' || $first === 'TRUE' || $first === 'ERROR'){
				array_shift($value);
				$value = $first . ' "' . trim(implode('" "', $value), '"') . '"';
			}else{
				$value = '"' . trim(implode('" "', $value), '"') . '"';
			}
		}

		$this->logger->log($value, $this->namespace);
	}
}