<?php

class ModulOteacomsettings extends AppModuleClass{

	const store_key = 'komunikace';

	/**
	 * {@inheritdoc}
	 * @see ModuleViewClass::__listModuleItems()
	 */
	function __listModuleItems(
		$info)
	{
		$Form = ViewsFactory::createForm($info->scope);

		$Form->createField('cert_priv_ote', FormUITypes::UPLOAD, null, 'Certifikát (komerční) s privátním klíčem příjemce v PEM formátu', true);
		$Form->createField('cert_priv_pass', FormUITypes::PASSWD, null, 'Heslo k certifikátu pro extrakci privátního klíče', true);
		$Form->createField('cert_priv_valid_to', FormUITypes::DATE, null, 'Vypršení platnosti připomenout dne', true);
		$Form->createField('cert_ote', FormUITypes::UPLOAD, null, 'Certifikát odesílatele (OTE) v PEM formátu', true);
		$Form->createField('mail_banka_server', FormUITypes::TEXT, null, 'Bankovní zprávy - Mail server', true);
		$Form->createField('mail_banka_user', FormUITypes::TEXT, null, 'Bankovní zprávy - Login', true);
		$Form->createField('mail_banka_pass', FormUITypes::PASSWD, null, 'Bankovní zprávy - Heslo', true);
		$Form->createField('mail_banka_folder', FormUITypes::TEXT, null, 'Bankovní zprávy - Složka', true);
		$Form->createField('mail_ote_server', FormUITypes::TEXT, null, 'OTE zprávy - Mail server', true);
		$Form->createField('mail_ote_user', FormUITypes::TEXT, null, 'OTE zprávy - Login', true);
		$Form->createField('mail_ote_pass', FormUITypes::PASSWD, null, 'OTE zprávy - Heslo', true);
		$Form->createField('mail_ote_folder', FormUITypes::TEXT, null, 'OTE zprávy - Složka', true);

		$values = OBE_AppCore::LoadVar(self::store_key);

		$Form->getField('cert_priv_pass')->disableEncode();
		$Form->getField('mail_banka_pass')->disableEncode();
		$Form->getField('mail_ote_pass')->disableEncode();

		$Form->getField('cert_priv_ote')
			->setMan()
			->setUpload()
			->createFileInfo((isset($values['cert_priv_ote']) ? APP_DIR_OLD . '/' . $values['cert_priv_ote'] : null));
		$Form->getField('cert_ote')
			->setMan()
			->setUpload()
			->createFileInfo((isset($values['cert_ote']) ? APP_DIR_OLD . '/' . $values['cert_ote'] : null));

		$Form->getField('cert_priv_ote')->setMask('09.09. 0009');

		$values['cert_priv_valid_to'] = OBE_DateTime::convertFromDB($values['cert_priv_valid_to']);

		$Form->fillWithData($values);

		$Form->buttons->addSubmit(FormButton::SAVE, 'Uložit');
		$Form->buttons->addCancel(FormButton::CANCEL, 'Zrušit');

		if($data = $Form->handleFormSubmit()){
			$this->onSendForm($data);
			$info->scope->resetViewByRedirect();
		}

		$this->views->add($Form);

		$this->addTestForm($info);

		return true;
	}

