<?php

namespace App\Extensions\Components;

use Nette\Forms\Controls as NControls;
use App\Extensions\Components\Controls as AControls;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\CheckboxList;
use Nette\Forms\Controls\ChoiceControl;
use Nette\Forms\Controls\SelectBox;

trait TContainerControls{

	/**
	 *
	 * @param string $name
	 * @param string $label
	 * @param array $items
	 * @param int $size
	 * @return SelectBox
	 */
	public function addSelect(
		$name,
		$label = null,
		array $items = null,
		$size = null)
	{
		$ctrl = parent::addSelect($name, $label, $items, $size);
		$ctrl->setAttribute('data-style', 'select');
		return $ctrl;
	}

	/**
	 * přidá cont
	 * @param int $width
	 * @return \Nette\Forms\ControlGroup
	 */
	public function addClearGroup(
		$width)
	{
		$ctrl = $this->addGroup();
		$ctrl->setOption('container', \Nette\Utils\Html::el('div')->setAttribute('style', 'clear: both')
			->class('col-md-' . $width));
		return $ctrl;
	}

	public function addPrimarySubmit(
		$name,
		$caption = null)
	{
		return $this->addSubmit($name, $caption)->setAttribute('class', 'btn-primary');
	}

	public function addSubmitCancel(
		$key,
		$label,
		$callback)
	{
		$btn = $this->addSubmit($key, $label)->setValidationScope([]);
		$btn->onClick[] = $callback;
		return $btn;
	}

	/**
	 *
	 * @param string
	 * @param string|object
	 * @return AControls\FloatInput
	 */
	public function addFloat(
		$name,
		$label = null,
		$maxLength = null)
	{
		return $this[$name] = new AControls\FloatInput($label, $maxLength);
	}

	public function addDate(
		$name,
		$label = null)
	{
		return $this[$name] = new AControls\DateInput($label);
	}

	public function addTime(
		$name,
		$label = null)
	{
		return $this[$name] = new AControls\TimeInput($label);
	}

	public function addTimeSec(
		$name,
		$label = null)
	{
		return $this[$name] = new AControls\TimeSecInput($label);
	}

	public function addTimeSelect(
		$name,
		$label = null)
	{
		return $this[$name] = new AControls\TimeSelectInput($label);
	}

	public function addColor(
		$name,
		$label = null)
	{
		return $this[$name] = new AControls\ColorInput($label);
	}

	public function addSelectPoz(
		$name,
		$label = null,
		$items = [])
	{
		return $this[$name] = new AControls\SelectPozBox($label, $items);
	}

	public function addCheckboxHide(
		$name,
		$label = null,
		$id = null)
	{
		return $this[$name] = new AControls\CheckHideBox($label, $id . '-h-' . $name);
	}

	public function addPasive(
		$name,
		$label)
	{
		return $this[$name] = (new AControls\PasiveText($label));
	}

	/**
	 * Adds select box control that allows multiple item selection.
	 * @param string
	 * @param string|object
	 * @param array
	 * @param int
	 * @return Controls\MultiSelectBox
	 */
	public function addMultiSelect(
		$name,
		$label = NULL,
		array $items = NULL,
		$size = NULL)
	{
		return $this[$name] = (new AControls\MultiSelectBox($label, $items))->setHtmlAttribute('size', $size > 1 ? (int) $size : NULL);
	}

	public function addPrice(
		$name,
		$label = null)
	{
		return $this[$name] = new AControls\PriceInput($label);
	}

	public function addTel(
		$name,
		$label = null)
	{
		return $this[$name] = new AControls\TelInput($label);
	}

	public function addEmail(
		$name,
		$label = null)
	{
		return $this[$name] = new AControls\EmailInput($label);
	}

	public function addMeters(
		$name,
		$label = null)
	{
		return $this[$name] = new AControls\MetersInput($label);
	}

	public function addRP(
		$name,
		$label = null)
	{
		return $this[$name] = new AControls\RPInput($label);
	}

	public function addRC(
		$name,
		$label = null)
	{
		return $this[$name] = new AControls\RCInput($label);
	}

	public function addOP(
		$name,
		$label = null)
	{
		return $this[$name] = new AControls\OPInput($label);
	}

	public function addIC(
		$name,
		$label = null,
		$maxLength = null)
	{
		return $this[$name] = new AControls\ICInput($label, $maxLength);
	}

	public function addDIC(
		$name,
		$label = null,
		$maxLength = null)
	{
		return $this[$name] = new AControls\DICInput($label, $maxLength);
	}

	public function addRZ(
		$name,
		$label = null,
		$maxLength = null)
	{
		return $this[$name] = new AControls\RZInput($label);
	}

	/**
	 * Adds single-line text input control to the form.
	 * @param string
	 * @param string|object
	 * @param int
	 * @param int
	 * @return NControls\TextInput
	 */
	public function addText(
		$name,
		$label = null,
		$maxLength = null,
		$cols = null)
	{
		return parent::addText($name, $label, $cols, $maxLength);
	}

	/**
	 * Adds check box control to the form.
	 * @param string
	 * @param string|object
	 * @return Checkbox
	 */
	public function addCheckbox(
		$name,
		$caption = null)
	{
		$control = parent::addCheckbox($name, $caption);
		$control->getLabelPrototype()->setAttribute('class', 'mt-checkbox');
		return $control;
	}

	/**
	 *
	 * @param string $name
	 * @param string $label
	 * @param array $items
	 * @return ChoiceControl
	 */
	public function addRadioList(
		$name,
		$label = null,
		array $items = null)
	{
		$control = parent::addRadioList($name, $label, $items);
		$control->getSeparatorPrototype()->setName(null);
		$control->getItemLabelPrototype()->setAttribute('class', 'mt-radio mt-radio-outline');
		$control->getContainerPrototype()
			->setName('div')
			->setAttribute('class', 'mt-radio-inline');
		return $control;
	}

	/**
	 * Adds set of checkbox controls to the form.
	 * @param string
	 * @param string|object
	 * @return CheckboxList
	 */
	public function addCheckboxList(
		$name,
		$label = null,
		array $items = null)
	{
		$control = parent::addCheckboxList($name, $label, $items);
		$control->getSeparatorPrototype()->setName(null);
		$control->getItemLabelPrototype()->setAttribute('class', 'mt-checkbox');
		$control->getContainerPrototype()
			->setName('div')
			->setAttribute('class', 'mt-checkbox-inline');
		return $control;
	}

	public function createTime(
		$label = null)
	{
		return (new AControls\TimeInput($label))->setRequired(false);
	}

	public function createTimeSec(
		$label = null)
	{
		return (new AControls\TimeSecInput($label))->setRequired(false);
	}

	public function createDate(
		$label = null)
	{
		return (new AControls\DateInput($label))->setRequired(false);
	}
}