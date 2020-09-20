<?php

namespace App\Components;

class ModelGridComponent extends OldBaseComponent{

	public static $anoNe = [
		0 => 'Ne',
		1 => 'Ano'
	];

	/** @var \ModelListClass */
	private $grid;

	/** @var \Closure */
	private $modelGetter = null;

	/** @var array */
	public $onConfig;

	/**
	 *
	 * @param \ModuleInfoClass $info
	 * @param string $name
	 * @return ModelGridComponent
	 */
	public function create(
		$info)
	{
		$this->grid = \ViewsFactory::createModelList($info);
		$config = $this->config($this->getInfo());
		$this->configGrid($config);
		return $this;
	}

	public function setModelGetter(
		\CLosure $getter)
	{
		$this->modelGetter = $getter;
	}

	/**
	 *
	 * @return \ModelListClass
	 */
	public function getGrid()
	{
		return $this->grid;
	}

	/**
	 *
	 * @return \ModuleInfoClass
	 */
	public function getInfo()
	{
		return $this->grid->info;
	}

	/**
	 *
	 * @param \ModuleInfoClass $info
	 */
	protected function config(
		$info)
	{
	}

	/**
	 *
	 * @param string $form
	 * @param [] $data
	 */
	private function configGrid(
		$data)
	{
		$data['model'] = $this->getModel();
		$this->grid->configByArray($data);
		$this->onConfig($this->grid);
	}

	private function getModel()
	{
		if($this->modelGetter){
			return $this->modelGetter($this->grid);
		}else{
			return $this->setUpModel($this->grid);
		}
	}

	/**
	 *
	 * @var \ModelListClass $Grid
	 * @return \ModelClass
	 */
	protected function setUpModel(
		\ModelListClass $Grid)
	{
	}

	public function run()
	{
		return $this->grid->handleActions();
	}

	public function getElementView()
	{
		return $this->grid->getElementView(null);
	}

	/**
	 *
	 * @param string $key
	 * @param callable $callback
	 * @return \ListAction
	 */
	public function addAction(
		$key,
		$callback)
	{
		$action = $this->grid->actions->addAction($key, new \ListAction($key));
		$this->grid->actions->setCallBack($key, $callback);
		return $action;
	}

	/**
	 *
	 * @param string $key
	 * @return \ListAction
	 */
	public function getAction(
		$key)
	{
		return $this->grid->actions->get($key);
	}

	/**
	 *
	 * @param string $key
	 * @param callable $callback
	 * @return \App\Components\ModelGridComponent
	 */
	public function setAction(
		$key,
		$callback)
	{
		$this->grid->actions->setCallBack($key, $callback);
		return $this;
	}

	/**
	 *
	 * @param int $index
	 * @return \ModelListFilterItemClass
	 */
	public function getFilter(
		$index)
	{
		return $this->grid->filter->getItem($index);
	}

	/**
	 *
	 * @param string $view
	 * @return \App\Components\ModelGridComponent
	 */
	public function passView(
		$view)
	{
		$this->grid->setForceView($view);
		return $this;
	}
}