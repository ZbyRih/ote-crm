<?php

class SelFakList{

	private $ses;

	/**
	 *
	 * @param ModulSession $ses
	 * @param ModuleInfoClass $info
	 * @param object $count
	 * @return ModelListClass
	 */
	public function create($ses, $info, $ppfs, $count){
		$this->ses = $ses;

		$U = new MUser();
		$us = MArray::MapValToKeyFromMArray($U->FindAll(), $U->name, 'id', 'jmeno');

		$sids = $ppfs->filter(function ($v){
			return $v['PVParFZ']['faktura_id'];
		})
			->extract('PVParFZ.faktura_id')
			->toList();

		$ids = $ses->faks;

		$faks = new MFaktury();
		$faks->conditions = [
			'id' => (empty($ids)) ? false : $ids
		];

		if($sids){
			$faks->conditions[] = 'OR';
			$faks->conditions[] = 'id IN(' . implode(',', $sids) . ')';

			$faks->order[] = '!FIELD(id,' . implode(',', $sids) . ')';
		}

		$faks->order['cis'] = 'DESC';

		$List = ViewsFactory::createModelList($info);
		$List->configByArray(
			[
				'form' => 'faktury',
				'model' => $faks,
				'actions' => [
					ListAction::DELETE
				],
				'spcCols' => [
					'Faktura' => [
						'cis' => 'Číslo',
						'DATE_FORMAT(Faktura.od, \'%d.%m. %Y\')' => 'Od',
						'DATE_FORMAT(Faktura.do, \'%d.%m. %Y\')' => 'Do',
						'user_id' => 'Vystavil',
						'DATE_FORMAT(Faktura.vystaveno, \'%d.%m. %Y\')' => 'vyst.',
						'man' => 'Ruční',
						'suma_a_dph' => 'Suma vč. DPH',
						'preplatek' => 'Platba',
						'!getUhrFaktura(Faktura.id)' => 'Uhrazeno'
					]
				],
				'numTypes' => [
					'Faktura' => [
						'suma_a_dph' => 3,
						'preplatek' => 3,
						'!getUhrFaktura(Faktura.id)' => 3
					]
				],
				'valuesSubstitute' => [
					'Faktura' => [
						'man' => [
							0 => 'ne',
							1 => 'ano'
						],
						'user_id' => $us
					]
				],
				'sorting' => '0:desc',
				'static' => true
			]);

		$List->setAppCallBack(ListClass::ON_DATAPROCCESS,
			function ($item, $orgItem, $List) use ($count){
				$count->sumFaks += $item['data'][7] - $item['data'][8];
				return $item;
			});

		$List->setActionCallBacks(
			[
				ListAction::DELETE => function ($info, $list) use ($ses){
					if($ids = ListClass::getActionIds($info)){
						$ppf = new MPVParFZ();
						foreach($ids as $id){
							if(false !== ($i = array_search($id, $ses->faks))){
								$arr = $ses->faks;
								unset($arr[$i]);
								$ses->faks = $arr;
							}else{
								if($p = $ppf->FindOne([
									'platba_id' => $info->scope->parent->recordId,
									'faktura_id' => $id
								])){
									$ppf->Delete($p['PVParFZ']['id']);
								}
								AdminApp::postMessage('Odebrána faktura', 'info');
								// tady musi byt jeste narovnani zápočťáku
							}
						}
					}
					return true;
				}
			]);

		return $List;
	}
}