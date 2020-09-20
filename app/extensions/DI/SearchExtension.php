<?php

namespace App\Extensions\DI;

use Nette;
use Nette\DI\Config\Helpers;
use Nette\Loaders\RobotLoader;
// use Nette\Schema\Expect;
use Nette\Utils\Arrays;
use Nette\Utils\ArrayHash;
use Nette\DI\ServiceDefinition;

/**
 * Services auto-discovery.
 */
final class SearchExtension extends Nette\DI\CompilerExtension{

	/** @var array */
	private $classes = [];

	/** @var string */
	private $tempDir;

	private $defaults = [
		'in' => null,
		'files' => [],
		'classes' => [],
		'extends' => [],
		'implements' => [],
		'exclude' => [
			'classes' => [],
			'extends' => [],
			'implements' => []
		],
		'tags' => []
	];

	private $counter = 0;

	public function __construct(
		$tempDir)
	{
		$this->tempDir = $tempDir;
	}

	public function loadConfiguration()
	{
		$this->config = $this->getConfig();
		foreach(array_filter($this->config) as $name => $batch){
			$batch = Helpers::merge($batch, $this->defaults);
			$batch = ArrayHash::from($batch, false);
			$batch->exclude = ArrayHash::from($batch->exclude, false);

			if(!is_dir($batch->in)){
				throw new \Exception("Option '{$this->name} › {$name} › in' must be valid directory name, '{$batch->in}' given.");
			}

			foreach($this->findClasses($batch) as $class){
				$this->classes[$class] = array_merge(isset($this->classes[$class]) ? $this->classes[$class] : [], $batch->tags);
			}
		}
	}

	public function findClasses(
		\stdClass $config)
	{
		$params = $this->getContainerBuilder()->parameters;
		$robot = new RobotLoader();
		$robot->setTempDirectory($this->tempDir);
		$robot->setAutoRefresh($params['debugMode']);
		$robot->addDirectory($config->in);
		$robot->acceptFiles = $config->files ?: [
			'*.php'
		];
		$robot->rebuild();

		$classes = array_unique(array_keys($robot->getIndexedClasses()));
		$exclude = $config->exclude;
		$acceptRE = self::buildNameRegexp($config->classes);
		$rejectRE = self::buildNameRegexp($exclude->classes);
		$acceptParent = array_merge($config->extends, $config->implements);
		$rejectParent = array_merge($exclude->extends, $exclude->implements);
		$found = [];
		foreach($classes as $class){
			$rc = new \ReflectionClass($class);
			if(($rc->isInstantiable() || ($rc->isInterface() && count($methods = $rc->getMethods()) === 1 && $methods[0]->getName() === 'create')) && (!$acceptRE || preg_match(
				$acceptRE, $rc->getName())) && (!$rejectRE || !preg_match($rejectRE, $rc->getName())) && (!$acceptParent || Arrays::some($acceptParent,
				function (
					$nm) use (
				$rc)
				{
					return $rc->isSubclassOf($nm);
				})) && (!$rejectParent || Arrays::every($rejectParent, function (
				$nm) use (
			$rc)
			{
				return !$rc->isSubclassOf($nm);
			}))){
				$found[] = $rc->getName();
			}
		}
		return $found;
	}

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		// vyradit uz zaregistrovany
		foreach($this->classes as $class => $foo){
			if($builder->findByType($class)){
				unset($this->classes[$class]);
			}
		}

		// projit a zaregistrovat
		foreach($this->classes as $class => $tags){
			$name = 'search.extension.' . $this->counter;
			$this->counter++;
			if(class_exists($class)){
				$def = $builder->addDefinition($name)->setType($class);
			}else{
				$srdef = new ServiceDefinition();
				$srdef->setImplement($class);

				$def = $builder->addDefinition($name, $srdef); //->addFactoryDefinition(null)->setImplement($class);
			}
			$def->setTags(Arrays::normalize($tags, true));
		}
	}

	private static function buildNameRegexp(
		array $masks)
	{
		$res = [];
		foreach((array) $masks as $mask){
			$mask = (strpos($mask, '\\') === false ? '**\\' : '') . $mask;
			$mask = preg_quote($mask, '#');
			$mask = str_replace('\*\*\\\\', '(.*\\\\)?', $mask);
			$mask = str_replace('\\\\\*\*', '(\\\\.*)?', $mask);
			$mask = str_replace('\*', '\w*', $mask);
			$res[] = $mask;
		}
		return $res ? '#^(' . implode('|', $res) . ')$#i' : null;
	}
}