<?php

class TagsListFieldDrivers{

	const DEF = 't2e';

	const TE2E = 't2e';

	const SESSION = 'session';

	const NONE = 'none';
}

class TagsListFieldClass extends FormFieldClass{

	const TAG = 'tag';

	private static $drivers = [
		TagsListFieldDrivers::NONE => 'TagDriverNone',
		TagsListFieldDrivers::SESSION => 'TagDriverSession',
		TagsListFieldDrivers::TE2E => 'TagDriverTag2Entity'
	];

	public $allowCreate = true;

	public $allowAjax = true;

	/**
	 *
	 * @var TagsFieldDriver
	 */
	public $relDriver = NULL;

	/**
	 *
	 * @var TagsInterface
	 */
	public $tagsInterface = NULL;

	/**
	 *
	 * @var EntityTagCtrl
	 */
	public $control = null;

	/**
	 *
	 * @param EntityTagCtrl $control
	 * @param array $config
	 */
	public function set($control, $config = null){
		$this->control = $control;

		if($config === null){
			$this->control->fieldKey = $this->key;
			$config = $this->control->config;
		}

		$this->tagsInterface = new TagsInterface($config);

		MArray::extendObject($this, $config);

		if(!isset($config['driver'])){
			$config['driver'] = TagsListFieldDrivers::DEF;
		}

		if(isset($config['key'])){
			$this->control->fieldKey = $config['key'];
		}

		$this->setDriver($config['driver'], $config);

		return $this;
	}

	public function setDriver($driver = NULL, $config = []){
		if(isset($config['driver'])){
			$driver = $config['driver'];
		}
		if($driver){
			if(isset(self::$drivers[$driver])){
				$this->relDriver = new self::$drivers[$driver]($this, $config);
			}else{
				$this->relDriver = new $driver($this, $config);
			}
		}else{
			$this->relDriver = new TagDriverTag2Entity($this, $config);
		}

		return $this;
	}

	/**
	 * odchytava uitype pro vypis pridruzeneho listu
	 * @param Array $field
	 * @param ModelFormClass2 $form
	 */
	function handleAccessPre(){
		$this->control->catchAjax($this);
		$this->data['list'] = $this->loadRelList();
	}

	/**
	 * odchytava uitype pro ulozeni prilozeneho obrazku
	 * @param FormFieldClass $field
	 */
	function handleAccessPost(){
		// tady chytit ajax
	}

	public function ajaxAddTag($callBack = NULL){
		if(OBE_Http::issetGet(self::TAG)){
			$tagName = urldecode(OBE_Http::getGet(self::TAG));
			$this->relDriver->addTagByName($tagName, $callBack);
		}
	}

	public function ajaxDelTag($callBack = NULL){
		if(OBE_Http::issetGet(self::TAG)){
			$this->relDriver->delTag(OBE_Http::getGet(self::TAG), $callBack);
		}
	}

	public function ajaxDelAll(){
		$this->relDriver->delAll();
	}

	public function ajaxFindTag(){
		if(OBE_Http::issetGet(self::TAG)){
			$this->tagsInterface->findTag(urldecode(OBE_Http::getGet(self::TAG)));
		}
	}

	public function ajaxFindAll(){
		if(OBE_Http::issetGet(k_module)){
			$this->tagsInterface->findAll();
		}
	}

	function getView(){
		$view = parent::getView();
		if(!isset($view['data']['list'])){
			$view['data']['list'] = NULL;
		}
		if($this->parent){
			$view['data']['recordId'] = $this->parent->scope->recordId;
		}else{
			$view['data']['recordId'] = NULL;
		}
		$view['data']['allowCreate'] = $this->allowCreate;
		$view['data']['allowAjax'] = $this->allowAjax;
		return $view;
	}

	public function loadRelList(){
		return $this->relDriver->loadRelList();
	}

	function setForceTags($tags = []){
		$this->relDriver->setIds($tags);
	}

	function getForceTags($recordId = NULL){
		return $this->relDriver->getIds($recordId);
	}
}