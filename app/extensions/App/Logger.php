<?php

namespace App\Extensions\App;

use greeny\NetteSlackLogger\SlackLogger;
use greeny\NetteSlackLogger\MessageFactory;
use Nette\Http\Url;
use Nette\Utils\Strings;
use Nette\DI\Container;
use Tracy\Debugger;

class Logger extends \Tracy\Logger{

	const STAT = 'stat';

	private $defaults = [
		'enabled' => FALSE,
		'timeout' => 30,
		'messageFactory' => MessageFactory::class,
		'defaults' => [
			'channel' => NULL,
			'icon' => NULL,
			'name' => NULL,
			'title' => NULL,
			'text' => NULL,
			'color' => NULL
		]
	];

	/** @var SlackLogger */
	private $slackLogger;

	public function __construct(Container $container){
		parent::__construct(Debugger::$logDirectory, Debugger::$email, Debugger::getBlueScreen());
		$this->createSlackLogger($container);
	}

	private function createSlackLogger(Container $container){
		$config = $container->getParameters();

		if(!isset($config['slackLogger']) || !isset($config['slackLogger']['enable']) || !$config['slackLogger']['enable']){
			return;
		}

		$config = $config['slackLogger'];

		$config = \Nette\DI\Config\Helpers::merge($config, $this->defaults);

		if($config['defaults']['name'] && Strings::startsWith($config['defaults']['name'], 'http')){
			$config['defaults']['name'] = (new Url($container->parameters['app']['basePath']))->host;
		}

		$this->slackLogger = new SlackLogger($config['slackUrl'], new MessageFactory($config['defaults'], $config['logUrl']), $config['timeout']);
	}

	public function log($value, $priority = self::INFO){
		if($priority == self::STAT){
			// poslat jen na ELK
			// 			parent::log(json_encode($value), $priority);
		}else{
			if($this->slackLogger){
				$this->slackLogger->log($value, $priority);
			}
			parent::log($value, $priority);
		}
	}

	public function getSlack(){
		return $this->slackLogger;
	}

	public function getSection($namespace){
		return (new LoggerSection($this))->setNameSpace($namespace);
	}
}