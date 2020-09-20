<?php

namespace App\Extensions\Components\Menu;

use App\Extensions\Components\BaseComponent;
use Nette\Application\Helpers;
use Nette\Application\LinkGenerator;
use App\Extensions\Utils\Strings;

class BaseMenu extends BaseComponent{

	use TMenuNode;

	/** @var LinkGenerator */
	private $linkGenerator;

	/** @var string */
	private $moduleName;

	/** @var string */
	private $presenterName;

	/** @var string */
	private $presenterResource;

	/** @var bool */
	private $collapsed;

	public function __construct(
		LinkGenerator $linkGenerator)
	{
		$this->linkGenerator = $linkGenerator;
	}

	/**
	 * @return string[]
	 */
	public function getBreadcrumbs()
	{
		return collection($this->nodes)->listNested('desc', 'nodes')
			->match([
			'active' => 1
		])
			->extract('title')
			->toList();
	}

	public function isCollapsed()
	{
		return $this->collapsed;
	}

	/**
	 * {@inheritdoc}
	 * @see BaseComponent::attached()
	 */
	protected function attached(
		$presenter)
	{
		parent::attached($presenter);

		$this->build();
		$this->init($presenter);

		$nodes = collection($this->nodes)->filter(function (
			MenuNode $v,
			$k)
		{
			return $this->checkAccess($v);
		})
			->each(function (
			$v,
			$k)
		{
			$this->resolveActive($v);
		})
			->toArray();

		$this->template->nodes = $nodes;
	}

	public function resetActiveTo(
		$to)
	{
		$this->setActiveTo($this, $to);
	}

	private function init(
		$presenter)
	{
		$this->collapsed = $presenter->getHttpRequest()->getCookie($this->getCookieKey());
		$this->presenterResource = $presenter->getResource();
		list($this->moduleName, $this->presenterName) = Helpers::splitName($presenter->getName());

		$this->modul = $this->presenterName;
		if($this->moduleName){
			$this->modul = $this->moduleName;
		}

		if($this->presenterResource == $this->modul){
			$this->presenterResource = '';
		}
	}

	private function checkAccess(
		MenuNode $mn)
	{
		if(!$this->user->isAllowed($mn->resource ?: $mn->modul, $mn->priviledge)){
			return false;
		}

		if(!$mn->nodes){
			if(!Strings::contains($mn->action, '/index.php?')){
				$mn->url = $this->linkGenerator->link($mn->action);
			}else{
				$mn->url = $mn->action;
			}
			return true;
		}

		$mn->nodes = collection($mn->nodes)->filter(function (
			MenuNode $v,
			$k)
		{
			return $this->checkAccess($v);
		})
			->toArray();

		if(!$mn->nodes){
			return false;
		}

		return true;
	}

	private function resolveActive(
		MenuNode $mn)
	{
		if($mn->nodes){
			if(collection($mn->nodes)->filter(function (
				MenuNode $v,
				$k)
			{
				return $v->active = $this->resolveActive($v);
			})
				->toArray()){
				return $mn->active = true;
			}else{
				return false;
			}
		}

		if($this->presenterResource){
			return $mn->active = $this->presenterResource == $mn->resource;
		}else{
			return $mn->active = ($this->modul == ($mn->resource ?: $mn->modul));
		}
	}

	private function setActiveTo(
		$mn,
		$to)
	{
		if($mn->nodes){
			$active = false;
			collection($mn->nodes)->each(function (
				MenuNode $v,
				$k) use (
			$to,
			$active)
			{
				$active |= $this->setActiveTo($v, $to);
			});
			return $mn->active = $active;
		}
		return $mn->active = $mn->action == $to;
	}

	protected function build()
	{
	}

	protected function getCookieKey()
	{
		return 'sidebar_closed';
	}
}