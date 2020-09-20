<?php

namespace App\Modules\Platby\Components;

use App\Extensions\App\Ciselnik;
use App\Extensions\Components\BaseComponent;
use App\Extensions\Components\BaseForm;
use App\Extensions\Components\Controls\DateInput;
use App\Models\Orm\Orm;
use App\Models\Orm\Platby\PlatbaEntity;
use App\Models\Repositories\CiselnikyValuesRepository;
use App\Models\Repositories\SettingsRepository;
use App\Models\Services\FormFactoryService;
use Nette\Forms\Controls\TextArea;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Controls\SelectBox;
use App\Models\Selections\FakSkupSelection;
use App\Models\Selections\KlientsSelection;
use App\Models\Selections\OdberMistSelection;
use App\Models\Orm\PlatbyZarazeni\PlatbaZarazeniEntity;
use App\Extensions\Utils\Callback;
use App\Extensions\Components\Controls\FloatInput;
use Nette\Application\UI\Form;

class PlatbaEdit extends BaseComponent{

	/** @var Orm */
	private $orm;

	/** @var PlatbaEntity */
	private $platba;

	/** @var KlientsSelection */
	private $selKli;

	/** @var FormFactoryService */
	private $facForm;

	/** @var OdberMistSelection */
	private $selOdbm;

	/** @var FakSkupSelection */
	private $selFakSkup;

	/** @var Ciselnik */
	private $typyPlateb;

	/** @var float */
	private $dphCoef;

	/** @var [] */
	public $onCancel = [];

	/** @var [] */
	public $onSave = [];

	public function __construct(
		Orm $orm,
		KlientsSelection $selKli,
		OdberMistSelection $selOdbm,
		FormFactoryService $facForm,
		FakSkupSelection $selFakSkup,
		SettingsRepository $repSettings,
		CiselnikyValuesRepository $repCisl)
	{
		$this->orm = $orm;
		$this->selKli = $selKli;
		$this->selOdbm = $selOdbm;
		$this->facForm = $facForm;
		$this->selFakSkup = $selFakSkup;
		$this->typyPlateb = $repCisl->getCiselnik('typy_pohybu');
		$this->dphCoef = $repSettings->dph_koef;
	}

	/**
	 * @param PlatbaEntity $platba
	 */
	public function setPlatba(
		$platba)
	{
		$this->platba = $platba;
	}

	public function render()
	{
		$this->template->setParameters([
			'form' => $this['form']
		]);
		parent::render();
	}

	public function createComponentForm()
	{
		$f = $this->facForm->create();

		$c = new DateInput('Připsáno');
		$c->setRequired();
		$f->addComponent($c, 'when');

		$c = new TextInput('Částka');
		$c->setRequired();
		$c->setAttribute('data-format', 'numeric');
		$c->setAttribute('data-numeric', 'currency');
		$f->addComponent($c, 'platba');

		$c = new TextInput('Z čísla účtu');
		$f->addComponent($c, 'fromCu');

		$c = new TextInput('Subjekt');
		$f->addComponent($c, 'subject');

		$c = new TextInput('Var. symbol');
		$f->addComponent($c, 'vs');

		$c = new TextArea('Popis');
		$f->addComponent($c, 'msg');

		$c = new FloatInput('Koeficient DPH');
		$f->addComponent($c, 'dphCoef');

		$c = new SelectBox('Zařazení');
		$f->addComponent($c, 'type');

		$c = new SelectBox('Odběratel');
		$c->setAttribute('data-style', 'select');
		$c->setAttribute('data-on-change', $this->link('changeKlient!'));
		$c->setAttribute('data-on-change-param', $this->getParameterId('klientId'));
		$c->controlPrototype->addAttributes([
			'class' => 'ajax'
		]);
		$f->addComponent($c, 'klientId');

		$c = new SelectBox('Fakturační skupina');
		$c->checkDefaultValue(false);
		$c->setAttribute('data-style', 'select');
		$f->addComponent($c, 'fakskupId');

		$c = new SelectBox('Odběrné místo');
		$c->checkDefaultValue(false);
		$c->setAttribute('data-style', 'select');
		$f->addComponent($c, 'omId');

		$f->addHidden('id');
		$f->addHidden('uuid');
		$f->addSubmit('save', $this->platba->hasValue('platbaId') ? 'Uložit' : 'Vytvořit');
		$f->addSubmit('cancel', 'Zrušit')->setValidationScope([])->onClick[] = function ()
		{
			$this->onCancel();
		};

		$f->onAnchor[] = Callback::arr($this, 'setListsItems');
		$f->onAnchor[] = Callback::arr($this, 'setDefaults');
		$f->onValidate[] = Callback::arr($this, 'onValidate');
		$f->onSuccess[] = Callback::arr($this, 'onSuccess');

		$f->setDefaults([
			'dphCoef' => $this->dphCoef
		]);

		return $f;
	}

