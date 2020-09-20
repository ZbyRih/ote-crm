<?php
namespace App\Modules\Faktury\Components;

use App\Extensions\Components\BaseComponent;
use App\Extensions\Components\BaseForm;
use App\Models\Orm\Orm;
use App\Models\Orm\Faktury\FakturaEntity;
use App\Models\Repositories\SettingsRepository;
use App\Models\Series\FakturaSeries;
use App\Models\Selections\OdberMistSelection;
use App\Models\Selections\KlientsSelection;

class EditUser extends BaseComponent{

	/** @var Orm */
	private $orm;

	/** @var FakturaSeries */
	private $faSeries;

	/** @var SettingsRepository */
	private $repSettings;

	/** @var OdberMistSelection */
	private $selOdbMist;

	/** @var KlientsSelection */
	private $selKlients;

	/** @var FakturaEntity */
	private $fa;

	/** @var int */
	private $userId;

	/** @var [] */
	public $onSave = [];

	/** @var [] */
	public $onCancel = [];

	public function __construct(
		Orm $orm,
		FakturaSeries $faSeries,
		SettingsRepository $repSettings,
		OdberMistSelection $selOdbMist,
		KlientsSelection $selKlients)
	{
		$this->orm = $orm;
		$this->faSeries = $faSeries;
		$this->repSettings = $repSettings;
		$this->selOdbMist = $selOdbMist;
		$this->selKlients = $selKlients;
	}

	/**
	 * @param FakturaEntity|NULL $fa
	 */
	public function setFa(
		$fa)
	{
		$this->fa = $fa;
	}

	public function setUserId(
		$usrId)
	{
		$this->userId = $usrId;
	}

	public function createComponentEditForm()
	{
		$f = $this->createForm();
		$f->addHidden('id');

		$f->addDate('od', 'Období od');
		$f->addDate('do', 'Období do');
		$f->addDate('dzp', 'Datum zdanitelneho plnění');

		$klients = $this->selKlients->getList($this->fa ? $this->fa->klientId : null);
		$oms = $this->selOdbMist->getList($this->fa ? $this->fa->omId : null);

		$f->addSelect('klientId', 'Klient', $klients);
		$f->addSelect('omId', 'Odběrné místo', $oms);

		$f->addPrice('suma', 'Suma bez dph (CZK)');
		$f->addPrice('dph', 'DPH (CZK)');
		$f->addPrice('preplatek', 'Nedoplatek / Přeplatek (CZK)');

		$f->addPrice('cenaDistribuce', 'Cena za distribuci (bez DPH CZK)');
		$f->addPrice('cenaDanPlyn', 'Cena za daň z plynu (bez DPH CZK)');
		$f->addPrice('cenaZaMwh', 'Cena nákupu za MWh (CZK)');

		$f->addFloat('spotreba', 'Spotreba MWh');
		$f->addFloat('nakupMwh', 'Nákup MWh');

		$f->addDate('uhrazenoDzp', 'Uhrazena dan z plynu dne');

		$f->addUpload('file', 'Soubor');

		$f->addSubmit('save', 'Uložit');
		$f->addSubmit('cancel', 'Zrušit')->setValidationScope([])->onClick[] = function ()
		{
			$this->onCancel();
		};

		$f->onAnchor[] = function (
			$f)
		{
			$f->setDefaults($this->fa ? $this->fa->toArray() : []);
		};

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
		BaseForm $f,
		$vals)
	{
	}

	/**
	 * @param BaseForm $form
	 * @param array $vals
	 */
	public function onSuccess(
		BaseForm $form,
		$vals)
	{
		$fa = $this->fa ?: new FakturaEntity();

		$fa->od = $vals['od'];
		$fa->do = $vals['do'];
		$fa->dzp = $vals['dzp'];
		$fa->klientId = $vals['klientId'];
		$fa->omId = $vals['omId'];
		$fa->suma = $vals['suma'];
		$fa->dph = $vals['dph'];
		$fa->sumaADph = $fa->suma + $fa->dph;
		$fa->preplatek = $vals['preplatek'];
		$fa->cenaDistribuce = $vals['cenaDistribuce'];
		$fa->cenaDanPlyn = $vals['cenaDanPlyn'];
		$fa->cenaZaMwh = $vals['cenaZaMwh'];
		$fa->spotreba = $vals['spotreba'];
		$fa->nakupMwh = $vals['nakupMwh'];
		$fa->uhrazenoDzp = $vals['uhrazenoDzp'];

		if(!$fa->id){

			$num = $this->faSeries->next($fa->dzp->format('Y'));

			$fa->man = 1;
			$fa->cis = $num;
			$fa->danZp = $this->repSettings->dan_z_pln;
			$fa->dphKoef = $this->repSettings->dph_koef;
			$fa->dphSazba = $this->repSettings->dph;
			$fa->vystaveno = new \DateTime();
			$fa->userId = $this->user->id;
			$fa->params = serialize([
				'title' => null,
				'z' => 0,
				'd' => 0,
				'c' => 0
			]);
		}

		if($file = $vals['file']){

			$fi = new \SplFileInfo($file->name);

			$fa->ext = $fi->getExtension();

			$O = new \MOdberatel();
			$k = $O->FindOneById($fa->klientId);
			$saveFile = \FakturaFile::getFile(\MContactDetails::name($k['ContactDetails']), $fa->od, $fa->cis, $fa->ext);

			$file->move($saveFile);
		}

		$this->orm->persist($fa);

		$this->onSave($fa);
	}
}