	/**
	 * @param AppFormClass2 $Form
	 */
	public function onSendForm(
		$data)
	{
		if($file = $data['cert_priv_ote']){
			OBE_File::checkDirectorys(APP_DIR_OLD . OBE_AppCore::getAppConf('certs_dir') . '/');
			OBE_File::moveUpload($file, APP_DIR_OLD . OBE_AppCore::getAppConf('certs_dir') . '/');
			$data['cert_priv_ote'] = OBE_AppCore::getAppConf('certs_dir') . '/' . $file['name'];
			$data['cert_priv_valid_to'] = $this->getValidTo($data['cert_priv_ote']);
		}else{
			unset($data['cert_priv_ote']);
		}

		if($file = $data['cert_ote']){
			OBE_File::checkDirectorys(APP_DIR_OLD . OBE_AppCore::getAppConf('certs_dir') . '/');
			OBE_File::moveUpload($file, APP_DIR_OLD . OBE_AppCore::getAppConf('certs_dir') . '/');
			$data['cert_ote'] = OBE_AppCore::getAppConf('certs_dir') . '/' . $file['name'];
		}else{
			unset($data['cert_ote']);
		}

		if(empty($data['cert_priv_pass'])){
			unset($data['cert_priv_pass']);
		}
		if(empty($data['mail_banka_pass'])){
			unset($data['mail_banka_pass']);
		}
		if(empty($data['mail_ote_pass'])){
			unset($data['mail_ote_pass']);
		}

		$var_values = OBE_AppCore::LoadVar(self::store_key, []);

		$data['cert_priv_valid_to'] = OBE_DateTime::convertToDB($data['cert_priv_valid_to']);

		$values = OBE_AppCore::saveVar(self::store_key, $data + $var_values);
	}

	public function addTestForm(
		$info)
	{
		$Form = ViewsFactory::createForm($info->scope);
		$Form->buttons->add('test_cert', 'Testovat certifikát');
		$Form->buttons->add('test_platby', 'Otestovat nastavení emailu s pohyby');
		$Form->buttons->add('test_ote', 'Otestovat nastavení emailu s OTE');

		$submit = $Form->buttons->getSubmit();

		if($submit == 'test_cert'){
			if($values = OBE_AppCore::LoadVar(ModulOteacomsettings::store_key)){
				$this->testCert($values['cert_priv_ote']);
			}
		}

		$redirect = true;

		if($submit == 'test_platby'){
			try{
				$reader = new PlatbyMailBoxReader(OBE_AppCore::LoadVar(ModulOteacomsettings::store_key));
				AdminApp::postMessage('Spojení se schránkou ' . $reader->getMail() . ' proběhlo v pořádku', 'success');
			}catch(Exception $e){
				$redirect = false;
				AdminApp::postMessage('Došlo k chybě: ' . $e->getMesaage(), 'danger');
			}
		}

		if($submit == 'test_ote'){
			try{
				$reader = new OTEMailBoxReader(OBE_AppCore::LoadVar(ModulOteacomsettings::store_key));
				AdminApp::postMessage('Spojení se schránkou ' . $reader->getMail() . ' proběhlo v pořádku', 'success');
			}catch(Exception $e){
				$redirect = false;
				AdminApp::postMessage('Došlo k chybě: ' . $e->getMesaage(), 'danger');
			}
		}

		if($submit && $redirect){
			$info->scope->resetViewByRedirect();
		}

		$this->views->add(ViewsFactory::newCardPane('Testy'));
		$this->views->add($Form);
	}

	public function testCert(
		$file)
	{
		$lastXmlErr = libxml_use_internal_errors(true);
		try{
			$cert = APP_DIR_OLD . $file;
			if(file_exists($cert)){
				$x509_data = openssl_x509_parse(file_get_contents($cert));
				if(is_array($x509_data) && !empty($x509_data)){
					AdminApp::postMessage('Certifikát se zdá být v pořádku', 'success');
					return true;
				}else{
					if($e = openssl_error_string()){
						AdminApp::postMessage('Došlo k chybě: ' . $e, 'danger');
					}
					return;
				}
			}
			AdminApp::postMessage('Soubor neexistuje', 'warning');
		}finally{
			libxml_clear_errors();
			libxml_use_internal_errors($lastXmlErr);
		}
		return false;
	}

	public function getValidTo(
		$file)
	{
		$lastXmlErr = libxml_use_internal_errors(true);
		try{
			$cert = APP_DIR_OLD . $file;
			if(file_exists($cert)){
				if($x509_data = openssl_x509_parse(file_get_contents($cert))){
					return date_create('@' . $x509_data['validTo_time_t'])->format('d.m. Y');
				}else{
					if($e = openssl_error_string()){
						AdminApp::postMessage('Došlo k chybě: ' . $e, 'danger');
					}
				}
			}
		}finally{
			libxml_clear_errors();
			libxml_use_internal_errors($lastXmlErr);
		}
		return false;
	}
}