	public function onValidate(
		BaseForm $form,
		$vals)
	{
		if(!$vals->klientId && $vals->omId){
			$form->addError('Pokud je vybráno OM, musí být vybrán Odběratel');
		}

		if(($vals->klientId || $vals->omId) && !$vals->type){
			$form->addError('Pokud je vybráno OM nebo Odběratel, musí být vybráno zařazení');
		}
	}

	public function onSuccess(
		Form $form,
		$vals)
	{
		$p = $this->platba;
		$p->when = $vals->when;
		$p->platba = $vals->platba;
		$p->fromCu = $vals->fromCu;
		$p->subject = $vals->subject;
		$p->msg = $vals->msg;
		$p->dphCoef = (string) $vals->dphCoef;
		$p->type = $vals->type;
		$p->vs = $vals->vs;

		if($vals->klientId || $vals->omId){
			if(!$p->hasValue('zarazeni')){
				$z = new PlatbaZarazeniEntity();
				$z->platba = $this->platba;
				$p->zarazeni = $z;
			}
			$p->zarazeni->klientId = $vals->klientId;
			$p->zarazeni->fakskupId = $vals->fakskupId ?: null;
			$p->zarazeni->omId = $vals->omId ?: null;
		}

		$this->onSave($p);
	}

	public function handleChangeKlient(
		$klientId)
	{
		$p = $this->platba;

		if(!$p->hasValue('zarazeni')){
			$p->zarazeni = new PlatbaZarazeniEntity();
		}

		$p->zarazeni->klientId = $klientId;
		$p->zarazeni->omId = null;

		$odberMists = $this->selOdbm->getLimitList($p->zarazeni->klientId);
		$this['form']->getComponent('omId')->setItems([
			null => ' - nevybráno - '
		] + $odberMists);

		$this->redrawControl('zarazeni');
	}

	public function setListsItems(
		BaseForm $form)
	{
		$types = $this->typyPlateb->getPairs();

		$form->getComponent('type')->setItems([
			null => ' - nezařazeno - '
		] + $types);

		$p = $this->platba;

		$klientId = $p->hasValue('zarazeni') && $p->zarazeni->klientId ? $p->zarazeni->klientId : null;

		$klients = $this->selKli->getList($klientId);
		$form->getComponent('klientId')->setItems([
			null => ' - nevybrán - '
		] + $klients);

		$fakSkups = $this->selFakSkup->getList($klientId);
		$form->getComponent('fakskupId')->setItems([
			null => ' - nezařazeno - '
		] + $fakSkups);

		$odberMists = $this->selOdbm->getLimitList($klientId);
		$form->getComponent('omId')->setItems([
			null => ' - nevybráno - '
		] + $odberMists);
	}

	public function setDefaults(
		BaseForm $form)
	{
		$p = $this->platba;

		$ids = [
			'id' => $p->hasValue('platbaId') ? $p->platbaId : null,
			'uuid' => $p->hasValue('uuid') ? $p->uuid : null
		];

		$zarazeni = [];
		if($p->hasValue('zarazeni')){
			$zarazeni = [
				'klientId' => $p->zarazeni->klientId,
				'fakskupId' => $p->zarazeni->fakskupId,
				'omId' => $p->zarazeni->omId
			];
		}

		$form->setDefaults(
			$ids + [
				'when' => $p->when,
				'fromCu' => $p->fromCu,
				'subject' => $p->subject,
				'platba' => $p->platba,
				'vs' => $p->vs,
				'msg' => $p->msg,
				'dphCoef' => $p->dphCoef,
				'type' => $p->hasValue('type') ? $p->type : null
			] + $zarazeni);
	}
}
