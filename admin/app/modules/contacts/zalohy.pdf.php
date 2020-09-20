<?php

class ZalohyPDFExport{

	public static function exportPdfOM(
		$year,
		$omIds,
		$contactId,
		$preview = false)
	{
		/* nejak to musi taky respektovat smlouvu, tj to veme zalohy pro datum smlouvy od do pro zakaznika */
		$omIds = MArray::AllwaysArray($omIds);

		$DPHCoef = OBE_Math::correctFloatNumber(OBE_AppCore::getDBVar('front', 'DPH_KOEF'));
		$splat = OBE_AppCore::getDBVar('front', 'DEN_SPLATNOSTI');

		$Zalohy = new MZalohy();
		$Contact = new MOdberatel();
		$OM = new MOdberMist();

		$odberatel = $Contact->FindOneById($contactId);

		$sestavy = [];

		$min_od = OBE_DateTime::toDateDB('2037-12-31');
		$max_do = 0;

		foreach($omIds as $id){

			$sum = 0;
			$uhr = 0;

			$items = [];
			$odm = $OM->FindOneById($id);

			if($zals = $Zalohy->FindAll([
				'odber_mist_id' => $id,
				'!' . $year . ' BETWEEN YEAR(od)',
				'YEAR(do)',
				'klient_id' => $contactId
			])){
				foreach($zals as $zal){
					$z = $zal['Zalohy'];

					if($min_od > OBE_DateTime::toDateDB($z['od'])){
						$min_od = OBE_DateTime::toDateDB($z['od']);
					}

					if($max_do < OBE_DateTime::toDateDB($z['do'])){
						$max_do = OBE_DateTime::toDateDB($z['do']);
					}

					$m = OBE_DateTime::getMonthDB($z['od']);

					$items[] = [
						'M' => $m,
						'splat' => $splat . '.' . $m . '. ' . OBE_DateTime::getYearDB($z['od']),
						'om_name' => MAddress::addrUl($odm),
						'com' => $odm['OdberMist']['com'],
						'zakl' => self::zakl($z['vyse'], $DPHCoef),
						'dph' => self::dph($z['vyse'], $DPHCoef),
						'full' => $z['vyse'],
						'uhr' => 0 //  $z['uhrazeno']
					];

					$sum += $z['vyse'];
					$uhr += 0; // $z['uhrazeno'];
				}

				$sestavy[] = [
					'items' => $items,
					'sum' => [
						'zakl' => self::zakl($sum, $DPHCoef),
						'dph' => self::dph($sum, $DPHCoef),
						'full' => $sum,
						'uhr' => $uhr
					]
				];
			}
		}

		$tbl = self::fetchSmarty([
			'sestavy' => $sestavy
		], 'zalohy.om.tpl');

		$templ = OBE_Templates::fromFile(APP_DIR_OLD . '/templates/zalohy/zalohy.om.full.tpl');

		// 		$templ = OBE_Templates::load('ZALOHA_OM');

		$identity = MContactDetails::identity($odberatel['ContactDetails']);
		$fakturacni = '<b>' . MContactDetails::name($odberatel['ContactDetails']) . '</b><br />' . MAddress::addrUl($odberatel) . '<br />' . MAddress::city(
			$odberatel);

		$odbAddr = MContactDetails::konAddr($odberatel);
		if($konAddr = MContactDetails::konAddr($odberatel, 'Korespond')){
			if(MContactDetails::isKonAddrEmpty($konAddr)){
				$konAddr = $odbAddr;
			}
		}

		$korespondencni = '<strong>' . MContactDetails::name($odberatel['ContactDetails'], true) . '</strong><br />' . str_replace("\r\n", '<br />', $konAddr);

		$templ->replace(
			[
				'OBCHODNIK_IDENT_A' => OBE_AppCore::getDBVar('front', 'OBCHODNIK_IDENT_A'),
				'OBCHODNIK_IDENT_B' => OBE_AppCore::getDBVar('front', 'OBCHODNIK_IDENT_B'),
				'ADRESA_KORESPONDENCNI' => $korespondencni,
				'ADRESA_FAKTURACNI' => $fakturacni,
				'IDENTITA' => $identity,
				'TABULKA' => $tbl,
				'CU_OBCHODNIKA' => OBE_AppCore::getDBVar('front', 'CU_OBCHODNIKA'),
				'UHRADA' => OBE_AppCore::getDBVar('front', 'UHRADA'),
				'VYSTAVENO' => OBE_DateTime::now(),
				'OBDOBI_OD' => OBE_DateTime::timeToUsr($min_od),
				'OBDOBI_DO' => OBE_DateTime::timeToUsr($max_do),
				'ODBM_ADDR' => MAddress::addr($odm),
				'ODBM_COM' => $odm['OdberMist']['com'],
				'ODBM_EIC' => $odm['OdberMist']['eic']
			]);

		self::correctSrc($preview, $templ);

		$html = '<style>' . $templ->sub_content . '</style>' . $templ->content;

		$clearName = OBE_Strings::remove_diacritics(MContactDetails::sname($odberatel['ContactDetails']));

		OBE_PDFExport::export($html, $clearName . '_' . $odm['OdberMist']['com'] . '_' . $year . '_' . date('m') . '.' . date('d') . '.pdf', $preview);
	}

