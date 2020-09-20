<?php

namespace App\Components\Service;

use App\Extensions\Components\BaseComponent;
use App\Extensions\Utils\Helpers\ArrayHash;
use App\Models\Repositories\ParametersRepository;
use Nette\DI\Container;
use Nette\Utils\Strings;
use Tracy\Debugger;

class Services extends BaseComponent{

	/** @var ITestMailDlg */
	private $comTestDialog;

	/** @var Container */
	private $container;

	private $panels;

	private $getters;

	public function __construct(Container $container, ParametersRepository $opts, ITestMailDlg $testDlg){
		$this->comTestDialog = $testDlg;

		$ns = $opts->service['namespace'];
		$pnls = $opts->service['panels'];

		foreach($pnls as $p){
			$s = $container->getByType($ns . '\Service' . Strings::firstUpper($p));
			if($s && $s->getEnable()){
				$this->panels[$p] = $s;
			}
		}
	}

	public function render(){
		$this->template->panels = $this->panels;

		parent::render();
	}

	public function createComponent($name){
		if(array_key_exists($name, $this->panels)){
			return $this->panels[$name]->setGetters($this->getters);
		}
		return parent::createComponent($name);
	}

	public function handleTestSlack(){
		if($slack = Debugger::getLogger()->getSlack()){
			$slack->log(new \Exception('Test slack'), Debugger::ERROR);
			$this->presenter->flashSuccess('Poslána zpráva na slack');
		}else{
			$this->presenter->flashWarning('Služba slack není aktivní');
		}
		$this->presenter->redirect('default');
	}

	public function handleTestException(){
		throw new \Exception('Test exception');
	}

	public function handleTestCreateDir(){
		$this->presenter->flashInfo('Není implementováno.');
		$this->presenter->redirect('default');
	}

	public function createComponentTestMailDlg(){
		return $this->comTestDialog->create()->setBacklink($this->presenter->storeRequest());
	}

	public function setGetters($getters){
		$this->getters = ArrayHash::from($getters, false);
		return $this;
	}
}