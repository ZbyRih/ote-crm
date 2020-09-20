<?php

namespace App\Components\Tisk;

use App\Extensions\Components\Tisk\TiskPage;
use App\Models\Entities\RozpisZalohEntity;
use App\Models\Repositories\SettingsRepository;
use App\Models\Views\AddressView;
use Nette\Application\UI\ITemplateFactory;

class TiskRozpisZaloh extends TiskPage{

	/** @var string */
	private $identA;

	/** @var string */
	private $identB;

	/** @var string */
	private $cisloUctu;

	/** @var string */
	private $formaUhrady;

	/** @var RozpisZalohEntity */
	private $zalohy;

	public function __construct(
		ITemplateFactory $tplFac,
		SettingsRepository $repSettings)
	{
		parent::__construct();
		$this->setTemplateFactory($tplFac);

		$this->identA = $repSettings->ident_a;
		$this->identB = $repSettings->ident_b;
		$this->cisloUctu = $repSettings->cislo_uctu;
		$this->formaUhrady = $repSettings->uhrada;
	}

	public function setZalohy(
		RozpisZalohEntity $zalohy)
	{
		$this->zalohy = $zalohy;
	}

	/**
	 * {@inheritdoc}
	 * @see \App\Extensions\Components\BaseComponent::render()
	 */
	public function render()
	{
		$tpl = $this->template;
		$eng = $tpl->getLatte();
		$eng->addFilter('pricef', function (
			$s)
		{
			return number_format($s, 2, ',', ' ');
		});

		$tpl->setParameters(
			[
				'sestavy' => $this->zalohy->sestavy,

				'dodavatel_a' => $this->identA,
				'dodavatel_b' => $this->identB,
				'dodavatel_cu' => $this->cisloUctu,

				'showOdbMist' => (bool) $this->zalohy->odberMist,
				'odbm_adr' => !$this->zalohy->odberMist ? '' : AddressView::addr($this->zalohy->odberMist->addressId),
				'odbm_com' => !$this->zalohy->odberMist ? '' : $this->zalohy->odberMist->com,
				'odbm_eic' => !$this->zalohy->odberMist ? '' : $this->zalohy->odberMist->eic,

				'year' => $this->zalohy->do->format('Y'),
				'obdobi_od' => $this->zalohy->od->format('d.m. Y'),
				'obdobi_do' => $this->zalohy->do->format('d.m. Y'),
				'datum_vystaveni' => $this->zalohy->vystaveno->format('d.m. Y'),
				'forma_uhrady' => $this->formaUhrady,

				'odberatel_nazev' => $this->zalohy->odberatel->faIdent,
				'odberatel_sidlo' => AddressView::getHtmlFakturacni($this->zalohy->odberatel->fakturacni),
				'odberatel_identity' => $this->zalohy->odberatel->identity,
				'odberatel_osloveni' => $this->zalohy->odberatel->konIdent ?: $this->zalohy->odberatel->faIdent,
				'odberatel_kontaktni' => AddressView::getHtmlDorucovaci($this->zalohy->odberatel->kontaktni)
			]);

		parent::render();
	}
}