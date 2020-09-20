<?php

class ModuleItemSelectFieldClass extends FormFieldClass{

	const REMOVE_TITLE = 'Odebrat';

	var $itemLabel = NULL;

	var $selLinkTitle = NULL;

	var $selLinkUrl = NULL;

	var $remTitle = self::REMOVE_TITLE;

	var $fromFieldsToUrl = NULL;

	var $fromListToFields = NULL;

	public function setSelect(
		$linkTitle,
		$fromModuleId,
		$itemLabel = NULL/*, $adds = []*/){
		$selThrow = AdminApp::$modules->getModuleName(MODULES::SELECT);
		$selFrom = AdminApp::$modules->getModuleName($fromModuleId);

		$this->selLinkTitle = $linkTitle;
		$this->selLinkUrl = k_ajax . '=select&' . k_module . '=' . $selThrow . '&frommodule=' . $selFrom;

		$this->itemLabel = $itemLabel;

		return $this;
	}

	public function setAdds(
		$adds)
	{
		$link = http_build_query($adds);
		$this->selLinkUrl .= (($link) ? '&' . $link : '');
		return $this;
	}

	public function setItemLabel(
		$label)
	{
		$this->itemLabel = $label;
		return $this;
	}

	function setRemTitle(
		$title)
	{
		$this->remTitle = $title;
		return $this;
	}

	function setFieldsToUrl(
		$config)
	{
		$this->fromFieldsToUrl = str_replace('"', '|', json_encode($config));
		return $this;
	}

	function setListToField(
		$config)
	{
		$this->fromListToFields = str_replace('"', '|', json_encode($config));
		return $this;
	}

	function handleAccessPre()
	{
		$this->data = array_merge($this->data,
			[
				'itemLabel' => $this->itemLabel,
				'selLinkTitle' => $this->selLinkTitle,
				'selLinkUrl' => $this->selLinkUrl,
				'remTitle' => $this->remTitle,
				'fields2url' => $this->fromFieldsToUrl,
				'list2fields' => $this->fromListToFields
			]);
	}
}