<?php


class FakturyEditSubModule extends SubModule{

	var $handlers = [
		self::DEFAULT_VIEW => 'edit'
	];

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function edit($info){
		$view = false;
		$MF = new MFaktury();

		if($f = $MF->FindOneById($info->scope->parent->recordId)){

			$this->views->setTitle($f[$MF->name]['cis']);

			if($f[$MF->name]['odeslano']){
				$view = true;
				AdminApp::postMessage('Tuto fakturu již nelze editovat, již byla odeslána.', 'warning');
			}

			if($f[$MF->name]['man'] && !$f[$MF->name]['odeslano']){
				return $this->editMan($info, $view);
			}else{
				return $this->editAuto($info);
			}
		}else{
			return $this->editMan($info, $view);
		}
		return false;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function editMan($info, $view){

		$F = ViewsFactory::createForm($info->scope);

		$p = $F->createField('od', FormUITypes::DATE, null, 'Období od');
		$p->addToForm($F)->inline = 'first';
		$p = $F->createField('do', FormUITypes::DATE, null, 'Období do');
		$p->addToForm($F)->inline = 'next';
		$p = $F->createField('dzp', FormUITypes::DATE, null, 'Datum zdanitelneho plnění');
		$p->addToForm($F)->inline = 'last';

		$p = $F->createField('klient_id', FormUITypes::MODULE_ITEM_SELECT, null, 'Klient');
		$p->setSelect('Vybrat', MODULES::CONTACTS);
		$p->addToForm($F);

		$p = $F->createField('om_id', FormUITypes::MODULE_ITEM_SELECT, null, 'Odběrné místo');
		$p->setSelect('Vybrat', MODULES::ODBER_MIST)->setFieldsToUrl([
			'od' => 'od',
			'do' => 'do'
		]);
		$p->addToForm($F);

		$p = $F->createField('suma', FormUITypes::TEXT, 0, 'suma bez dph');
		$p->setType(FormFieldClass::CURRENCY)->addToForm($F)->inline = 'first';
		$p = $F->createField('dph', FormUITypes::TEXT, 0, 'dph');
		$p->setType(FormFieldClass::CURRENCY)->addToForm($F)->inline = 'next';
		$p = $F->createField('preplatek', FormUITypes::TEXT, 0, 'Nedoplatek / Přeplatek');
		$p->setType(FormFieldClass::CURRENCY)->addToForm($F)->inline = 'last';

		$p = $F->createField('cena_distribuce', FormUITypes::TEXT, 0, 'Cena za distribuci (bez DPH)');
		$p->setType(FormFieldClass::CURRENCY)->addToForm($F)->inline = 'first';
		$p = $F->createField('cena_dan_plyn', FormUITypes::TEXT, 0, 'Cena za daň z plynu (bez DPH)');
		$p->setType(FormFieldClass::CURRENCY)->addToForm($F)->inline = 'next';
		$p = $F->createField('cena_za_mwh', FormUITypes::TEXT, 0, 'Cena nákupu za MWh');
		$p->setType(FormFieldClass::CURRENCY)->addToForm($F)->inline = 'last';

		$p = $F->createField('spotreba', FormUITypes::TEXT, 0, 'Spotreba MWh');
		$p->setType(FormFieldClass::FLOAT)->addToForm($F);

		$p = $F->createField('nakup_mwh', FormUITypes::TEXT, 0, 'Nákup MWh');
		$p->setType(FormFieldClass::FLOAT)->addToForm($F);

		$p = $F->createField('uhrazeno_dzp', FormUITypes::DATE, null, 'Uhrazena dan z plynu dne');
		$p->addToForm($F);

		$p = $F->createField('file', FormUITypes::UPLOAD, null, 'Soubor');
		$p->setMan()->setUpload();
		$p->addToForm($F);

		$F->buttons->addSubmit(FormButton::T_SUBMIT, 'Uložit');
		$F->buttons->addCancel(FormButton::T_CANCEL, 'Zrušit');

		if(!$info->scope->parent->isEmptyRecId()){
			$mf = new MFaktury();
			$df = $mf->FindOneById($info->scope->parent->recordId);

			$df[$mf->name]['od'] = OBE_DateTime::convertFromDB($df[$mf->name]['od']);
			$df[$mf->name]['do'] = OBE_DateTime::convertFromDB($df[$mf->name]['do']);
			$df[$mf->name]['dzp'] = OBE_DateTime::convertFromDB($df[$mf->name]['dzp']);
			$df[$mf->name]['uhrazeno_dzp'] = OBE_DateTime::convertFromDB($df[$mf->name]['uhrazeno_dzp']);

			$C = new MContacts();
			$k = $C->FindOneById($df[$mf->name]['klient_id']);

			$p = $F->getField('klient_id');
			$p->setItemLabel(MContactDetails::name($k['ContactDetails']) . ', ' . MContactDetails::konAddr($k));

			$O = new MOdberMist();
			$oo = $O->FindOneById($df[$mf->name]['om_id']);

			$p = $F->getField('om_id');
			$p->setItemLabel(MOdberMist::identity($oo));

			$F->fillWithData($df[$mf->name]);
		}else{
			$F->fillWithData([
				'dzp' => (new \DateTime())->format('d.m. Y')
			]);
		}

		if($view){
			$F->setAccess(FormFieldRights::VIEW);
		}else{
			$F->setAccess(FormFieldRights::EDIT);
		}

		if($d = $F->handleFormSubmit()){
			if($this->onSaveFormMan($info, $d)){
				$info->scope->parent->resetViewByRedirect(null, ModuleViewClass::DEFAULT_VIEW);
			}else{
				$info->scope->resetViewByRedirect(null, ModuleViewClass::DEFAULT_VIEW);
			}
		}elseif($d === false){
			$info->scope->parent->resetViewByRedirect(null, ModuleViewClass::DEFAULT_VIEW);
		}

		if($view){
			$F->buttons->clear();
		}

		$this->views->add($F);

		FakturyDetailLists::addListOfPlas($info, $info->scope->parent->recordId, $this->views);
		FakturyDetailLists::addListOfOtes($info, $info->scope->parent->recordId, $this->views);

		return true;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param array $d
	 * @return boolean
	 */
	public function onSaveFormMan($info, $d){
		$MF = new MFaktury();

		$a = [
			'klient_id' => $d['klient_id'],
			'om_id' => $d['om_id'],
			'od' => OBE_DateTime::convertToDB($d['od']),
			'do' => OBE_DateTime::convertToDB($d['do']),
			'dph' => $d['dph'],
			'suma' => $d['suma'],
			'suma_a_dph' => $d['dph'] + $d['suma'],
			'preplatek' => $d['preplatek'],
			'dzp' => OBE_DateTime::convertToDB($d['dzp']),
			'cena_distribuce' => $d['cena_distribuce'],
			'cena_dan_plyn' => $d['cena_dan_plyn'],
			'cena_za_mwh' => $d['cena_za_mwh'],
			'spotreba' => $d['spotreba'],
			'nakup_mwh' => $d['nakup_mwh'],
			'uhrazeno_dzp' => OBE_DateTime::convertToDB($d['uhrazeno_dzp'])
		];

		if($info->scope->parent->isEmptyRecId()){
			$pp = OBE_DateTime::getDBToDate(OBE_DateTime::convertToDB($d['dzp']))->format('y');
			$num = MFaktury::getNewCis($pp);
			$a = $a + [
				'cis' => $num,
				'dan_zp' => OBE_AppCore::getDBVar('front', 'DAN_Z_PLN'),
				'dph_koef' => OBE_AppCore::getDBVar('front', 'DPH_KOEF'),
				'dph_sazba' => OBE_AppCore::getDBVar('front', 'DPH'),
				'vystaveno' => OBE_DateTime::convertDTToDB(new \DateTime()),
				'user_id' => AdminUserClass::$userId,
				'man' => 1,
				'params' => serialize([
					'title' => null,
					'z' => 0,
					'd' => 0,
					'c' => 0
				])
			];
		}else{
			$FA = $MF->FindOneById($info->scope->parent->recordId);
			$a = $a + [
				'id' => $info->scope->parent->recordId,
				'cis' => $FA[$MF->name]['cis']
			];
		}

		if($file = $d['file']){

			$file['name'] = '';

			$O = new MOdberatel();
			$k = $O->FindOneById($a['klient_id']);

			$saveFile = FakturaFile::getFile(MContactDetails::name($k['ContactDetails']), $a['od'], $a['cis'], $file['ext']);
			$file['name'] = '';

			OBE_File::moveUpload($file, $saveFile);

			$a['ext'] = $file['ext'];
		}

		$s[$MF->name] = $a;
		$MF->Save($s);

		if($info->scope->parent->isEmptyRecId()){
			$info->scope->parent->recordId = $s[$MF->name]['id'];
			return false;
		}

		return true;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @return boolean
	 */
	function editAuto($info){
		$F = ViewsFactory::createForm($info->scope);

		$p = $F->createField('nakup_mwh', FormUITypes::TEXT, 0, 'Nákup MWh');
		$p->setType(FormFieldClass::FLOAT)->addToForm($F);

		$p = $F->createField('uhrazeno_dzp', FormUITypes::DATE, null, 'Uhrazena dan z plynu dne');
		$p->addToForm($F);

		$F->buttons->addSubmit(FormButton::T_SUBMIT, 'Uložit');
		$F->buttons->addCancel(FormButton::T_CANCEL, 'Zpět');

		if($d = $F->handleFormSubmit()){
			$this->onSaveFormAuto($info, $d);
			$info->scope->parent->resetViewByRedirect(null, ModuleViewClass::DEFAULT_VIEW);
		}elseif($d === false){
			$info->scope->parent->resetViewByRedirect(null, ModuleViewClass::DEFAULT_VIEW);
		}

		$this->views->add($F);

		FakturyDetailLists::addListOfPlas($info, $info->scope->parent->recordId, $this->views);
		FakturyDetailLists::addListOfOtes($info, $info->scope->parent->recordId, $this->views);

		return true;
	}

	/**
	 *
	 * @param ModuleInfoClass $info
	 * @param array $d
	 * @return boolean
	 */
	public function onSaveFormAuto($info, $d){
		$MF = new MFaktury();

		$a = [
			'nakup_mwh' => $d['nakup_mwh'],
			'uhrazeno_dzp' => $d['uhrazeno_dzp'],
			'id' => $info->scope->parent->recordId
		];

		$s[$MF->name] = $a;
		$MF->Save($s);
	}
}