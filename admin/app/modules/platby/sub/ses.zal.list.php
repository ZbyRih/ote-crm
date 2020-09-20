<?php

class SelZalList{

	/**
	 * @param ModulSession $ses
	 * @param ModuleInfoClass $info
	 * @param int $year
	 * @param object $count
	 * @return ModelListClass
	 */
	public function create(
		$ses,
		$info,
		$ppfs,
		$year,
		$count)
	{
		$ids = $ses->zals;

		$sids = $ppfs->filter(function (
			$v)
		{
			return $v['PVParFZ']['zaloha_id'];
		})
			->extract('PVParFZ.zaloha_id')
			->toList();

		$oms = [];
		$zs = (new MZalohy())->FindAllById((empty($ids)) ? false : $ids);
		if(!empty($zs)){
			$oms = collection($zs)->extract('Zalohy.odber_mist_id')->toList();
		}

		$zals = new MOdberMistWZalSum();

		$zals->conditions[] = [
			'odber_mist_id' => (empty($oms)) ? false : $oms,
			'YEAR(Zalohy.od)' => $year
// 			'!isUhrZaloha(Zalohy.vyse, Zalohy.zaloha_id) IS NOT TRUE'
		];

		if($sids){
			$zals->conditions[] = 'OR';
			$zals->conditions[] = '!Zalohy.zaloha_id IN(' . implode(',', $sids) . ')';

			$zals->order[] = '!FIELD(Zalohy.zaloha_id,' . implode(',', $sids) . ')';
		}

		$zals->group[] = 'Zalohy.odber_mist_id';
		$zals->group[] = 'YEAR(Zalohy.od)';

		$zals->order = [
			'com' => 'ASC'
		];

		$List = ViewsFactory::createModelList($info);
		$List->configByArray(
			[
				'actions' => [
					ListAction::DELETE
				],
				'model' => $zals,
				'primaryKey' => 'Zalohy.zaloha_id',
				'cols' => [
					'OdberMist' => [
						'!IF(SUM(Zalohy.uhrazeno) >= SUM(Zalohy.vyse), 3, IF((SELECT DISTINCT TRUE FROM tx_zalohy AS z WHERE OdberMist.odber_mist_id = z.odber_mist_id AND YEAR(z.od) = ' . $year . ' AND z.uhr = 0 AND LAST_DAY(z.od) <= NOW()), 2, 1)) color'
					]
				],
				'spcCols' => [
					'OdberMist' => [
						'com' => 'OM'
					],
					'Address' => [
						'CONCAT_WS(\', \', Address.city, Address.street, Address.cp, Address.co)' => 'Adresa OM'
					],
					'Zalohy' => [
						'!CONCAT(DATE_FORMAT(MIN(Zalohy.od), \'%e.%c. %Y\'), \' - \', DATE_FORMAT(MAX(Zalohy.do), \'%e.%c. %Y\'))' => 'od - do',
						'COUNT(zaloha_id)' => 'Počet záloh',
						'SUM(vyse)' => 'Celkem'
// 						'!SUM(getUhrZaloha(Zalohy.zaloha_id))' => 'Uhrazeno'
					]
				],
				'numTypes' => [
					'Zalohy' => [
						'SUM(vyse)' => 3
// 						'!SUM(getUhrZaloha(Zalohy.zaloha_id))' => 3
					]
				],
				'linesColor' => [
					'OdberMist.color' => [
						2 => 'alert-danger',
						3 => 'alert-success'
					]
				],
				'sorting' => 'false',
				'static' => true
			]);

		$List->setAppCallBack(ListClass::ON_DATAPROCCESS,
			function (
				$item,
				$orgItem,
				$List) use (
			$count)
			{
				$count->sumZals += ($item['data'][4] - $item['data'][5]);
				return $item;
			});

		$List->setActionCallBacks(
			[
				ListAction::DELETE => function (
					$info,
					$List) use (
				$ses)
				{
					if($ids = ListClass::getActionIds($info)){
						$ppf = new MPVParFZ();
						foreach($ids as $id){
							if(false !== ($i = array_search($id, $ses->zals))){
								$arr = $ses->zals;
								unset($arr[$i]);
								$ses->zals = $arr;
							}else{
								if($ps = $ppf->FindAll([
									'platba_id' => $info->scope->parent->recordId,
									'!zaloha_id IS NOT NULL'
								])){
									foreach($ps as $p){
										$ppf->Delete($p['PVParFZ']['id']);
									}
								}
							}
						}
					}
					return true;
				}
			]);

		return $List;
	}
}