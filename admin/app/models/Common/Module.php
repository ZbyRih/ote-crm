<?php

class MModule extends AdminJsonClass
{

	const ID = 'id';

	const NAME = 'name';

	const ACCESS = 'access';

	const FILE = 'file';

	const MODEL = 'model';

	public $folders;

	private $loaded;

	// na loadovane moduly
	private $lastModuleClass;

	// posledni trida naloadovaneho modulu
	public function __construct($input = [])
	{
		$this->decode('modules');

		$modules = [];
		$folders = [];

		$this['items'] = MArray::setSubItems($this['items'], 'selected', false);

		foreach ($this['items'] as $item) {
			if (isset($item['items'])) {

				$subItems = MArray::MapItemToKey(MArray::FilterMArray($item['items'], 'active', 1), 'id');

				if (!empty($subItems)) {
					$modules = $modules + $subItems;
					$item['items'] = array_keys($subItems);
				}

				if ($item['visible']) {
					$this->folders[] = $item;
				}
			} else {
				if ($item['active']) {
					if ($item['visible']) {
						$this->folders[] = $item;
					}
					$modules = $modules + [
						$item['id'] => $item
					];
				}
			}
		}

		$modules = MArray::setSubItems($modules, 'selected', false);
		$modules = MArray::setSubItems($modules, MModule::ACCESS, 3);

		$accesses = AdminUserClass::getModulesAccesss();
		MArray::MergeArrays($modules, $accesses, 'file', MModule::ACCESS);

		parent::__construct($modules);
	}

	public function getDefault()
	{
		$visible = $this->getVisible();
		reset($visible);
		return key($visible);
	}

	public function getMenu()
	{
		$ua = AdminUserClass::getModulesAccesss();

		foreach ($this->folders as &$item) {
			if (isset($item['items']) && !empty($item['items'])) {

				$keys = $item['items'];

				$item['items'] = array_intersect_key($this->_data, array_combine($keys, $keys));

				foreach ($item['items'] as $k => $it) {
					if (isset($ua[$it['file']]) && !$ua[$it['file']]['visible']) {
						$item['items'][$k]['visible'] = false;
					}
				}

				if (!(count(MArray::FilterMArray($item['items'], 'visible', true)) > 0)) {
					unset($item['items']);
					$item['visible'] = false;
					$item['selected'] = false;
				} else {
					$item['selected'] = (count(MArray::FilterMArray($item['items'], 'selected', true)) > 0);
				}
			} else if (!isset($item['items'])) {
				$item = $this[$item['id']];
				if (isset($ua[$item['file']]) && !$ua[$item['file']]['visible']) {
					$item['visible'] = false;
				}
			}
		}
		return $this->folders;
	}

	public function getItems()
	{
		return $this->_data;
	}

	/**
	 * vrati viditelne pro uzivatele
	 * @return array
	 */
	public function getVisible()
	{
		return MArray::FilterMArray($this->_data, 'visible', 1);
	}

	public function getModuleName($moduleId)
	{
		$mod = $this->getModuleById($moduleId);
		return $mod[self::FILE];
	}

	/**
	 *
	 * @param Integer $moduleId
	 * @return array
	 */
	public function getModuleById($moduleId)
	{
		if (isset($this[$moduleId])) {
			return $this[$moduleId];
		} else {
			throw new OBE_Exception('Modul id:' . $moduleId . ' není v modules.json uveden');
		}
	}

	public function getModuleIdByName($modulFile)
	{
		foreach ($this->_data as $key => $modul) {
			if ($modul[self::FILE] == $modulFile) {
				return $key;
			}
		}
		return NULL;
	}

	/**
	 *
	 * @param String $char
	 * @return array
	 */
	public function getModuleByChar($char)
	{
		if ($item = MArray::GetMArrayItemByKey($this->_data, 'handler', $char)) {
			return reset($item);
		}
		return NULL;
	}

	/**
	 *
	 * @param Integer $moduleId
	 */
	public function createModuleById($moduleId)
	{
		if ($moduleData = self::getModuleById($moduleId)) {
			return self::createModuleByData($moduleData);
		}
		return NULL;
	}

	/**
	 *
	 * @param Array $moduleData
	 * @return AppModuleClass
	 */
	public static function createModuleByData($moduleData)
	{
		try {
			$className = self::load($moduleData[self::FILE]);
			$ModuleObj = new $className($moduleData);
		} catch (Exception $e) {
			if ($e->getCode() == 1) {
				// 				Trace::dump($e->getMessage());
			}
			$ModuleObj = new AppModuleClass($moduleData);
		}
		return $ModuleObj;
	}

	public function getModuleItemNameByCharType($primaryId, $charType)
	{
		if ($moduleData = MArray::GetMArrayItemByKey($this, 'handler', $charType)) {
			$modelObj = $this->getModelObj($moduleData);
			$data = $modelObj->FindOneByID($primaryId);
			return $this->getModuleItemName($data, $moduleData, $modelObj);
		}
		return NULL;
	}

	public function getModuleItemName($item, $moduleData, $modelObj)
	{
		$pair = $this->getModulKeyNamaPair($moduleData, $modelObj);
		return $pair->extract($item);
	}

	/**
	 *
	 * @param Array $moduleData
	 * @param ModelClass $modelObj
	 * @return ModelNameKeyPair
	 */
	private function getModulKeyNamaPair($moduleData, $modelObj)
	{
		if (!isset($moduleData['item-name'])) {
			$moduleData['item-name'] = new ModelNameKeyPair($modelObj, $moduleData['model']['name']);
			$this[$moduleData['id']] = $moduleData;
		}
		return $moduleData['item-name'];
	}

	private function getModelObj($module)
	{
		$modelName = $module['model']['class'];
		$className = 'M' . $modelName;
		return new $className();
	}

	public static function load($file)
	{
		return 'Modul' . ucfirst(str_replace('.', '', $file));
	}
}
