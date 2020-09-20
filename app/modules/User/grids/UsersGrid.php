<?php

namespace App\Modules\User\Grids;

use App\Extensions\Components\BaseGridBoo;
use Carbon\Carbon;
use Kdyby\Translation\Translator;

class UsersGrid extends BaseGridBoo{

	/** @var Carbon */
	private $last;

	/** @var [] */
	public $onEdit = [];

	/** @var [] */
	public $onDelete = [];

	/** @var [] */
	public $onActivate = [];

	/** @var [] */
	public $onRelog = [];

	public function __construct(
		Translator $translator)
	{
		parent::__construct($translator);
	}

	protected function build()
	{
		$last = Carbon::now();
		$last->hour--;
		$last->minute = 0;
		$last->second = 0;

		$this->last = $last;

		$this->addColumnText('login', 'Login');
		$this->addColumnText('jmeno', 'Jméno');
		$this->addColumnText('role', 'Role')->setRenderer(function (
			$item)
		{
			return $this->getTranslator()
				->translate('app.roles.' . $item->role);
		});

		if($this->isAllowed('activity')){
			$this->addColumnText('activity', 'Poslední přihlášení')->setRenderer(
				function (
					$item)
				{
					return ($item->activity && $item->activity > $this->last) ? $item->activity->format('j.n. Y H:i:s') : '';
				});
		}

		if($this->isAllowed('edit')){
			$this->addActionCallback('edit', '', function (
				$id)
			{
				$this->onEdit($id);
			})
				->setIcon('edit')
				->setTitle('Upravit')
				->setClass(self::BUTTON_ICON . '');
		}

		if($this->isAllowed('delete')){
			$this->addActionCallback('delete', '', function (
				$id)
			{
				$this->onDelete($id);
			})
				->setIcon(function (
				$item)
			{
				return $item->deleted ? 'ban' : 'check';
			})
				->setTitle('Zakázat')
				->setClass(self::BUTTON_ICON . ' btn-success')
				->setConfirm('Opravdu chcete uživatele smazat?')
				->setRenderCondition(function (
				$row)
			{
				return !$row->deleted;
			});

			$this->addActionCallback('activate', '', function (
				$id)
			{
				$this->onActivate($id);
			})
				->setIcon('ban')
				->setTitle('Povolit')
				->setClass(self::BUTTON_ICON . ' btn-default-light')
				->setRenderCondition(function (
				$row)
			{
				return $row->deleted;
			});
		}

		if($this->isAllowed('relog')){
			$this->addActionCallback('relog', '', function (
				$id)
			{
				$this->onRelog($id);
			})
				->setIcon('briefcase')
				->setTitle('Přelogovat')
				->setClass(self::BUTTON_ICON . '');
		}

		$this->setDefaultSort('login');
	}
}