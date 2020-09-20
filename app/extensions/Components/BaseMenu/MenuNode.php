<?php
namespace App\Extensions\Components\Menu;
use App\Extensions\Abstracts\TArrayAccess;

class MenuNode implements \ArrayAccess{
	use TArrayAccess;
	use TMenuNode;

	public function __construct($action, $title, $icon, $parent, $resource){
		$this->set($action, $title, $icon, $parent, $resource);
	}
}