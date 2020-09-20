<?php


class MZalohyHack extends MZalohy{

	/**
	 * (non-PHPdoc)
	 * @see ModelItemClass::onSaveBefor()
	 */
	public function onSaveBefor(&$modelItem){

	}

	public function odpojitZalohy($klientId, $year, $omId){

		$PFF = new MPVParFZ();

		$Zalohy = new MZalohyHack();
		if($zals = $Zalohy->FindAll(
			[
				'odber_mist_id' => $omId,
				'klient_id' => $klientId,
				'!' . $year . ' BETWEEN YEAR(Zalohy.od) AND YEAR(Zalohy.do)'
			])){

			$plas = 0;

			collection($zals)->each(
				function ($v) use (&$plas, $PFF){

					$plas += $PFF->CountBy('zaloha_id', $v['Zalohy']['zaloha_id']);

					$PFF->Delete(null, [
						'zaloha_id' => $v['Zalohy']['zaloha_id']
					]);
				});

			AdminApp::$mainModule->activityLog('Odpárováno', 'Odpárováno plateb: ' . $plas . ' od ' . count($zals) . ' záloh.', null, 'info');
		}
	}
}