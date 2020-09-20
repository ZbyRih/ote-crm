<?php
namespace App\Extensions\Abstracts;

use App\Extensions\Interfaces\IViewProvider;

class UnimplementedMethodInView extends \Exception{
}

class ViewData implements IViewProvider{

	/** @var mixed */
	private $data;

	/** @var IViewProvider */
	private $view;

	public function __construct($data, IViewProvider $view){
		$this->data = $data;
		$this->view = $view;
	}

	public function __call($name, $arguments){
		if(!method_exists($this->view, $name)){
			throw new UnimplementedMethodInView($name);
		}

		return call_user_func_array([
			$this->view,
			$name
		], [
			$this->data
		] + $arguments);
	}
}