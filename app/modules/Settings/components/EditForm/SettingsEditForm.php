<?php

namespace App\Modules\Settings\Components;

use App\Extensions\Components\BaseComponent;
use App\Extensions\Components\Controls\IntegerInput;
use App\Extensions\Components\Controls\FloatInput;
use App\Models\Events\SettingsChangedEvent;
use App\Models\Orm\Orm;
use App\Models\Resources\ConfigFileResource;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Controls\TextArea;
use App\Components\Controls\MAUploadControl;
use App\Extensions\Components\Controls\DateInput;
use Contributte\EventDispatcher\EventDispatcher;
use App\Models\Orm\Settings\SettingEntity;
use App\Models\Events\DBCommitEvent;

class SettingsEditForm extends BaseComponent{

	/** @var Orm @inject */
	private $orm;

	/** @var EventDispatcher */
	private $dispatcher;

	/** @var string */
	private $group = 'main';

	private static $types = [
		SettingEntity::TYPE_INT => IntegerInput::class,
		SettingEntity::TYPE_FLOAT => FloatInput::class,
		SettingEntity::TYPE_STRING => TextInput::class,
		SettingEntity::TYPE_WSWG => TextArea::class,
		SettingEntity::TYPE_FILE => MAUploadControl::class,
		SettingEntity::TYPE_DATE => DateInput::class
	];

	/**
	 *
	 * @var array
	 */
	public $onSave = [];

	public function __construct(
		Orm $orm,
		EventDispatcher $dispatcher)
	{
		$this->orm = $orm;
		$this->dispatcher = $dispatcher;
	}

	/**
	 *
	 * @param string $group
	 */
	public function setGroup(
		$group)
	{
		$this->group = $group;
	}

	public function createComponentForm()
	{
		$f = $this->createForm();

		$settings = $this->orm->settings->findBy([
			'group' => $this->group
		])->fetchAll();

		$c = collection($settings)->sortBy('id', SORT_ASC)
			->indexBy('id')
			->buffered();

		foreach($c->toArray() as $a){
			if(!array_key_exists($a->type, self::$types)){
				continue;
			}

			$e = new self::$types[$a->type]($a->description);

			$f->addComponent($e, $a->id);
			$e->setRequired((bool) $a->require);
		}

		$f->addSubmit('save', 'Uložit');
		$f->addSubmit('cancel', 'Zrušit')->setValidationScope([])->onClick[] = function (){
			$this->redirect(':Homepage:default');
		};

		$vals = $c->extract('value')->toArray();

		$f->setDefaults($vals);

		$f->onValidate[] = [
			$this,
			'onValidate'
		];

		$f->onSuccess[] = [
			$this,
			'onSuccess'
		];

		return $f;
	}

	public function onValidate(
		$form,
		$vs)
	{
	}

	public function onSuccess(
		$form,
		$vs)
	{
		foreach($vs as $k => $v){
			if(!$entity = $this->orm->settings->getById($k)){
				continue;
			}

			if($entity->type == SettingEntity::TYPE_FILE){
				if(!$v->hasFile()){
					continue;
				}
				$v = ConfigFileResource::fromFileUpload($v);
			}

			if($entity->type == SettingEntity::TYPE_DATE && $v){
				$v = $v->format('Y-m-d');
			}

			$entity->value = $v === null ? null : (string) $v;
			$this->orm->persist($entity);
		}

		try{
			$this->onSave();
		}finally {
			$this->dispatcher->dispatch(SettingsChangedEvent::NAME);
			$this->dispatcher->dispatch(DBCommitEvent::NAME);
		}
	}
}