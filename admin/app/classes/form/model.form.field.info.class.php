<?php
use Nette\Application\ApplicationException;


class ModelFormFieldInfo extends FormFieldInfo{

	var $model = NULL;

	var $col = NULL;

	function __construct(
		&$arrayDefinition = [],
		$parent)
	{
		if(isset($arrayDefinition['modelname']) && isset($arrayDefinition['rowname'])){
			throw new ApplicationException('Starý způsob zadání modelu a fieldu ModelFormFieldInfo');
		}else if(isset($arrayDefinition['field'])){
			$parts = explode('.', $arrayDefinition['field']);
			$this->model = (count($parts) > 0) ? $parts[0] : null;
			$this->col = (count($parts) > 1) ? $parts[1] : null;
			$parent->key = $this->createKeyName($this->model, $this->col);
		}

		parent::__construct($arrayDefinition, $parent);
	}

	function createKeyName(
		$model,
		$col)
	{
		if(!empty($model) || !empty($col)){
			$names = [];
			if(!empty($model)){
				$names[] = $model;
			}
			if(!empty($col)){
				$names[] = $col;
			}
			return implode('_', $names);
		}
		return NULL;
	}

	function extractData(
		$data)
	{
		if(isset($data[$this->model]) && array_key_exists($this->col, $data[$this->model])){
			return $data[$this->model][$this->col];
		}

		return parent::extractData($data);
	}

	function extractDataOrDefault(
		$data,
		$globalDefaultValue = NULL)
	{
		if(isset($data[$this->model]) && is_array($data[$this->model]) && array_key_exists($this->col, $data[$this->model])
			/*&& !empty($data[$this->model][$this->col])*/){
			return $data[$this->model][$this->col];
		}

		return parent::extractDataOrDefault($data, $globalDefaultValue);
	}

	function isModel()
	{
		return (bool) ($this->model !== NULL && $this->col !== NULL);
	}
}