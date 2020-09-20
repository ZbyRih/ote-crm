<?php

namespace App\Models\Commands;

use App\Extensions\App\Logger;
use App\Extensions\App\User\User;
use App\Extensions\Interfaces\ICommand;
use App\Models\Repositories\ParametersRepository;
use Netpromotion\Profiler\Profiler;
use Nette\Application\Application;
use Nette\Http\Request;
use Nette\Security\IIdentity;
use Tracy\Debugger;
use Tracy\ILogger;

class StatsLogCommand implements ICommand{

	/** @var ILogger */
	private $logger;

	/** @var \Nette\Application\Request */
	private $request;

	/** @var IIdentity */
	private $identity;

	/** @var [] */
	private $cookies;

	/** @var bool */
	private $fire;

	public function __construct(
		User $user,
		ILogger $logger,
		Request $request,
		Application $application,
		ParametersRepository $options)
	{
		if(!$presenter = $application->getPresenter()){
			throw new \Exception('Application didnt return presenter.');
		}

		$this->logger = $logger;
		$this->request = $presenter->getRequest();
		$this->identity = $user->getIdentity();
		$this->cookies = $request->getCookies();

		$this->fire = $user->isLoggedIn() && (array_key_exists('statsLog', $options) ? $options['statsLog'] : true);
	}

	public function execute()
	{
		if(!$this->fire){
			return;
		}

		if(!$this->identity){
			return;
		}

		$duration = microtime(true) - Debugger::$time;

		Profiler::start('log');

		$data = [
			'method' => $this->request->getMethod(),
			'name' => $this->request->getPresenterName(),
			'params' => $this->request->getParameters(),
			'secure' => ($this->request->hasFlag('secure')) ? $this->request->flags['secure'] : false,
			'client' => array_key_exists('HTTP_USER_AGENT', $_SERVER) ? $_SERVER['HTTP_USER_AGENT'] : null,
			'login' => $this->identity->login,
			'user' => $this->identity->name,
			'role' => $this->identity->role,
			'duration' => $duration
		];

		if(isset($this->cookies['wxh'])){
			list($w, $h) = explode('x', $this->cookies['wxh']);

			$data['screen'] = [
				'width' => $w,
				'height' => $h
			];
		}

		$this->logger->log($data, Logger::STAT);

		Profiler::finish('log');
	}
}