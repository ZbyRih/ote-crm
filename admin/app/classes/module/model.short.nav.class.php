<?php

class ModelShortNavClass extends ViewElementClass{

	const alone = 0;

	const prvni = 1;

	const posledni = 2;

	const mezi = 3;

	/**
	 * object modellistu
	 * @var ModelListClass
	 */
	var $modelList;

	var $nameRows = NULL;

	public $prev = NULL;

	public $next = NULL;

	public $curr = NULL;

	/**
	 *
	 * @var ModelClass
	 */
	var $modelObj = NULL;

	var $scope = NULL;

	public function __construct($type = NULL){
		parent::__construct('model_quick_nav');
	}

	/**
	 *
	 * @param ModelListClass $modelList
	 * @return self
	 */
	public function init($modelList, $scope, $displayRowName){
		$this->scope = $scope;
		$this->modelList = $modelList;
		$this->modelList->bPageing = false;
		$this->curr = $scope->recordId;
		$this->nameRows = ModelHelper::getModelNameAndRowLikeMArray($displayRowName, $this->modelList->model);
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see ViewElementClass::getElementView()
	 */
	public function getElementView(){
		$this->create();
		AdminApp::$mainModule->views->setTitle($this->curr['name']);
		return $this;
	}

	/**
	 *
	 * @param integer $currentRecordId
	 * @param mixed $nameRow - string/array('modelName' => 'rowname')
	 * @param string $view
	 * @param string $currentName
	 * @return array - for smarty
	 */
	public function create($currentRecordId = NULL, $nameRow = NULL, $currentName = NULL){
		/* vybrat ocislovanej list a tim ziskat pozici soucasneho recordu v listu a pak vybrat prvek pred a po */
		OBE_App::$db->query('SET @ind:=-1');

		if($nameRow !== NULL){
			$this->nameRows = ModelHelper::getModelNameAndRowLikeMArray($nameRow);
		}

		if($currentRecordId === NULL){
			if($this->curr !== NULL){
				$currentRecordId = $this->curr;
			}else{
				throw new OBE_Exception('ShortNavClass::Create není platné currentRecordId');
			}
		}

		$this->modelList->createListCols();
		$this->modelObj = clone ($this->modelList->model);

		$listRows = $this->modelList->getCols();

		$cols = $this->modelObj->getExistsCols($this->nameRows);
		$listRows = MArray::MergeMultiArray($cols, $listRows);
		$listRows = MArray::CleanDoubledVals($listRows);

		$order = $this->modelList->getOrder();
		$order = $this->modelList->sortObj->updateOrderForModel($order);

		$this->modelList->filter->setUpModel($this->modelList->model);

		$sql = $this->modelList->model->GetFindAllRawSql([], $listRows, $order);

		$next = NULL;
		$prev = NULL;
		$current = NULL;

		if($res = OBE_App::$db->FetchSingleArray(
			'SELECT l.ind_pos FROM( SELECT @ind:=@ind+1 AS ind_pos, sl.' . $this->modelList->model->primaryKey . ' FROM(' . $sql . ') AS sl) as l WHERE l.' . $this->modelList->model->primaryKey . ' = ' . $currentRecordId)){

			$pos = $res['ind_pos'];
			$lim = 3;
			$res = OBE_App::$db->FetchSingleArray('SELECT @ind AS ind_pos');
			$type = self::mezi;

			if($pos == 0){
				$pos = 0;
				$type = self::prvni;
				$lim = 2;
				if($pos == $res['ind_pos']){
					$type = self::alone;
					$lim = 1;
				}
			}elseif($pos == $res['ind_pos']){
				$type = self::posledni;
				$pos--;
				$lim = 2;
			}else{
				$pos--;
			}

			if($res = OBE_App::$db->FetchArray('SELECT l.' . $this->modelList->model->primaryKey . ' FROM(' . $sql . ') as l LIMIT ' . $pos . ',' . $lim)){
				if($type == self::posledni){ //posledni prvek
					$prev = $this->_getModelObjectId(reset($res));
					$current = $this->_getModelObjectId(end($res));
				}elseif($type == self::prvni){ //prvni prvek
					$next = $this->_getModelObjectId(end($res));
					$current = $this->_getModelObjectId(reset($res));
				}elseif($type == self::mezi){ //mezi
					$prev = $this->_getModelObjectId(reset($res));
					$current = $this->_getModelObjectId(next($res));
					$next = $this->_getModelObjectId(end($res));
				}elseif($type == self::alone){
					$current = $this->_getModelObjectId(reset($res));
				}
			}
		}else{
			$current = $currentRecordId;
		}

		$this->modelObj->conditions = [];

		$this->prev = $this->getItemName($prev);
		$this->next = $this->getItemName($next);
		if($currentName === NULL){
			$this->curr = $this->getItemName($current);
		}else{
			$this->curr = [
				'name' => $currentName,
				'recordId' => $current
			];
		}
	}

	private function _getModelObjectId($data){
		return $data[$this->modelList->model->primaryKey];
	}

	function getItemName($recordId){
		if($recordId !== NULL){
			$cols = $this->modelObj->getExistsCols($this->nameRows);
			$modelData = $this->modelObj->FindOneById($recordId, $cols);
			$name = '';
			if($names = ModelHelper::getDataDump($this->nameRows, $modelData, true)){
				$name = implode(' - ', array_filter($names));
			}
			return [
				'recordId' => $recordId,
				'name' => $name
			];
		}
		return NULL;
	}
}