	public static function exportPdfFS(
		$year,
		$om_Ids,
		$fsId,
		$contactId,
		$preview = false)
	{
		$DPHCoef = OBE_Math::correctFloatNumber(OBE_AppCore::getDBVar('front', 'DPH_KOEF'));
		$splat = OBE_AppCore::getDBVar('front', 'DEN_SPLATNOSTI');

		$Zalohy = new MZalohy();
		$Contact = new MOdberatel();
		$FakSkup = new MFakSkup();
		$SmlOM = new MSmlOM();

		$odberatel = $Contact->FindOneById($contactId);
		$fak = $FakSkup->FindOneById($fsId);

		$sestavy = [];

		$min_od = OBE_DateTime::toDateDB('2037-12-31');
		$max_do = 0;

		if($oms = $SmlOM->FindAll([
			'fak_skup_id' => $fsId,
			'!' . $year . ' BETWEEN YEAR(od)',
			'YEAR(do)',
			'klient_id' => $contactId
		])){

			$sm_items = MArray::MapItemToKey(MArray::GetMArrayForOneModel($oms, 'SmlOM'), 'odber_mist_id');
			$fs_items = MArray::MapItemToKey(MArray::GetMArrayForOneModel($oms, 'FakSkup'), 'fak_skup_id');
			$om_items = MArray::MapItemToKey(MArray::GetMArrayForOneModel($oms, 'OdberMist'), 'odber_mist_id');
			$ad_items = MArray::MapItemToKey(MArray::GetMArrayForOneModel($oms, 'Address'), 'address_id');

			$om2addr = MArray::MapValToKey($om_items, 'odber_mist_id', 'address_id');

			$omids = array_keys($om_items);

			$Zalohy->removeAssociateModels();

			if($zals = $Zalohy->FindAll([
				'odber_mist_id' => $omids,
				'!' . $year . ' BETWEEN YEAR(od)',
				'YEAR(do)',
				'klient_id' => $contactId
			], [], [
				'od',
				'odber_mist_id'
			])){

				$sum = 0;
				$uhr = 0;

				$z = reset($zals);
				$lm = $m = OBE_DateTime::getMonthDB($z['Zalohy']['od']);

				foreach($zals as $zal){
					$z = $zal['Zalohy'];

					$m = OBE_DateTime::getMonthDB($z['od']);

					if($lm != $m){
						$sestavy[] = [
							'items' => $items,
							'm' => $lm,
							'fs_vs' => $fs_vs,
							'sum' => [
								'zakl' => self::zakl($sum, $DPHCoef),
								'dph' => self::dph($sum, $DPHCoef),
								'full' => $sum,
								'uhr' => $uhr
							]
						];

						$items = [];
						$sum = 0;
						$uhr = 0;
					}

					if($min_od > OBE_DateTime::toDateDB($z['od'])){
						$min_od = OBE_DateTime::toDateDB($z['od']);
					}

					if($max_do < OBE_DateTime::toDateDB($z['do'])){
						$max_do = OBE_DateTime::toDateDB($z['do']);
					}

					$items[] = [
						'splat' => $splat . '.' . $m . '. ' . OBE_DateTime::getYearDB($z['od']),
						'om_name' => MAddress::addrUl([
							'Address' => $ad_items[$om2addr[$z['odber_mist_id']]]
						]),
						'com' => $om_items[$z['odber_mist_id']]['com'],
						'zakl' => self::zakl($z['vyse'], $DPHCoef),
						'dph' => self::dph($z['vyse'], $DPHCoef),
						'full' => $z['vyse'],
						'uhr' => 0 // $z['uhrazeno']
					];

					$sum += $z['vyse'];
					$uhr += 0; // $z['uhrazeno'];

					$lm = $m;
					$fs_vs = $fs_items[$sm_items[$z['odber_mist_id']]['fak_skup_id']]['cis'];
				}

				$sestavy[] = [
					'items' => $items,
					'm' => $lm,
					'fs_vs' => $fs_vs,
					'sum' => [
						'zakl' => self::zakl($sum, $DPHCoef),
						'dph' => self::dph($sum, $DPHCoef),
						'full' => $sum,
						'uhr' => $uhr
					]
				];
			}
		}

		$tbl = self::fetchSmarty([
			'sestavy' => $sestavy,
			'year' => $year
		], 'zalohy.fs.tpl');

		$templ = OBE_Templates::fromFile(APP_DIR_OLD . '/templates/zalohy/zalohy.fs.full.tpl');

		$identity = MContactDetails::identity($odberatel['ContactDetails']);
		$fakturacni = '<b><strong>F' . MContactDetails::name($odberatel['ContactDetails']) . '</strong><b/><br />' . MAddress::addrUl($odberatel) . '<br />' . MAddress::city(
			$odberatel);

		$odbAddr = MContactDetails::konAddr($odberatel);
		if($konAddr = MContactDetails::konAddr($odberatel, 'Korespond')){
			if(MContactDetails::isKonAddrEmpty($konAddr)){
				$konAddr = $odbAddr;
			}
		}

		if($fakAddr = MContactDetails::konAddr($fak)){
			if(!MContactDetails::isKonAddrEmpty($fakAddr)){
				$konAddr = $fakAddr;
			}
		}

		$odbKon = MContactDetails::name($fak['ContactDetails'], true);
		if(empty($odbKon)){
			$odbKon = MContactDetails::name($odberatel['ContactDetails'], true);
		}

		$korespondencni = '<strong>' . $odbKon . '</strong><br />' . str_replace("\r\n", '<br />', $konAddr);

		$templ->replace(
			[
				'OBCHODNIK_IDENT_A' => OBE_AppCore::getDBVar('front', 'OBCHODNIK_IDENT_A'),
				'OBCHODNIK_IDENT_B' => OBE_AppCore::getDBVar('front', 'OBCHODNIK_IDENT_B'),
				'ADRESA_KORESPONDENCNI' => $korespondencni,
				'ADRESA_FAKTURACNI' => $fakturacni,
				'IDENTITA' => $identity,
				'TABULKA' => $tbl,
				'CU_OBCHODNIKA' => OBE_AppCore::getDBVar('front', 'CU_OBCHODNIKA'),
				'UHRADA' => OBE_AppCore::getDBVar('front', 'UHRADA'),
				'VYSTAVENO' => OBE_DateTime::now(),
				'OBDOBI_OD' => OBE_DateTime::timeToUsr($min_od),
				'OBDOBI_DO' => OBE_DateTime::timeToUsr($max_do)
			]);

		self::correctSrc($preview, $templ);

		$html = '<style>' . $templ->sub_content . '</style>' . $templ->content;

		$clearName = OBE_Strings::remove_diacritics(MContactDetails::sname($odberatel['ContactDetails']));

		OBE_PDFExport::export($html, $clearName . '_' . $fak['FakSkup']['cis'] . '_' . $year . '_' . date('m') . '.' . date('d') . '.pdf', $preview);
	}

	public static function correctSrc(
		$preview,
		$templ)
	{
		if($preview){
			$templ->fullReplace([
				'src="' => 'src="../'
			]);
		}
	}

	public static function fetchSmarty(
		$params,
		$tpl)
	{
		$smarty = new OBE_Smarty();
		$smarty->debugging = false;
		$smarty->assign($params);

		return $smarty->fetch('zalohy/' . $tpl);
	}

	static function zakl(
		$from,
		$coef)
	{
		return $from - ($from * $coef);
	}

	static function dph(
		$from,
		$coef)
	{
		return $from * $coef;
	}
}