<?phpclass ModelListFilterItemClass{
	var $field = NULL;
	var $type = NULL;
	var $fields = NULL;
	var $userSetModel = NULL;
	var $setModel = true;

	function __construct($keyName, $params, $parent){
		if(isset($params['type'])){
			$this->type = $params['type'];
		}
		if(isset($params['fields'])){
			$this->fields = $params['fields'];
		}
		if(isset($params['mod'])){
			$this->setModel = $params['mod'];
		}

		$configArray = [
			  'key' => $keyName
			, 'title' => $params['name']
			, 'value' => $this->getDefault()
		];

		$this->field = $this->createField($configArray, $parent);

		if(isset($params['list'])){
			$this->field->setList($params['list']);
		}

		switch($this->type){
			case 'tags':
				$this->field->set($params['obj'], [
					  'driver' => TagsListFieldDrivers::NONE
					, 'allowCreate' => false
					, 'allowAjax' => false
					, 'key' => $keyName
				]);
				break;
			default:
				$this->field->setValue($configArray['value']);
				break;
		}
	}

	function getDefault(){
		switch($this->type){
			case 'tags': return [];
			case 'x': return 0;
		}
		return NULL;
	}

	function createField(&$configArray, $parent){
		$uiType = FormUITypes::TEXT;
		switch($this->type){
			case 'list':
				$uiType = FormUITypes::DROP_DOWN;
				break;
			case 'x':
				$uiType = FormUITypes::CHECKBOX;
				break;
			case 'date':
				$uiType = FormUITypes::DATE;
				break;
			case '2dates':
				$uiType = FormUITypes::DATE;
				break;
			case 'tags':
				$configArray['driver'] = 'session';
				$configArray['allowCreate'] = false;
				$configArray['allowAjax'] = false;
				$uiType = FormUITypes::TAGS;
				break;
		}
		$configArray['type'] = $uiType;

		$class = FromUITypesHandlersClass::getClass($uiType);

		return new $class($configArray, $parent);
	}

	function resetValue(){
		if($this->type == 'tags'){
			$this->field->setForceTags([]);
		}else if($this->type == 'x'){
			$this->field->setValue(0);
		}else{
			$this->field->setValue(NULL);
		}
	}

	function setValue($val){
		$this->field->setValue($val);
	}

	function getValue(){
		return $this->field->getValue();
	}

	function getView(){
		$this->field->setValue($this->decodeTags($this->field->getValue()));
		$this->field->handleAccessPre();
		return $this->field->getView();
	}

	function setUpModel($modelObj, $prev = null){
		if(!empty($this->fields)){

			$cond = NULL;
			$value = $this->getValue();

			if(is_callable($this->userSetModel)){
				call_user_func($this->userSetModel, $modelObj, $value);
				return;
			}

			if((!empty($value) || $value === 0 || $value === '0') && $this->setModel){
				switch($this->type){
					case 'like':
						if($value !== 'NULL'){
							foreach($this->fields as $field){
								$cond[] = 'LOWER(' . $field . ') LIKE \'%' . $value . '%\'';
							}
							if(!empty($cond)){
								$cond = '!(' . implode(' OR ', $cond) . ')';
								array_push($modelObj->conditions, $cond);
							}
						}
						break;
					case 'x':
						foreach($this->fields as $field){
							if($value){
								$modelObj->conditions[$field] = $value;
							}else{
								$modelObj->conditions[$field] = 0;
							}
						}
						break;
					case 'date':
						$modelObj->conditions[] = 'DATE(' . $field . ') = ' . OBE_DateTime::convertToDB($value);
						break;
					case '2dates':
						$modelObj->conditions[] = $field . ' BETWEEN ' . $val[0] . ' AND ' . $val[1];
						break;
					case 'tags':
						if($value !== 'NULL' && !empty($value)){
							$value = $this->decodeTags($value);
							$this->field->control->addFilterModel($modelObj, $this->field, $value, $prev);
						}
						break;
					default:
						if($value !== 'NULL'){
							foreach($this->fields as $field){
								$modelObj->conditions[$field] = $value;
							}
						}
						break;
				}
			}
		}
	}

	function setUserSetModel($userSetModel){
		$this->userSetModel = $userSetModel;
	}

	private function decodeTags($val){
		if($this->type == 'tags' && !empty($val)){
			$val = explode('|', $val);
		}
		return $val;
	}
}