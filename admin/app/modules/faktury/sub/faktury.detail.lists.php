<?php

class FakturyDetailLists{

	public static function addListOfPlas($info, $faId, $views){
		$ppf = new MPVParFZ();
		$ppf->addAssociatedModels(
			[
				'MPlatby' => [
					'type' => 'hasOne',
					'name' => 'Platba',
					'foreignKey' => 'platba_id',
					'associationForeignKey' => 'platba_id'
				]
			]);

		if($pls = $ppf->FindBy('faktura_id', $faId, [], [], [
			'Platba.when' => 'ASC'
		])){

			$views->add(ViewsFactory::newCardPane('Připojené platby.'));

			$L = ViewsFactory::createList($info);

			$L->configByArray(
				[
					'cols' => [
						'from_cu' => 'z účtu',
						'vs' => 'V.S.',
						'when' => 'Přišla dne.',
						'platba' => 'Platba',
						'suma' => 'Do faktury připsáno'
					],
					'numTypes' => [
						'suma' => 3,
						'platba' => 3
					]
				]);

			$L->setData(
				collection($pls)->map(
					function ($v, $k){
						return [
							'id' => $v['PVParFZ']['id'],
							'from_cu' => $v['Platba']['from_cu'],
							'vs' => $v['Platba']['vs'],
							'when' => (new \DateTime($v['Platba']['when']))->format('j.n. Y'),
							'platba' => $v['Platba']['platba'],
							'suma' => $v['PVParFZ']['suma']
						];
					})
					->indexBy('id')
					->toArray());

			$views->add($L);
		}
	}

	public static function addListOfOtes($info, $faId, $views){
		$views->add(ViewsFactory::newCardPane('Vyfakturované zprávy OTE.'));

		$gp6 = new GP6HeadWMailAndOM();

		$List = ViewsFactory::createModelList($info);

		$gp6->conditions['faktura_id'] = $faId;

		$List->configByArray(
			[
				'form' => 'ote_list',
				'model' => $gp6,
				'actions' => [],
				'spcCols' => [
					'OTEMails' => [
						'CONCAT(DATE_FORMAT(GP6Head.from, \'%d.%m. %Y\'), \' - \', DATE_FORMAT(GP6Head.to, \'%d.%m. %Y\'))' => 'Od - do',
						'ote_kod' => 'Kód'
					],
					'GP6Head' => [
						'pofId' => 'POF ID',
						'CONCAT_WS(\', \', OdberMist.eic, Address.city, Address.street, Address.cp, Address.co) AS adresa' => 'O.M.',
						'attributes_segment' => 'Segment',
						'attributes_corReason' => 'Důvod',
						'priceTotal' => 'Total bez DPH',
						'cancelled' => 'Zrušená',
						'!IF(GP6Head.faktura_id, 1, 0) AS vyfak' => 'Vyfakturovaná'
					]
				],
				'numTypes' => [
					'GP6Head' => [
						'priceTotal' => 3
					]
				],
				'valuesSubstitute' => [
					'GP6Head' => [
						'cancelled' => [
							0 => 'ne',
							1 => 'ano'
						],
						'attributes_segment' => GP6Head::SEGMENT,
						'attributes_corReason' => GP6Head::COR_REASON,
						'vyfak' => [
							0 => 'ne',
							1 => 'ano'
						]
					]
				]
			]);

		$views->add($List);
	}
}