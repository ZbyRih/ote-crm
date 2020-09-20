<?php
namespace App\Components\Tisk;

use App\Models\Values\PlatbaValue;
use App\Models\Entities\DokladEntity;
use App\Models\Repositories\SettingsRepository;
use App\Models\Views\AddressView;
use App\Extensions\Components\Tisk\TiskPage;
use App\Extensions\Utils\Arrays;
use App\Extensions\Utils\Html;
use App\Extensions\Helpers\Formaters;
use Nette\Application\UI\ITemplateFactory;

class TiskDanDoklad extends TiskPage{

	/** @var SettingsRepository */
	private $repSettings;

	/** @var string */
	private $identA;

	/** @var string */
	private $identB;

	/** @var string */
	private $cisloUctu;

	/** @var DokladEntity */
	private $doklad;

	public function __construct(
		ITemplateFactory $tplFac,
		SettingsRepository $repSettings)
	{
		parent::__construct();
		$this->setTemplateFactory($tplFac);

		$this->identA = $repSettings->ident_a;
		$this->identB = $repSettings->ident_b;
		$this->cisloUctu = $repSettings->cislo_uctu;
	}

	/**
	 * @param DokladEntity $doklad
	 */
	public function setDoklad(
		DokladEntity $doklad)
	{
		$this->doklad = $doklad;
	}

	/**
	 * {@inheritdoc}
	 * @see \App\Extensions\Components\BaseComponent::render()
	 */
	public function render()
	{
		$platba = new PlatbaValue($this->doklad->platba->sum, $this->doklad->dphCoef);

		$a = explode(',', $this->identA);
		$b = explode(',', $this->identB);

		$oName = Arrays::remove(0, $a);
		$oSidlo = Html::el('span')->addText($a[1] . ',' . $a[2])
			->addHtml(Html::el('br'))
			->addText($a[3] . ',' . $b[0])
			->addHtml(Html::el('br'))
			->addText($b[1] . ',' . $b[2] . ',' . $b[3]);

		$this->template->setParameters(
			[
				'sazba_dph' => 21,
				'dodavatel_cu' => $this->cisloUctu,
				'dodavatel_name' => $oName,
				'dodavatel_sidlo' => $oSidlo,

				'doklad_vs' => $this->doklad->vs,
				'doklad_cislo' => $this->doklad->cislo,
				'doklad_vyst' => $this->doklad->vystaveno->format('j.n. Y'),

				'platba_when' => $this->doklad->platba->when->format('j.n. Y'),
				'platba_vs' => $this->doklad->platba->vs,
				'platba_cu' => $this->doklad->platba->cu,

				'doklad_dph' => Formaters::price($platba->dph, 2, 'Kč'),
				'doklad_zaklad' => Formaters::price($platba->zaklad, 2, 'Kč'),
				'doklad_suma' => Formaters::price($platba->suma, 2, 'Kč'),

				'odberatel_nazev' => $this->doklad->odberatel->faIdent,
				'odberatel_sidlo' => AddressView::getHtmlFakturacni($this->doklad->odberatel->fakturacni),
				'odberatel_identity' => $this->doklad->odberatel->identity,
				'odberatel_osloveni' => $this->doklad->odberatel->konIdent,
				'odberatel_kontaktni' => AddressView::getHtmlDorucovaci($this->doklad->odberatel->kontaktni),

				'odbm_adr' => !$this->doklad->odberMist ? '' : AddressView::addr($this->doklad->odberMist->addressId),
				'odbm_com' => !$this->doklad->odberMist ? '' : $this->doklad->odberMist->com,
				'odbm_eic' => !$this->doklad->odberMist ? '' : $this->doklad->odberMist->eic
			]);

		parent::render();
	}
}