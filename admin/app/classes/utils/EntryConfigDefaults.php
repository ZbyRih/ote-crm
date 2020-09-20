<?php


namespace App\Utils;

use Schematic\Entries;
use Schematic\Entry;
use Exception;

class EntryConfigDefaults extends Entry{

	/**
	 *
	 * @param array $data
	 * @param string $entriesClass
	 */
	public function __construct(array $data, $entriesClass = Entries::class){

		$defaults = $this->extractDefaults($this);

		$data = $this->updateDefaults($data, $defaults);

		parent::__construct($data, $entriesClass);
	}

	private function extractDefaults($object){

		$reflection = new \ReflectionClass($object);

		while($reflection && $reflection->getName() != 'EntryConfigDefaults'){
			$docs[] = $reflection->getDocComment();
			$reflection = $reflection->getParentClass();
		}

		/* @TODO: přepsat - použít nějakou knihovnu na parsování doc comentu */
		$docs = array_reverse($docs);

		$lines = [];
		foreach($docs as $d){
			$lines = array_merge($lines, explode("\n", $d));
		}

		$ret = [];
		foreach($lines as $l){
			if(strpos($l, '@property') && strpos($l, '=')){
				$elms = explode('=', trim($l, ' *'));

				if(count($elms) > 1){

					$params = explode(' ', trim($elms[0]));

					$val = trim($elms[1]);

					if($val === 'fasle'){
						throw new Exception(' did you mean `false` for key `' . trim($params[2], ' $') . '`? `fasle` given.');
					}

					$ret[trim($params[2], ' $')] = $val === 'false' ? false : ($val === 'true' ? true : ($val === 'null' ? null : (is_numeric($val) ? (float) $val : ($val === '[]' ? [] : $val))));
				}
			}
		}

		return $ret;
	}

	private function updateDefaults($data, $defaults){
		foreach($defaults as $k => $v){
			if(!array_key_exists($k, $data)){
				$data[$k] = $v;
			}
		}
		return $data;
	}
}