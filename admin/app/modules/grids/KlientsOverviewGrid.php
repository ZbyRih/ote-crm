<?php


namespace App\Klients\Grids;

use App\Components\ModelGridComponent;

class KlientsOverviewGrid extends ModelGridComponent{

	/**
	 *
	 * @var \EntityTagCtrl
	 */
	private $tagsCtrl;

	public function config($info){
		$this->onConfig[] = function ($List){
			if($this->isDeleted()){
				if($action = $this->getAction(\ListAction::DELETE)){
					$action->setIcon('md md-undo');
					$action->setTitle('Vrátit');
					$action->setMass('Vrátit');
				}
			}
		};

		$aktivni = [
			'!(SELECT COUNT(tso.id) FROM tx_sml_om AS tso
				WHERE ' . date('Y') . ' BETWEEN YEAR(tso.od) AND YEAR(tso.do)
					AND tso.klient_id = Contacts.klient_id) AS c_smlom' => 'O',
			'!(SELECT COUNT(tcm.id) FROM tx_cena_mwh AS tcm
				WHERE YEAR(tcm.od) = ' . date('Y') . '
					AND tcm.klient_id = Contacts.klient_id
					AND tcm.odber_mist_id IN (
						SELECT tso.odber_mist_id FROM tx_sml_om AS tso
							WHERE ' . date('Y') . ' BETWEEN YEAR(tso.od) AND YEAR(tso.do)
								AND tso.klient_id = Contacts.klient_id
							GROUP BY tso.odber_mist_id)) AS c_cmwh' => 'C',
			'!(SELECT COUNT(z.zaloha_id) FROM tx_zalohy AS z
				WHERE ' . date('Y') . ' BETWEEN YEAR(z.od) AND YEAR(z.do)
					AND z.klient_id = Contacts.klient_id
					AND z.odber_mist_id IN (
						SELECT tso.odber_mist_id FROM tx_sml_om AS tso
							WHERE ' . date('Y') . ' BETWEEN YEAR(tso.od) AND YEAR(tso.do)
								AND tso.klient_id = Contacts.klient_id
							GROUP BY tso.odber_mist_id)) AS c_zals' => 'Z'
		];

		return [
			'actions' => [
				\ListAction::EDIT,
				\ListAction::DELETE
			],
			'spcCols' => [
				'ContactDetails' => [
					'kind' => 'Forma',
					'firstname' => 'Jméno',
					'lastname' => 'Přijmení',
					'firm_name' => 'Název subjektu',
					'email' => 'E-Mail',
					'ContactDetails.email AS email2' => 'Poslat'
				],
				'Address' => [
					'CONCAT_WS(\', \', Address.city, Address.street, Address.cp, Address.co) AS adresa' => 'Adresa',
					'zip' => 'PSČ'
				],
				'Contacts' => [
					'DATE_FORMAT(createdate, \'%d.%m. %Y\')' => 'Datum vytvoření'
				] + $aktivni
			],
			'pagination' => true,
			'itemsOnPage' => 10,
			'filter' => [
				[
					'type' => 'like',
					'fields' => [
						'ContactDetails.email',
						'ContactDetails.firstname',
						'ContactDetails.lastname',
						'ContactDetails.firm_name'
					],
					'name' => 'Jméno, Přijmení, Mail'
				],
				[
					'type' => 'x',
					'fields' => [
						'Contacts.deleted'
					],
					'name' => 'Neaktivní'
				],
				[
					'type' => 'x',
					'fields' => [
						'ntags'
					],
					'name' => '<span class="text-sm" style="top:10px; position: relative;">Neobsahuje<br />Štítky<span>',
					'mod' => false
				],
				[
					'type' => 'tags',
					'fields' => [
						'tagy'
					],
					'name' => 'Štítky',
					'obj' => $this->tagsCtrl
				]
			],
			'sort' => [
				'ContactDetails.firstname',
				'ContactDetails.lastname',
				'ContactDetails.firm_name',
				'ContactDetails.email',
				'ContactDetails.kind',
				'DATE_FORMAT(Contacts.createdate, \'%d.%m. %Y\')'
			],
			'valuesSubstitute' => [
				'Contacts' => [
					'deleted' => [
						'1' => 'neaktivní',
						'0' => 'aktivní'
					]
				],
				'ContactDetails' => [
					'kind' => \MContactDetails::$KIND_SHR
				]
			],
			'numbered' => true,
			'headInfo' => [
				'c_cmwh' => 'Počet aktivních cen za mwh',
				'c_smlom' => 'Počet letos aktivních odběrných míst',
				'c_zals' => 'Počet letošních záloh'
			],
			'fieldTplMap' => [
				'ContactDetails' => [
					'ContactDetails.email AS email2' => \FormUITypes::EMAIL
				]
			]
		];
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \App\Components\ModelGridComponent::setUpModel()
	 */
	public function setUpModel(\ModelListClass $Grid){
		$model = new \MOdberatel();
		$model->conditions['fakturacni'] = 0;

		if(\AdminUserClass::isOnlyOwn()){
			$model->conditions['owner_id'] = \AdminUserClass::$userId;
		}

		return $model;
	}

	/**
	 *
	 * @param \EntityTagCtrl $tagsCtrl
	 */
	public function setTagsCtrl($tagsCtrl){
		$this->tagsCtrl = $tagsCtrl;
		return $this;
	}

	public function isDeleted(){
		if($it = $this->getFilter(1)){
			if($it->getValue() == 1){
				return true;
			}
		}
		return false;
	}
}