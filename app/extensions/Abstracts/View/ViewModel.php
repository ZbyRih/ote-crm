<?php
namespace App\Models\Core\View;

use App\Extensions\Abstracts\ViewData;
use App\Extensions\Interfaces\ITableFind;
use App\Extensions\Interfaces\IViewProvider;

class ViewModel{

	/** @var ITableFind */
	private $store;

	/** @var IViewProvider */
	private $view;

	public function __construct(ITableFind $store, IViewProvider $view){
		$this->store = $store;
		$this->view = $view;
	}

	/**
	 *
	 * @param integer $id - primary key
	 * @return ViewData|null
	 */
	public function get($id){
		if(!$rec = $this->getData($id)){
			return null;
		}

		return new ViewData($rec, $this->view);
	}

	public function put($object){
		return new ViewData($object, $this->view);
	}

	protected function getData($id){
		return $this->store->find($id);
	}
}