<?php

namespace App\Presenters;

use Exception;
use Tracy\Debugger;
use Nette\Application\Responses\ForwardResponse;

class ErrorPresenter extends BasePresenter{

	/**
	 *
	 * @param \Exception $exception
	 */
	public function actionDefault(
		$exception)
	{

		// AJAX request? Note this error in payload.
		if(Debugger::$productionMode && $this->isAjax()){
			$this->payload->error = true;
			$this->payload->error_message = $exception->getMessage();
			$this->redrawControl('content');
		}

		if(Debugger::$productionMode && $exception instanceof \Nette\Application\BadRequestException){
			if($exception->getCode() == '404' && $this->isAjax()){
				$this->flashWarning('Požadavek na neexistující adresu.');
			}

			if($this->isLogged()){
				$this->setView($exception->getCode());
			}else{
				$this->redirect('Sign:In:');
			}
			return;
		}

		if($exception instanceof \Nette\InvalidStateException){
			if(strpos($exception->getMessage(), 'row with signature') !== false){
				if($this->cyclingCheck('invalid.state')){
					Debugger::log($exception, Debugger::ERROR);
				}else{
					$this->flashInfo('Neplatná cache data -> resetováno');
					$this->sendResponse(new ForwardResponse($this->getRequest()));
				}
			}
		}

		if(Debugger::$productionMode){
			Debugger::log($exception, Debugger::ERROR);
			$this->setView('500');
			$this->template->chyba = ($exception instanceof Exception) ? $exception->getMessage() : 'neznámá';
			return;
		}

		Debugger::exceptionHandler($exception);
	}

	public function renderDefault()
	{
		if($this->isAjax()){
			$this->setLayout(false);
		}
	}

	public function setView(
		$code)
	{
		$code = (string) $code;
		$file = is_file($this->templateFile($code)) ? $code : ($code{0} == '4' ? '4xx' : '500');
		parent::setView($file);
	}

	private function templateFile(
		$code)
	{
		return __DIR__ . '/templates/Error/' . $code . '.latte';
	}

	private function cyclingCheck(
		$key)
	{
		$now = time();
		$ivs = $this->getSession($key);

		if($ivs->offsetExists('time')){
			if($now - $ivs->time < 2){
				return true;
			}
		}
		$ivs->time = $now;

		return false;
	}
}